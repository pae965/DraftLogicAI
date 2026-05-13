<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleAbstract;
use App\Services\AI\AIService;
use App\Models\User;

class AbstractService
{
    public function __construct(
        protected AIService $aiService
    ) {}

    /**
     * ตั้งค่า abstract แบบ manual
     */
    public function setManual(Article $article, string $language, string $contentText): ArticleAbstract
    {
        return ArticleAbstract::updateOrCreate(
            ['article_id' => $article->id, 'language' => $language],
            [
                'mode'            => ArticleAbstract::MODE_MANUAL,
                'content_text'    => $contentText,
                'source_language' => null,
                'ai_provider'     => null,
                'ai_model'        => null,
                'translated_at'   => null,
                'approved_by_author' => false,
                'approved_at'     => null,
            ]
        );
    }

    /**
     * แปล abstract ด้วย AI (ต้องมี source content ก่อน)
     */
    public function translate(
        Article $article,
        User $user,
        string $sourceLanguage,
        string $targetLanguage,
        ?string $customInstructions = null
    ): ArticleAbstract {
        if ($sourceLanguage === $targetLanguage) {
            throw new \InvalidArgumentException('Source and target languages must differ');
        }

        $source = $article->abstracts()
            ->where('language', $sourceLanguage)
            ->first();

        if (!$source || empty($source->content_text)) {
            throw new \RuntimeException("Source abstract ({$sourceLanguage}) has no content yet");
        }

        $langPair = $sourceLanguage === 'th' ? 'Thai → English' : 'English → Thai';
        $systemPrompt = <<<PROMPT
You are an academic legal abstract translator (Thai legal academia standard).
Translate the abstract: {$langPair}.

REQUIREMENTS:
- Maintain the THREE required components: (1) objective, (2) findings, (3) recommendations.
- Output as ONE paragraph (no headers, no line breaks).
- Use formal academic legal terminology.
- Preserve technical terms accurately (statute names, legal doctrines, court names).
- Do NOT add information not present in the source.
- Output ONLY the translated text. No preamble, no explanations.
PROMPT;

        if ($customInstructions) {
            $systemPrompt .= "\n\nADDITIONAL: {$customInstructions}";
        }

        $response = $this->aiService->generateText(
            user: $user,
            systemPrompt: $systemPrompt,
            userPrompt: $source->content_text,
            article: $article,
            purpose: 'abstract_translation'
        );

        return ArticleAbstract::updateOrCreate(
            ['article_id' => $article->id, 'language' => $targetLanguage],
            [
                'mode'              => ArticleAbstract::MODE_AI_TRANSLATED,
                'content_text'      => trim($response['text']),
                'source_language'   => $sourceLanguage,
                'ai_provider'       => $response['provider'],
                'ai_model'          => $response['model'],
                'translated_at'     => now(),
                'approved_by_author' => false,
                'approved_at'       => null,
            ]
        );
    }

    /**
     * อนุมัติ abstract
     */
    public function approve(Article $article, string $language): ArticleAbstract
    {
        $abstract = $article->abstracts()
            ->where('language', $language)
            ->firstOrFail();

        $abstract->update([
            'approved_by_author' => true,
            'approved_at'        => now(),
        ]);

        return $abstract;
    }

    /**
     * ดึง approved abstracts ทั้ง 2 ภาษา (สำหรับ Export)
     *
     * @return array{th: string|null, en: string|null}
     */
    public function getApproved(Article $article): array
    {
        $approved = $article->abstracts()
            ->where('approved_by_author', true)
            ->get()
            ->keyBy('language');

        return [
            'th' => $approved->get('th')?->content_text,
            'en' => $approved->get('en')?->content_text,
        ];
    }
}
