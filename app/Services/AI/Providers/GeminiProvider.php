<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProvider;
use GuzzleHttp\Client;

class GeminiProvider implements AIProvider
{
    public const PROVIDER_NAME = 'gemini';
    public const API_BASE = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct(
        protected string $apiKey,
        protected ?string $defaultModel = null,
        protected ?Client $httpClient = null
    ) {
        $this->defaultModel ??= config('ai.gemini.default_model', 'gemini-1.5-flash');
        $this->httpClient ??= new Client(['timeout' => 60]);
    }

    public function generateText(
        string $systemPrompt,
        string $userPrompt,
        ?string $model = null,
        array $options = []
    ): array {
        $model = $model ?? $this->defaultModel;

        $url = self::API_BASE . "/{$model}:generateContent?key={$this->apiKey}";

        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => [
                ['parts' => [['text' => $userPrompt]]],
            ],
            'generationConfig' => [
                'maxOutputTokens' => $options['max_tokens'] ?? 2048,
                'temperature'     => $options['temperature'] ?? 0.7,
            ],
        ];

        $response = $this->httpClient->post($url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json'    => $payload,
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        $text = '';
        if (!empty($body['candidates'][0]['content']['parts'])) {
            foreach ($body['candidates'][0]['content']['parts'] as $part) {
                $text .= $part['text'] ?? '';
            }
        }

        return [
            'text'          => $text,
            'provider'      => self::PROVIDER_NAME,
            'model'         => $model,
            'tokens_input'  => $body['usageMetadata']['promptTokenCount']     ?? 0,
            'tokens_output' => $body['usageMetadata']['candidatesTokenCount'] ?? 0,
        ];
    }

    public function generateJSON(
        string $systemPrompt,
        string $userPrompt,
        ?string $model = null,
        array $options = []
    ): array {
        $systemPrompt .= "\n\nReply with ONLY valid JSON, no markdown code fences.";

        $resp = $this->generateText($systemPrompt, $userPrompt, $model, $options);

        $cleaned = trim($resp['text']);
        $cleaned = preg_replace('/^```(?:json)?\s*/', '', $cleaned);
        $cleaned = preg_replace('/\s*```$/', '', $cleaned);

        $data = json_decode($cleaned, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Gemini returned invalid JSON: ' . json_last_error_msg());
        }

        return [
            'data'          => $data,
            'provider'      => self::PROVIDER_NAME,
            'model'         => $resp['model'],
            'tokens_input'  => $resp['tokens_input'],
            'tokens_output' => $resp['tokens_output'],
        ];
    }

    public function estimateCost(string $model, int $tokensInput, int $tokensOutput): float
    {
        $pricing = [
            'gemini-1.5-flash'    => ['input' => 0.075, 'output' => 0.30],
            'gemini-1.5-pro'      => ['input' => 1.25,  'output' => 5.00],
            'gemini-2.0-flash'    => ['input' => 0.10,  'output' => 0.40],
        ];
        $rates = $pricing[$model] ?? ['input' => 0.075, 'output' => 0.30];
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
