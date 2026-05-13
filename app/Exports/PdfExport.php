<?php

namespace App\Exports;

use App\Models\Article;
use App\Services\AbstractService;
use App\Services\SectionService;
use App\Services\TiptapConverter;
use Mpdf\Mpdf;

/**
 * Export article เป็น PDF ด้วย mPDF
 *
 * mPDF รองรับภาษาไทยและฟอนต์ TH Sarabun New ดีที่สุดใน PHP world
 *
 * Setup ฟอนต์:
 *   1. วางไฟล์ .ttf ใน public/fonts/th-sarabun-new/
 *   2. config ตามนี้ใน boot()
 *   3. ดู: https://mpdf.github.io/fonts-languages/fonts-in-mpdf-7-x.html
 */
class PdfExport
{
    public function __construct(
        protected SectionService $sectionService,
        protected AbstractService $abstractService,
        protected TiptapConverter $tiptap
    ) {}

    /**
     * Build PDF as binary string
     */
    public function build(Article $article, string $language = 'both'): string
    {
        $article->load(['authors', 'sections', 'abstracts', 'keywords', 'citations', 'citationUses.citation']);

        $mpdf = $this->createMpdf();
        $html = $this->buildHtml($article, $language);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('', 'S'); // string output
    }

    protected function createMpdf(): Mpdf
    {
        $fontDir = public_path('fonts/th-sarabun-new');
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();

        return new Mpdf([
            'mode'             => 'utf-8',
            'format'           => 'A4',
            'margin_top'       => 25.4,    // 1 inch in mm
            'margin_right'     => 25.4,
            'margin_bottom'    => 25.4,
            'margin_left'      => 25.4,
            'fontDir'          => array_merge(
                $defaultConfig['fontDir'],
                [$fontDir]
            ),
            'fontdata'         => array_merge(
                $defaultFontConfig['fontdata'],
                [
                    'thsarabunnew' => [
                        'R'  => 'THSarabunNew.ttf',
                        'B'  => 'THSarabunNew Bold.ttf',
                        'I'  => 'THSarabunNew Italic.ttf',
                        'BI' => 'THSarabunNew BoldItalic.ttf',
                        'useOTL' => 0xFF,
                        'useKashida' => 75,
                    ],
                ]
            ),
            'default_font'      => 'thsarabunnew',
            'default_font_size' => 16,
            'autoScriptToLang'  => true,
            'autoLangToFont'    => true,
        ]);
    }

    protected function buildHtml(Article $article, string $language): string
    {
        $includeTH = in_array($language, ['th', 'both'], true);
        $includeEN = in_array($language, ['en', 'both'], true);

        $sections = $this->sectionService->getOrderedSections($article, false);
        $numbering = $this->sectionService->computeNumbering($sections);
        $abstracts = $this->abstractService->getApproved($article);

        $html = '<style>
            body { font-family: thsarabunnew; font-size: 16pt; line-height: 1.0; }
            h1.title { font-size: 18pt; font-weight: bold; text-align: center; margin: 0; }
            h2.author { font-size: 16pt; font-weight: normal; text-align: center; margin: 0; }
            h3.section { font-size: 16pt; font-weight: bold; margin-top: 12pt; margin-bottom: 6pt; }
            p.body { font-size: 16pt; text-indent: 1cm; text-align: justify; margin: 6pt 0; }
            p.bib { font-size: 16pt; text-indent: -1cm; padding-left: 1cm; margin: 6pt 0; }
            h3.bib-heading { font-size: 16pt; font-weight: bold; text-align: center; margin: 12pt 0; }
            sup.cite { font-size: 12pt; }
            i { font-style: italic; }
        </style>';

        // ===== Title =====
        if ($includeTH && $article->title_th) {
            $html .= '<h1 class="title">' . e($article->title_th) . '<sup class="cite">*</sup></h1>';
        }
        if ($includeEN && $article->title_en) {
            $html .= '<h1 class="title">' . e($article->title_en) . '</h1>';
        }

        // ===== Authors =====
        $authorMarkers = '*';
        foreach ($article->authors as $idx => $author) {
            $isAdvisor = $author->isAdvisor();
            $marker = '';
            if (!$isAdvisor) {
                $authorMarkers .= '*';
                $marker = '<sup class="cite">' . str_repeat('*', strlen($authorMarkers) - 1) . '</sup>';
            }

            if ($includeTH) {
                $name = $isAdvisor
                    ? "อาจารย์ที่ปรึกษา: " . $author->getDisplayName('th')
                    : $author->getDisplayName('th');
                $html .= '<h2 class="author">' . e($name) . $marker . '</h2>';
            }
            if ($includeEN) {
                $nameEn = $isAdvisor
                    ? "Advisor: " . $author->getDisplayName('en')
                    : $author->getDisplayName('en');
                $html .= '<h2 class="author"><em>' . e($nameEn) . '</em></h2>';
            }
        }

        $html .= '<br>';

        // ===== Sections =====
        foreach ($sections as $sec) {
            $html .= $this->renderSectionHtml($sec, $article, $numbering, $abstracts, $includeTH, $includeEN);
        }

        // ===== Bibliography =====
        $html .= $this->renderBibliographyHtml($article);

        return $html;
    }

    protected function renderSectionHtml($sec, Article $article, array $numbering, array $abstracts, bool $includeTH, bool $includeEN): string
    {
        switch ($sec->type) {
            case 'abstract':
                if ($includeTH) {
                    return '<h3 class="section">บทคัดย่อ</h3><p class="body">' . e($abstracts['th'] ?? '') . '</p>';
                }
                return '';

            case 'abstract_en':
                if ($includeEN) {
                    return '<h3 class="section">Abstract</h3><p class="body">' . e($abstracts['en'] ?? '') . '</p>';
                }
                return '';

            case 'keywords':
                $kwTH = $article->keywords->where('language', 'th')->pluck('keyword')->all();
                $kwEN = $article->keywords->where('language', 'en')->pluck('keyword')->all();
                $html = '';
                if ($includeTH && !empty($kwTH)) {
                    $html .= '<p class="body" style="text-indent: 0;"><strong>คำสำคัญ:</strong> ' . e(implode(', ', $kwTH)) . '</p>';
                }
                if ($includeEN && !empty($kwEN)) {
                    $html .= '<p class="body" style="text-indent: 0;"><strong>Keywords:</strong> ' . e(implode(', ', $kwEN)) . '</p>';
                }
                return $html;

            case 'bibliography':
                return ''; // handled in renderBibliographyHtml

            case 'richtext':
                $num = $numbering[$sec->id] ?? null;
                $heading = ($num ? "{$num}. " : '') . ($includeTH ? $sec->label_th : $sec->label_en);
                $content = $sec->content ?? ($includeTH ? $sec->content_th : $sec->content_en);
                $html = '<h3 class="section">' . e($heading) . '</h3>';
                $plainText = $this->tiptap->toPlainText($content);
                foreach (preg_split('/\n{2,}/', trim($plainText)) as $para) {
                    if (!empty(trim($para))) {
                        $html .= '<p class="body">' . e($para) . '</p>';
                    }
                }
                return $html;
        }
        return '';
    }

    protected function renderBibliographyHtml(Article $article): string
    {
        $usedIds = $article->citationUses->pluck('citation_id')->unique();
        $cits = $article->citations->whereIn('id', $usedIds);
        if ($cits->isEmpty()) return '';

        $sorted = $cits->sort(function ($a, $b) {
            if ($a->language !== $b->language) return $a->language === 'th' ? -1 : 1;
            return strcmp($a->formatted_bibliography ?? '', $b->formatted_bibliography ?? '');
        });

        $html = '<h3 class="bib-heading">บรรณานุกรม</h3>';
        foreach ($sorted as $c) {
            // <i>...</i> เป็น italic ใน HTML
            $html .= '<p class="bib">' . $c->formatted_bibliography . '</p>';
        }
        return $html;
    }
}
