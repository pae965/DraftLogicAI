<?php

namespace App\Services\AI;

use App\Models\Article;
use App\Models\Citation;
use App\Models\User;
use App\Services\CitationFormatter;

/**
 * AI-assisted citation processing — 3 modes
 *
 * Mode A: manual + AI format       — user กรอกข้อมูลละเอียด → AI format
 * Mode B: URL/ISBN lookup           — AI ดึง metadata + format
 * Mode C: free-form reformat        — user วาง raw → AI parse + format
 */
class AICitationService
{
    public function __construct(
        protected AIService $aiService,
        protected CitationFormatter $formatter
    ) {}

    /**
     * Mode A: Manual data → format (deterministic, no AI)
     */
    public function modeAFormatStructured(
        Article $article,
        string $type,
        array $data,
        string $language
    ): Citation {
        $formatted = $this->formatter->format($type, $data, $language);

        return $article->citations()->create([
            'citation_type'          => $type,
            'language'               => $language,
            'data'                   => $data,
            'formatted_footnote'     => $formatted['footnote'],
            'formatted_bibliography' => $formatted['bibliography'],
            'ai_normalized'          => false,
            'ai_mode'                => Citation::AI_MODE_MANUAL,
        ]);
    }

    /**
     * Mode B: URL/ISBN → AI extract → format
     */
    public function modeBLookup(
        Article $article,
        User $user,
        string $language,
        ?string $url = null,
        ?string $isbn = null
    ): Citation {
        if (empty($url) && empty($isbn)) {
            throw new \InvalidArgumentException('Must provide url or isbn');
        }

        $langLabel = $language === 'th' ? 'ไทย' : 'English';
        $systemPrompt = <<<PROMPT
You are a metadata extractor for Thai legal academic citations (NIDA standard).
Extract metadata from the source provided and return JSON in this exact schema:

{
  "citation_type": "book|article|article_in_book|newspaper|thesis|website|unpublished|other",
  "data": { /* fields per type — see schema docs */ },
  "confidence": 0.0-1.0,
  "notes": "describe any missing or uncertain data"
}

Schema per citation_type:
- book: { authors[], title, edition?, city, publisher, year, pages? }
- article: { authors[], title, journal, volume?, issue?, monthYear, pages }
- thesis: { authors[], title, degreeAndDept, year, pages? }
- website: { baseType, base{}, retrievedDate, url }

DO NOT fabricate data — if year/pages/author unknown, set null and note in 'notes'.
Output language: {$langLabel}
PROMPT;

        $sourceLine = $url ? "URL: {$url}" : "ISBN: {$isbn}";
        $userPrompt = "{$sourceLine}\n\nExtract metadata and return JSON.";

        $resp = $this->aiService->generateJSON(
            user: $user,
            systemPrompt: $systemPrompt,
            userPrompt: $userPrompt,
            article: $article,
            purpose: 'citation_lookup'
        );

        $extracted = $resp['data'];
        $citationType = $extracted['citation_type'];
        $data = $extracted['data'];

        $formatted = $this->formatter->format($citationType, $data, $language);

        return $article->citations()->create([
            'citation_type'          => $citationType,
            'language'               => $language,
            'data'                   => $data,
            'formatted_footnote'     => $formatted['footnote'],
            'formatted_bibliography' => $formatted['bibliography'],
            'ai_normalized'          => true,
            'ai_mode'                => Citation::AI_MODE_URL_LOOKUP,
            'source_url'             => $url,
            'source_isbn'            => $isbn,
            'notes'                  => $extracted['notes'] ?? null,
        ]);
    }

    /**
     * Mode C: Free-form text → AI parse → format
     */
    public function modeCReformat(
        Article $article,
        User $user,
        string $rawText,
        string $language
    ): Citation {
        $langLabel = $language === 'th' ? 'ไทย' : 'English';
        $systemPrompt = <<<PROMPT
You are a citation parser for Thai legal academia (NIDA standard).
Receive a citation in any format (possibly malformed) and return JSON:

{
  "citation_type": "book|article|article_in_book|newspaper|thesis|website|unpublished|other",
  "data": { /* fields per type */ },
  "confidence": 0.0-1.0,
  "issues": ["list of missing/uncertain fields"]
}

DO NOT fabricate fields not in input — set null and note in 'issues'.
Output language: {$langLabel}
PROMPT;

        $resp = $this->aiService->generateJSON(
            user: $user,
            systemPrompt: $systemPrompt,
            userPrompt: "Citation text to parse:\n\n{$rawText}",
            article: $article,
            purpose: 'citation_reformat'
        );

        $extracted = $resp['data'];
        $citationType = $extracted['citation_type'];
        $data = $extracted['data'];

        $formatted = $this->formatter->format($citationType, $data, $language);

        $issues = $extracted['issues'] ?? [];

        return $article->citations()->create([
            'citation_type'          => $citationType,
            'language'               => $language,
            'data'                   => $data,
            'formatted_footnote'     => $formatted['footnote'],
            'formatted_bibliography' => $formatted['bibliography'],
            'ai_normalized'          => true,
            'ai_mode'                => Citation::AI_MODE_REFORMAT,
            'notes'                  => !empty($issues) ? 'Issues: ' . implode('; ', $issues) : null,
        ]);
    }
}
