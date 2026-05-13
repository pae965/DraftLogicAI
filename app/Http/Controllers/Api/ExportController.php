<?php

namespace App\Http\Controllers\Api;

use App\Exports\ExportService;
use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group Export
 *
 * ส่งออกบทความเป็น Word (.docx) หรือ PDF
 */
class ExportController extends Controller
{
    public function __construct(
        protected ExportService $exportService
    ) {}

    /**
     * Export Word
     *
     * @queryParam language string th|en|both. Default: both
     */
    public function word(Request $request, Article $article): Response
    {
        $this->authorize('view', $article);
        $language = $request->get('language', 'both');

        $result = $this->exportService->export($article, 'docx', $language);

        return response($result['buffer'])
            ->header('Content-Type', $result['mime'])
            ->header('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"')
            ->header('X-Export-Warnings', json_encode($result['warnings']));
    }

    /**
     * Export PDF
     */
    public function pdf(Request $request, Article $article): Response
    {
        $this->authorize('view', $article);
        $language = $request->get('language', 'both');

        $result = $this->exportService->export($article, 'pdf', $language);

        return response($result['buffer'])
            ->header('Content-Type', $result['mime'])
            ->header('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"')
            ->header('X-Export-Warnings', json_encode($result['warnings']));
    }

    /**
     * Validate ก่อน export — return warnings เท่านั้น
     */
    public function validate(Request $request, Article $article)
    {
        $this->authorize('view', $article);
        $language = $request->get('language', 'both');

        $result = $this->exportService->export($article, 'docx', $language);
        return response()->json(['warnings' => $result['warnings']]);
    }
}
