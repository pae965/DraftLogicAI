<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleSection;
use App\Services\SectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Sections
 *
 * จัดการหัวข้อบทความ — เพิ่ม/ลบ/แก้ไข/แทรก/เรียงลำดับ
 */
class SectionController extends Controller
{
    public function __construct(
        protected SectionService $sectionService
    ) {}

    /**
     * List sections of an article (ordered)
     */
    public function index(Article $article): JsonResponse
    {
        $this->authorize('view', $article);
        $sections = $this->sectionService->getOrderedSections($article, includeHidden: true);
        $numbering = $this->sectionService->computeNumbering($sections);

        return response()->json([
            'data'      => $sections,
            'numbering' => $numbering,
        ]);
    }

    /**
     * Update section content (Tiptap JSON)
     */
    public function update(Request $request, Article $article, ArticleSection $section): JsonResponse
    {
        $this->authorize('update', $article);

        if ($section->article_id !== $article->id) {
            abort(404);
        }

        $data = $request->validate([
            'label_th'   => ['sometimes', 'string', 'max:255'],
            'label_en'   => ['sometimes', 'string', 'max:255'],
            'visible'    => ['sometimes', 'boolean'],
            'numbered'   => ['sometimes', 'boolean'],
            'content'    => ['nullable', 'array'],
            'content_th' => ['nullable', 'array'],
            'content_en' => ['nullable', 'array'],
            'extra'      => ['nullable', 'array'],
        ]);

        $section->update($data);
        return response()->json(['data' => $section->fresh()]);
    }

    /**
     * Insert new section after a given order
     *
     * @bodyParam after_order integer required ลำดับหลังจาก section นี้. Example: 4
     * @bodyParam label_th string required ชื่อหัวข้อภาษาไทย. Example: ขอบเขตการศึกษา
     */
    public function insert(Request $request, Article $article): JsonResponse
    {
        $this->authorize('update', $article);

        $data = $request->validate([
            'after_order'       => ['required', 'integer'],
            'label_th'          => ['required', 'string', 'max:255'],
            'label_en'          => ['required', 'string', 'max:255'],
            'template_item_key' => ['nullable', 'string', 'max:64'],
            'numbered'          => ['nullable', 'boolean'],
            'visible'           => ['nullable', 'boolean'],
            'type'              => ['nullable', 'in:abstract,abstract_en,keywords,richtext,bibliography'],
        ]);

        $afterOrder = $data['after_order'];
        unset($data['after_order']);

        $data['template_item_key'] = $data['template_item_key'] ?? ('custom_' . uniqid());
        $data['numbered']          = $data['numbered'] ?? true;
        $data['visible']           = $data['visible'] ?? true;
        $data['type']              = $data['type'] ?? 'richtext';

        $section = $this->sectionService->insertSection($article, $afterOrder, $data);
        return response()->json(['data' => $section], 201);
    }

    /**
     * Reorder sections (drag & drop)
     *
     * @bodyParam ordered_ids array required Section IDs ในลำดับใหม่
     */
    public function reorder(Request $request, Article $article): JsonResponse
    {
        $this->authorize('update', $article);

        $data = $request->validate([
            'ordered_ids'   => ['required', 'array'],
            'ordered_ids.*' => ['integer'],
        ]);

        $this->sectionService->reorderSections($article, $data['ordered_ids']);
        $sections = $this->sectionService->getOrderedSections($article, true);
        return response()->json(['data' => $sections]);
    }

    /**
     * Delete section
     */
    public function destroy(Article $article, ArticleSection $section): JsonResponse
    {
        $this->authorize('update', $article);
        if ($section->article_id !== $article->id) abort(404);

        $section->delete();
        return response()->json(['message' => 'Section deleted']);
    }
}
