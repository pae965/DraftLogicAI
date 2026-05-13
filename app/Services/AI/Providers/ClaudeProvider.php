<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProvider;
use GuzzleHttp\Client;

class ClaudeProvider implements AIProvider
{
    public const PROVIDER_NAME = 'claude';
    public const API_URL = 'https://api.anthropic.com/v1/messages';

    public function __construct(
        protected string $apiKey,
        protected ?string $defaultModel = null,
        protected ?Client $httpClient = null
    ) {
        $this->defaultModel ??= config('ai.claude.default_model', 'claude-3-5-sonnet-20241022');
        $this->httpClient ??= new Client(['timeout' => 60]);
    }

    public function generateText(
        string $systemPrompt,
        string $userPrompt,
        ?string $model = null,
        array $options = []
    ): array {
        $model = $model ?? $this->defaultModel;
        $maxTokens = $options['max_tokens'] ?? 2048;

        $payload = [
            'model'      => $model,
            'max_tokens' => $maxTokens,
            'system'     => $systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ];

        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }

        $response = $this->httpClient->post(self::API_URL, [
            'headers' => [
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ],
            'json' => $payload,
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        $text = '';
        if (!empty($body['content'])) {
            foreach ($body['content'] as $block) {
                if ($block['type'] === 'text') {
                    $text .= $block['text'];
                }
            }
        }

        return [
            'text'          => $text,
            'provider'      => self::PROVIDER_NAME,
            'model'         => $model,
            'tokens_input'  => $body['usage']['input_tokens'] ?? 0,
            'tokens_output' => $body['usage']['output_tokens'] ?? 0,
        ];
    }

    public function generateJSON(
        string $systemPrompt,
        string $userPrompt,
        ?string $model = null,
        array $options = []
    ): array {
        $systemPrompt .= "\n\nReply with ONLY valid JSON, no markdown code fences, no explanations.";

        $resp = $this->generateText($systemPrompt, $userPrompt, $model, $options);

        $cleaned = trim($resp['text']);
        $cleaned = preg_replace('/^```(?:json)?\s*/', '', $cleaned);
        $cleaned = preg_replace('/\s*```$/', '', $cleaned);

        $data = json_decode($cleaned, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                'Claude returned invalid JSON: ' . json_last_error_msg() . "\nRaw: {$cleaned}"
            );
        }

        return [
            'data'          => $data,
            'provider'      => self::PROVIDER_NAME,
            'model'         => $resp['model'],
            'tokens_input'  => $resp['tokens_input'],
            'tokens_output' => $resp['tokens_output'],
        ];
    }

    /**
     * ราคาประมาณ (USD per 1M tokens) — อ้างอิง Anthropic 2024-10
     */
    public function estimateCost(string $model, int $tokensInput, int $tokensOutput): float
    {
        $pricing = [
            'claude-3-5-sonnet-20241022' => ['input' => 3.0,  'output' => 15.0],
            'claude-3-5-haiku-20241022'  => ['input' => 0.80, 'output' => 4.0],
            'claude-3-opus-20240229'     => ['input' => 15.0, 'output' => 75.0],
        ];
        $rates = $pricing[$model] ?? ['input' => 3.0, 'output' => 15.0];
        return ($tokensInput * $rates['input'] + $tokensOutput * $rates['output']) / 1_000_000;
    }

    public function getProviderName(): string
    {
        return self::PROVIDER_NAME;
    }

    public function getDefaultModel(): string
    {
        return $this->defaultModel;
    }
}
