<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AIProvider;
use App\Services\AI\Providers\ClaudeProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\OpenAIProvider;
use InvalidArgumentException;

class AIProviderFactory
{
    /**
     * สร้าง provider ตาม name + apiKey
     */
    public function make(string $providerName, string $apiKey, ?string $defaultModel = null): AIProvider
    {
        switch ($providerName) {
            case 'claude':
            case 'anthropic':
                return new ClaudeProvider($apiKey, $defaultModel);
            case 'openai':
                return new OpenAIProvider($apiKey, $defaultModel);
            case 'gemini':
            case 'google':
                return new GeminiProvider($apiKey, $defaultModel);
            default:
                throw new InvalidArgumentException("Unknown AI provider: {$providerName}");
        }
    }

    /**
     * รายชื่อ providers ที่รองรับ
     */
    public function getSupportedProviders(): array
    {
        return ['claude', 'openai', 'gemini'];
    }
}
