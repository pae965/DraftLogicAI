<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\SectionTemplate;
use App\Services\SectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Articles
 *
 * จัดการบทความวิจัย/ค้นคว้าอิสระ
 */
class ArticleController extends Controller
{
    public function __construct(
        protected SectionService $sectionService
    ) {}

    /**
     * List articles
     *
     * รายการบทความของ user (หรือทั้งหมดสำหรับ admin)
     *
     * @queryParam status string สถานะ: draft, pending_review, published. Example: published
     * @queryParam search string ค้นหาในชื่อเรื่อง. Example: สัญญา
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Article::with(['primaryAuthor:id,name,name_th,name_en', 'template:id,key,name_th']);

        if (!$user->isEditor()) {
            $query->where('primary_author_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('search')) {
            $kw = $request->get('search');
            $query->where(function ($q) use ($kw) {
                $q->where('title_th', 'like', "%{$kw}%")
                  ->orWhere('title_en', 'like', "%{$kw}%");
            });
        }

        return response()->json([
            'data' => $query->latest()->paginate(20),
        ]);
    }

    /**
     * Get article detail
     */
    public function show(Article $article): JsonResponse
    {
        $this->authorize('view', $article);

        $article->load([
            'primaryAuthor', 'template', 'sections',
            'authors', 'abstracts', 'keywords', 'citations', 'citationUses',
        ]);

        return response()->json(['data' => $article]);
    }

    /**
     * Create new article
     *
     * @bodyParam title_th string required ชื่อบทความภาษาไทย
     * @bodyParam title_en string required ชื่อบทความภาษาอังกฤษ
     * @bodyParam template_id integer ID ของ template (ถ้าไม่ส่งจะใช้ default)
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title_th'     => ['required', 'string', 'max:500'],
            'title_en'     => ['required', 'string', 'max:500'],
            'subtitle_th'  => ['nullable', 'string', 'max:500'],
            'subtitle_en'  => ['nullable', 'string', 'max:500'],
            'template_id'  => ['nullable', 'integer', 'exists:section_templates,id'],
            'primary_language' => ['nullable', 'in:th,en'],
            'independent_study_title_th' => ['nullable', 'string'],
            'independent_study_title_en' => ['nullable', 'string'],
        ]);

        // ถ้าไม่ส่ง template_id → ใช้ default ของ user
        if (empty($data['template_id'])) {
            $tpl = $this->sectionService->getDefaultTemplateForUser($request->user());
            $data['template_id'] = $tpl?->id;
        }

        $article = Article::create($data);
        // sections จะถูกสร้างอัตโนมัติผ่าน hook ใน Article model

        // เพิ่ม primary author
        $article->authors()->create([
            'user_id'         => $request->user()->id,
            'title_th'        => $request->user()->title_th,
            'title_en'        => $request->user()->title_en,
            'display_name_th' => $request->user()->name_th ?? $request->user()->name,
            'display_name_en' => $request->user()->name_en ?? $request->user()->name,
            'affiliation_th'  => $request->user()->default_affiliation_th,
            'affiliation_en'  => $request->user()->default_affiliation_en,
            'address_th'      => $request->user()->default_address_th,
            'address_en'      => $request->user()->default_address_en,
            'email'           => $request->user()->email,
            'orcid_id'        => $request->user()->orcid_id,
            'profile_url'     => $request->user()->profile_url,
            'role'            => 'primary_author',
            'order'           => 0,
        ]);

        return response()->json(['data' => $article->fresh(['sections', 'authors'])], 201);
    }

    /**
     * Update article
     */
    public function update(Request $request, Article $article): JsonResponse
    {
        $this->authorize('update', $article);

        $data = $request->validate([
            'title_th'     => ['sometimes', 'string', 'max:500'],
            'title_en'     => ['sometimes', 'string', 'max:500'],
            'subtitle_th'  => ['nullable', 'string', 'max:500'],
            'subtitle_en'  => ['nullable', 'string', 'max:500'],
            'template_id'  => ['nullable', 'integer', 'exists:section_templates,id'],
            'status'       => ['sometimes', 'in:draft,pending_review,scheduled,published,archived'],
            'independent_study_title_th' => ['nullable', 'string'],
            'independent_study_title_en' => ['nullable', 'string'],
            'degree_program_th' => ['nullable', 'string'],
            'degree_program_en' => ['nullable', 'string'],
            'faculty_th'        => ['nullable', 'string'],
            'faculty_en'        => ['nullable', 'string'],
        ]);

        $article->update($data);
        return response()->json(['data' => $article->fresh()]);
    }

    /**
     * Delete article (soft delete)
     */
    public function destroy(Article $article): JsonResponse
    {
        $this->authorize('delete', $article);
        $article->delete();
        return response()->json(['message' => 'Article deleted']);
    }

    /**
     * Get available templates for current user
     */
    public function availableTemplates(Request $request): JsonResponse
    {
        $result = $this->sectionService->getAvailableTemplatesForUser($request->user());
        return response()->json($result);
    }
}
