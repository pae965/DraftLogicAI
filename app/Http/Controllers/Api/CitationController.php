<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Citation;
use App\Services\AI\AICitationService;
use App\Services\CitationFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Citations
 *
 * จัดการรายการอ้างอิง — 3 modes: manual, URL/ISBN lookup, free-form reformat
 */
class CitationController extends Controller
{
    public function __construct(
        protected CitationFormatter $formatter,
        protected AICitationService $aiCitation
    ) {}

    /**
     * List citations of an article
     */
    public function index(Article $article): JsonResponse
    {
        $this->authorize('view', $article);
        return response()->json(['data' => $article->citations()->get()]);
    }

    /**
     * Mode A: Create citation from structured data (deterministic format)
     *
     * @bodyParam citation_type string required book|article|article_in_book|newspaper|thesis|website|unpublished|other
     * @bodyParam language string required th|en
     * @bodyParam data object required ข้อมูลตาม schema ของ type นั้น ๆ
     */
    public function storeManual(Request $request, Article $article): JsonResponse
    {
        $this->authorize('update', $article);
        $data = $request->validate([
            'citation_type' => ['required', 'in:book,article,article_in_book,newspaper,thesis,website,unpublished,other'],
            'language'      => ['required', 'in:th,en'],
            'data'          => ['required', 'array'],
        ]);

        $citation = $this->aiCitation->modeAFormatStructured(
            $article, $data['citation_type'], $data['data'], $data['language']
        );

        return response()->json(['data' => $citation], 201);
    }

    /**
     * Mode B: Lookup citation from URL or ISBN
     */
    public function storeLookup(Request $request, Article $article): JsonResponse
    {
        $this->authorize('update', $article);
        $data = $request->validate([
            'language' => ['required', 'in:th,en'],
            'url'      => ['nullable', 'url'],
            'isbn'     => ['nullable', 'string'],
        ]);

        if (empty($data['url']) && empty($data['isbn'])) {
            return response()->json(['error' => 'Provide either url or isbn'], 422);
        }

        $citation = $this->aiCitation->modeBLookup(
            $article, $request->user(), $data['language'],
            $data['url'] ?? null, $data['isbn'] ?? null
        );

        return response()->json(['data' => $citation], 201);
    }

    /**
     * Mode C: Reformat free-form citation text
     */
    public function storeReformat(Request $request, Article $article): JsonResponse
    {
        $this->authorize('update', $article);
        $data = $request->validate([
            'raw_text' => ['required', 'string', 'max:5000'],
            'language' => ['required', 'in:th,en'],
        ]);

        $citation = $this->aiCitation->modeCReformat(
            $article, $request->user(), $data['raw_text'], $data['language']
        );

        return response()->json(['data' => $citation], 201);
    }

    /**
     * Update citation
     */
    public function update(Request $request, Article $article, Citation $citation): JsonResponse
    {
        $this->authorize('update', $article);
        if ($citation->article_id !== $article->id) abort(404);

        $data = $request->validate([
            'citation_type' => ['sometimes', 'in:book,article,article_in_book,newspaper,thesis,website,unpublished,other'],
            'language'      => ['sometimes', 'in:th,en'],
            'data'          => ['sometimes', 'array'],
            'notes'         => ['nullable', 'string'],
        ]);

        // re-format ถ้า data หรือ type เปลี่ยน
        if (isset($data['data']) || isset($data['citation_type'])) {
            $type = $data['citation_type'] ?? $citation->citation_type;
            $structured = $data['data'] ?? $citation->data;
            $lang = $data['language'] ?? $citation->language;

            $formatted = $this->formatter->format($type, $structured, $lang);
            $data['formatted_footnote'] = $formatted['footnote'];
            $data['formatted_bibliography'] = $formatted['bibliography'];
        }

        $citation->update($data);
        return response()->json(['data' => $citation->fresh()]);
    }

    /**
     * Delete citation
     */
    public function destroy(Article $article, Citation $citation): JsonResponse
    {
        $this->authorize('update', $article);
        if ($citation->article_id !== $article->id) abort(404);

        $citation->delete();
        return response()->json(['message' => 'Citation deleted']);
    }
}
