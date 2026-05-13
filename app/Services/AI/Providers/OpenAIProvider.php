<?php

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\AIProvider;
use GuzzleHttp\Client;

class OpenAIProvider implements AIProvider
{
    public const PROVIDER_NAME = 'openai';
    public const API_URL = 'https://api.openai.com/v1/chat/completions';

    public function __construct(
        protected string $apiKey,
        protected ?string $defaultModel = null,
        protected ?Client $httpClient = null
    ) {
        $this->defaultModel ??= config('ai.openai.default_model', 'gpt-4o-mini');
        $this->httpClient ??= new Client(['timeout' => 60]);
    }

    public function generateText(
        string $systemPrompt,
        string $userPrompt,
        ?string $model = null,
        array $options = []
    ): array {
        $model = $model ?? $this->defaultModel;

        $payload = [
            'model'    => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'max_tokens' => $options['max_tokens'] ?? 2048,
        ];

        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }

        $response = $this->httpClient->post(self::API_URL, [
            'headers' => [
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type'  => 'application/json',
            ],
            'json' => $payload,
        ]);

        $body = json_decode($response->getBody()->getContents(), true);

        return [
            'text'          => $body['choices'][0]['message']['content'] ?? '',
            'provider'      => self::PROVIDER_NAME,
            'model'         => $model,
            'tokens_input'  => $body['usage']['prompt_tokens']     ?? 0,
            'tokens_output' => $body['usage']['completion_tokens'] ?? 0,
        ];
    }

    public function generateJSON(
        string $systemPrompt,
        string $userPrompt,
        ?string $model = null,
        array $options = []
    ): array {
        // OpenAI รองรับ JSON mode native
        $options['response_format'] = ['type' => 'json_object'];
        $systemPrompt .= "\n\nReply with valid JSON only.";

        $resp = $this->generateText($systemPrompt, $userPrompt, $model, $options);

        $data = json_decode($resp['text'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('OpenAI returned invalid JSON: ' . json_last_error_msg());
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
            'gpt-4o'         => ['input' => 2.50,  'output' => 10.0],
            'gpt-4o-mini'    => ['input' => 0.15,  'output' => 0.60],
            'gpt-4-turbo'    => ['input' => 10.0,  'output' => 30.0],
            'gpt-3.5-turbo'  => ['input' => 0.50,  'output' => 1.50],
        ];
        $rates = $pricing[$model] ?? ['input' => 2.50, 'output' => 10.0];
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
