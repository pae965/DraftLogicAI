<?php

namespace App\Services\AI;

use App\Models\AiSetting;
use App\Models\AiUsageLog;
use App\Models\Article;
use App\Models\User;
use App\Services\AI\Contracts\AIProvider;

/**
 * Main AI Service — เลือก provider ตาม user setting + log usage
 *
 * BYOK: ดึง API key จาก ai_settings ของ user (encrypted)
 * Fallback: ถ้า user ไม่มี active setting → ใช้ env default key
 */
class AIService
{
    public function __construct(
        protected AIProviderFactory $factory
    ) {}

    /**
     * หา active provider สำหรับ user
     */
    public function getProviderForUser(User $user, ?string $forceProvider = null): AIProvider
    {
        $query = $user->aiSettings()->where('is_active', true);

        if ($forceProvider) {
            $query->where('provider', $forceProvider);
        }

        $setting = $query->first();

        if ($setting) {
            return $this->factory->make(
                $setting->provider,
                $setting->api_key,
                $setting->model_default
            );
        }

        // Fallback to env default (Claude)
        $envProvider = $forceProvider ?? 'claude';
        $envKey = config("ai.{$envProvider}.api_key");

        if (empty($envKey)) {
            throw new \RuntimeException(
                "User {$user->id} has no active AI setting and no env fallback for {$envProvider}"
            );
        }

        return $this->factory->make($envProvider, $envKey);
    }

    /**
     * Generate text + log usage
     */
    public function generateText(
        User $user,
        string $systemPrompt,
        string $userPrompt,
        ?Article $article = null,
        string $purpose = 'other',
        ?string $forceProvider = null,
        ?string $model = null,
        array $options = []
    ): array {
        $provider = $this->getProviderForUser($user, $forceProvider);
        $startedAt = now();

        try {
            $response = $provider->generateText($systemPrompt, $userPrompt, $model, $options);

            $cost = $provider->estimateCost(
                $response['model'],
                $response['tokens_input'],
                $response['tokens_output']
            );

            $this->logUsage($user, $article, $response, $purpose, $cost, true, null, $startedAt);

            return $response;
        } catch (\Throwable $e) {
            $this->logUsage(
                $user, $article,
                ['provider' => $provider->getProviderName(), 'model' => $model ?? '', 'tokens_input' => 0, 'tokens_output' => 0],
                $purpose, 0.0, false, $e->getMessage(), $startedAt
            );
            throw $e;
        }
    }

    /**
     * Generate JSON + log usage
     */
    public function generateJSON(
        User $user,
        string $systemPrompt,
        string $userPrompt,
        ?Article $article = null,
        string $purpose = 'other',
        ?string $forceProvider = null,
        ?string $model = null,
        array $options = []
    ): array {
        $provider = $this->getProviderForUser($user, $forceProvider);
        $startedAt = now();

        try {
            $response = $provider->generateJSON($systemPrompt, $userPrompt, $model, $options);

            $cost = $provider->estimateCost(
                $response['model'],
                $response['tokens_input'],
                $response['tokens_output']
            );

            $this->logUsage($user, $article, $response, $purpose, $cost, true, null, $startedAt);

            return $response;
        } catch (\Throwable $e) {
            $this->logUsage(
                $user, $article,
                ['provider' => $provider->getProviderName(), 'model' => $model ?? '', 'tokens_input' => 0, 'tokens_output' => 0],
                $purpose, 0.0, false, $e->getMessage(), $startedAt
            );
            throw $e;
        }
    }

    /**
     * บันทึก usage log
     */
    protected function logUsage(
        User $user,
        ?Article $article,
        array $response,
        string $purpose,
        float $cost,
        bool $success,
        ?string $error,
        $requestedAt
    ): void {
        AiUsageLog::create([
            'user_id'       => $user->id,
            'article_id'    => $article?->id,
            'provider'      => $response['provider'] ?? 'unknown',
            'model'         => $response['model'] ?? '',
            'purpose'       => $purpose,
            'tokens_input'  => $response['tokens_input']  ?? 0,
            'tokens_output' => $response['tokens_output'] ?? 0,
            'cost_estimate' => $cost,
            'success'       => $success,
            'error_message' => $error,
            'requested_at'  => $requestedAt,
        ]);
    }
}
