<?php

namespace App\Exports;

use App\Models\Article;
use Illuminate\Support\Str;

/**
 * Export orchestrator
 */
class ExportService
{
    public function __construct(
        protected WordExport $wordExport,
        protected PdfExport $pdfExport
    ) {}

    /**
     * Export article ตาม format ที่เลือก
     *
     * @return array{buffer: string, mime: string, filename: string, warnings: array}
     */
    public function export(Article $article, string $format, string $language = 'both'): array
    {
        $warnings = $this->validateBeforeExport($article, $language);

        if ($format === 'docx') {
            $buffer = $this->wordExport->build($article, $language);
            $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
            $ext = 'docx';
        } elseif ($format === 'pdf') {
            $buffer = $this->pdfExport->build($article, $language);
            $mime = 'application/pdf';
            $ext = 'pdf';
        } else {
            throw new \InvalidArgumentException("Unknown format: {$format}");
        }

        $safeName = Str::slug(
            $article->title_th ?? $article->title_en ?? 'article',
            '-',
            null
        );
        $safeName = preg_replace('/[\/\\\\?%*:|"<>]/', '_', $safeName);
        $filename = mb_substr($safeName, 0, 80) . ".{$ext}";

        return [
            'buffer'   => $buffer,
            'mime'     => $mime,
            'filename' => $filename,
            'warnings' => $warnings,
        ];
    }

    /**
     * ตรวจสอบบทความก่อน export — return warnings
     */
    protected function validateBeforeExport(Article $article, string $language): array
    {
        $warnings = [];

        if ($article->authors()->count() === 0) {
            $warnings[] = 'Article has no authors';
        }

        $abstracts = $article->abstracts()
            ->where('approved_by_author', true)
            ->pluck('language')
            ->all();

        if (in_array($language, ['th', 'both'], true) && !in_array('th', $abstracts, true)) {
            $warnings[] = 'Thai abstract not approved or empty';
        }
        if (in_array($language, ['en', 'both'], true) && !in_array('en', $abstracts, true)) {
            $warnings[] = 'English abstract not approved or empty';
        }

        return $warnings;
    }
}
