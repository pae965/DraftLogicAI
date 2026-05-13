<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiSetting;
use App\Models\Article;
use App\Services\AbstractService;
use App\Services\AI\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group AI
 *
 * AI assistance endpoints — multi-provider (Claude / OpenAI / Gemini)
 */
class AIController extends Controller
{
    public function __construct(
        protected AIService $aiService,
        protected AbstractService $abstractService
    ) {}

    /**
     * List user's AI settings
     */
    public function settings(Request $request): JsonResponse
    {
        $settings = $request->user()->aiSettings()
            ->select(['id', 'provider', 'model_default', 'is_active', 'created_at'])
            ->get();

        return response()->json(['data' => $settings]);
    }

    /**
     * Save AI provider setting (BYOK)
     *
     * @bodyParam provider string required claude|openai|gemini
     * @bodyParam api_key string required API key (จะ encrypt อัตโนมัติ)
     */
    public function saveSetting(Request $request): JsonResponse
    {
        $data = $request->validate([
            'provider'      => ['required', 'in:claude,openai,gemini'],
            'api_key'       => ['required', 'string'],
            'model_default' => ['nullable', 'string'],
            'is_active'     => ['nullable', 'boolean'],
        ]);

        $setting = AiSetting::updateOrCreate(
            ['user_id' => $request->user()->id, 'provider' => $data['provider']],
            $data
        );

        return response()->json(['data' => [
            'id' => $setting->id,
            'provider' => $setting->provider,
            'is_active' => $setting->is_active,
        ]]);
    }

    /**
     * Translate abstract (TH ↔ EN)
     */
    public function translateAbstract(Request $request, Article $article): JsonResponse
    {
        $this->authorize('update', $article);
        $data = $request->validate([
            'source_language' => ['required', 'in:th,en'],
            'target_language' => ['required', 'in:th,en', 'different:source_language'],
            'custom_instructions' => ['nullable', 'string'],
        ]);

        $abstract = $this->abstractService->translate(
            $article,
            $request->user(),
            $data['source_language'],
            $data['target_language'],
            $data['custom_instructions'] ?? null
        );

        return response()->json(['data' => $abstract]);
    }

    /**
     * Approve abstract
     */
    public function approveAbstract(Request $request, Article $article): JsonResponse
    {
        $this->authorize('update', $article);
        $data = $request->validate([
            'language' => ['required', 'in:th,en'],
        ]);

        $abstract = $this->abstractService->approve($article, $data['language']);
        return response()->json(['data' => $abstract]);
    }

    /**
     * Set abstract manually
     */
    public function setAbstract(Request $request, Article $article): JsonResponse
    {
        $this->authorize('update', $article);
        $data = $request->validate([
            'language'     => ['required', 'in:th,en'],
            'content_text' => ['required', 'string', 'max:5000'],
        ]);

        $abstract = $this->abstractService->setManual(
            $article, $data['language'], $data['content_text']
        );
        return response()->json(['data' => $abstract]);
    }

    /**
     * Free-form text generation
     */
    public function generateText(Request $request): JsonResponse
    {
        $data = $request->validate([
            'system_prompt' => ['required', 'string', 'max:5000'],
            'user_prompt'   => ['required', 'string', 'max:10000'],
            'article_id'    => ['nullable', 'integer', 'exists:articles,id'],
            'force_provider'=> ['nullable', 'in:claude,openai,gemini'],
            'purpose'       => ['nullable', 'string'],
        ]);

        $article = !empty($data['article_id'])
            ? Article::find($data['article_id'])
            : null;

        $response = $this->aiService->generateText(
            user: $request->user(),
            systemPrompt: $data['system_prompt'],
            userPrompt: $data['user_prompt'],
            article: $article,
            purpose: $data['purpose'] ?? 'other',
            forceProvider: $data['force_provider'] ?? null,
        );

        return response()->json(['data' => $response]);
    }
}
