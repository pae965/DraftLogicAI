<?php

namespace App\Services\AI\Contracts;

interface AIProvider
{
    /**
     * Generate text response
     *
     * @return array{text: string, provider: string, model: string, tokens_input: int, tokens_output: int}
     */
    public function generateText(
        string $systemPrompt,
        string $userPrompt,
        ?string $model = null,
        array $options = []
    ): array;

    /**
     * Generate JSON response (with schema hint in system prompt)
     *
     * @return array{data: array, provider: string, model: string, tokens_input: int, tokens_output: int}
     */
    public function generateJSON(
        string $systemPrompt,
        string $userPrompt,
        ?string $model = null,
        array $options = []
    ): array;

    /**
     * Estimate cost in USD
     */
    public function estimateCost(string $model, int $tokensInput, int $tokensOutput): float;

    public function getProviderName(): string;
    public function getDefaultModel(): string;
}
