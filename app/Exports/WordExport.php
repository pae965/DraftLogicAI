<?php

namespace App\Exports;

use App\Models\Article;
use App\Services\AbstractService;
use App\Services\SectionService;
use App\Services\TiptapConverter;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\IOFactory;

/**
 * Export article เป็น Word (.docx) ตาม spec มาตรฐาน NIDA
 *
 * Spec:
 *   - กระดาษ A4 หน้าเดียว, ≤ 25 หน้า
 *   - margin 1" ทั้ง 4 ด้าน
 *   - ฟอนต์ TH Sarabun New
 *   - line spacing single
 *   - 18pt Bold center = title
 *   - 16pt center = author
 *   - 16pt Bold = section heading
 *   - 16pt = body
 *   - 14pt = footnote
 */
class WordExport
{
    protected const FONT = 'TH Sarabun New';
    protected const FONT_FALLBACK = 'Sarabun';

    public function __construct(
        protected SectionService $sectionService,
        protected AbstractService $abstractService,
        protected TiptapConverter $tiptap
    ) {}

    /**
     * สร้าง .docx เป็น string buffer
     */
    public function build(Article $article, string $language = 'both'): string
    {
        $article->load(['authors', 'sections', 'abstracts', 'keywords', 'citations', 'citationUses.citation']);

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName(self::FONT);
        $phpWord->setDefaultFontSize(16);

        // ===== A4 page setup with 1" margins =====
        $section = $phpWord->addSection([
            'paperSize'    => 'A4',
            'orientation'  => 'portrait',
            'marginTop'    => Converter::inchToTwip(1),
            'marginRight'  => Converter::inchToTwip(1),
            'marginBottom' => Converter::inchToTwip(1),
            'marginLeft'   => Converter::inchToTwip(1),
        ]);

        $includeTH = in_array($language, ['th', 'both'], true);
        $includeEN = in_array($language, ['en', 'both'], true);

        // ===== Title =====
        $this->renderTitle($section, $article, $includeTH, $includeEN);

        // ===== Authors =====
        $this->renderAuthors($section, $article, $includeTH, $includeEN);

        // ===== Spacer =====
        $section->addTextBreak(1);

        // ===== Sections (ordered, visible only) =====
        $sections = $this->sectionService->getOrderedSections($article, includeHidden: false);
        $numbering = $this->sectionService->computeNumbering($sections);
        $abstracts = $this->abstractService->getApproved($article);

        foreach ($sections as $sec) {
            $this->renderSection($section, $sec, $article, $numbering, $abstracts, $includeTH, $includeEN);
        }

        // ===== Bibliography (centered heading per spec) =====
        $this->renderBibliography($section, $article);

        // ===== Footnotes for IS info + author affiliation =====
        // (PhpWord จะจัดการ footnote auto-numbering)
        $this->attachFootnotes($section, $article, $includeTH, $includeEN);

        // ===== Save to memory =====
        $tempFile = tempnam(sys_get_temp_dir(), 'docx_');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);
        $content = file_get_contents($tempFile);
        unlink($tempFile);

        return $content;
    }

    protected function renderTitle(Section $section, Article $article, bool $includeTH, bool $includeEN): void
    {
        $titleStyle = ['name' => self::FONT, 'size' => 18, 'bold' => true];
        $paraStyle = ['alignment' => 'center', 'spaceAfter' => 60];

        if ($includeTH && $article->title_th) {
            $textRun = $section->addTextRun($paraStyle);
            $textRun->addText($article->title_th, $titleStyle);
            // footnote * (1) สำหรับ IS info
            $footnote = $textRun->addFootnote();
            $footnote->addText(
                $this->buildIsFootnoteText($article, 'th'),
                ['name' => self::FONT, 'size' => 14],
                ['indentation' => ['firstLine' => Converter::cmToTwip(1)]]
            );
        }

        if ($includeEN && $article->title_en) {
            $section->addText($article->title_en, $titleStyle, $paraStyle);
        }
    }

    protected function renderAuthors(Section $section, Article $article, bool $includeTH, bool $includeEN): void
    {
        $authorStyle = ['name' => self::FONT, 'size' => 16];
        $paraStyle = ['alignment' => 'center'];

        foreach ($article->authors as $author) {
            $isAdvisor = $author->isAdvisor();

            if ($includeTH) {
                $nameTH = $isAdvisor
                    ? "อาจารย์ที่ปรึกษา: " . $author->getDisplayName('th')
                    : $author->getDisplayName('th');

                $textRun = $section->addTextRun($paraStyle);
                $textRun->addText($nameTH, $authorStyle);

                // เพิ่ม footnote สำหรับ author info (เฉพาะที่ไม่ใช่ advisor)
                if (!$isAdvisor && ($author->affiliation_th || $author->address_th || $author->email)) {
                    $fn = $textRun->addFootnote();
                    $fn->addText(
                        $this->buildAuthorFootnoteText($author, 'th', $article),
                        ['name' => self::FONT, 'size' => 14],
                        ['indentation' => ['firstLine' => Converter::cmToTwip(1)]]
                    );
                }
            }

            if ($includeEN) {
                $nameEN = $isAdvisor
                    ? "Advisor: " . $author->getDisplayName('en')
                    : $author->getDisplayName('en');
                $section->addText(
                    $nameEN,
                    array_merge($authorStyle, ['italic' => true]),
                    $paraStyle
                );
            }
        }
    }

    protected function renderSection(
        Section $docSection,
        $sec,
        Article $article,
        array $numbering,
        array $abstracts,
        bool $includeTH,
        bool $includeEN
    ): void {
        switch ($sec->type) {
            case 'abstract':
                if ($includeTH) {
                    $this->renderHeading($docSection, 'บทคัดย่อ', false);
                    $this->renderBodyText($docSection, $abstracts['th'] ?? '');
                }
                break;

            case 'abstract_en':
                if ($includeEN) {
                    $this->renderHeading($docSection, 'Abstract', false);
                    $this->renderBodyText($docSection, $abstracts['en'] ?? '');
                }
                break;

            case 'keywords':
                $this->renderKeywords($docSection, $article, $includeTH, $includeEN);
                break;

            case 'bibliography':
                // จัดการแยกใน renderBibliography()
                break;

            case 'richtext':
                $num = $numbering[$sec->id] ?? null;
                $heading = $num ? "{$num}. " : '';
                $heading .= $includeTH ? $sec->label_th : $sec->label_en;

                $this->renderHeading($docSection, $heading, true);

                $content = $sec->content ?? ($includeTH ? $sec->content_th : $sec->content_en);
                $plainText = $this->tiptap->toPlainText($content);
                $this->renderBodyText($docSection, $plainText);
                break;
        }
    }

    protected function renderHeading(Section $section, string $text, bool $numbered): void
    {
        $section->addText(
            $text,
            ['name' => self::FONT, 'size' => 16, 'bold' => true],
            ['alignment' => 'left', 'spaceBefore' => 120, 'spaceAfter' => 60]
        );
    }

    protected function renderBodyText(Section $section, string $text): void
    {
        if (empty($text)) return;

        $paragraphs = preg_split('/\n{2,}/', trim($text));
        foreach ($paragraphs as $para) {
            if (empty(trim($para))) continue;
            $section->addText(
                $para,
                ['name' => self::FONT, 'size' => 16],
                [
                    'alignment'  => 'both',
                    'indentation'=> ['firstLine' => Converter::cmToTwip(1)],
                    'spaceAfter' => 60,
                ]
            );
        }
    }

    protected function renderKeywords(Section $docSection, Article $article, bool $includeTH, bool $includeEN): void
    {
        $kwTH = $article->keywords->where('language', 'th')->pluck('keyword')->all();
        $kwEN = $article->keywords->where('language', 'en')->pluck('keyword')->all();

        if ($includeTH && !empty($kwTH)) {
            $textRun = $docSection->addTextRun(['alignment' => 'left', 'spaceAfter' => 120]);
            $textRun->addText('คำสำคัญ: ', ['name' => self::FONT, 'size' => 16, 'bold' => true]);
            $textRun->addText(implode(', ', $kwTH), ['name' => self::FONT, 'size' => 16]);
        }
        if ($includeEN && !empty($kwEN)) {
            $textRun = $docSection->addTextRun(['alignment' => 'left', 'spaceAfter' => 240]);
            $textRun->addText('Keywords: ', ['name' => self::FONT, 'size' => 16, 'bold' => true]);
            $textRun->addText(implode(', ', $kwEN), ['name' => self::FONT, 'size' => 16]);
        }
    }

    protected function renderBibliography(Section $docSection, Article $article): void
    {
        $usedCitationIds = $article->citationUses->pluck('citation_id')->unique();
        $usedCitations = $article->citations->whereIn('id', $usedCitationIds);

        if ($usedCitations->isEmpty()) return;

        // sort: TH first, then EN, alphabetical
        $sorted = $usedCitations->sort(function ($a, $b) {
            if ($a->language !== $b->language) {
                return $a->language === 'th' ? -1 : 1;
            }
            return strcmp($a->formatted_bibliography ?? '', $b->formatted_bibliography ?? '');
        });

        // CENTERED heading per spec
        $docSection->addText(
            'บรรณานุกรม',
            ['name' => self::FONT, 'size' => 16, 'bold' => true],
            ['alignment' => 'center', 'spaceBefore' => 240, 'spaceAfter' => 240]
        );

        foreach ($sorted as $cit) {
            $entry = $cit->formatted_bibliography ?? '';
            $entry = $this->convertItalicMarkers($entry);

            // hanging indent 1cm
            $textRun = $docSection->addTextRun([
                'alignment'   => 'left',
                'spaceAfter'  => 60,
                'indentation' => [
                    'left'    => Converter::cmToTwip(1),
                    'hanging' => Converter::cmToTwip(1),
                ],
            ]);
            $this->addInlineRuns($textRun, $entry, 16);
        }
    }

    protected function attachFootnotes(Section $section, Article $article, bool $includeTH, bool $includeEN): void
    {
        // citation footnotes are added inline via section content
        // (already attached during renderTitle / renderAuthors / etc.)
    }

    protected function buildIsFootnoteText(Article $article, string $lang): string
    {
        $isTitle = $lang === 'th'
            ? ($article->independent_study_title_th ?? $article->title_th)
            : ($article->independent_study_title_en ?? $article->title_en);

        if ($lang === 'th') {
            return sprintf(
                'บทความนี้เป็นส่วนหนึ่งของการค้นคว้าอิสระ เรื่อง "%s" หลักสูตร%s %s %s',
                $isTitle,
                $article->degree_program_th ?? 'นิติศาสตรมหาบัณฑิต',
                $article->faculty_th ?? 'คณะนิติศาสตร์',
                $article->institution_th ?? 'มหาวิทยาลัยเทคโนโลยีราชมงคลสุวรรณภูมิ'
            );
        }
        return sprintf(
            'This article is part of the Independent Study titled "%s", %s, %s, %s',
            $isTitle,
            $article->degree_program_en ?? 'Master of Laws',
            $article->faculty_en ?? 'Faculty of Law',
            $article->institution_en ?? 'Rajamangala University of Technology Suvarnabhumi'
        );
    }

    protected function buildAuthorFootnoteText($author, string $lang, Article $article): string
    {
        $parts = [];

        if ($lang === 'th') {
            $parts[] = sprintf(
                'นักศึกษาหลักสูตร%s %s %s',
                $article->degree_program_th ?? 'นิติศาสตรมหาบัณฑิต',
                $article->faculty_th ?? 'คณะนิติศาสตร์',
                $article->institution_th ?? 'มหาวิทยาลัยเทคโนโลยีราชมงคลสุวรรณภูมิ'
            );
            if ($author->address_th) $parts[] = "ที่อยู่: {$author->address_th}";
            if ($author->email) $parts[] = "E-mail: {$author->email}";
            if ($author->orcid_id) $parts[] = "ORCID: {$author->orcid_id}";
        } else {
            $parts[] = sprintf(
                'Student of %s, %s, %s',
                $author->affiliation_en ?? $article->degree_program_en ?? 'Master of Laws',
                $article->faculty_en ?? 'Faculty of Law',
                $article->institution_en ?? 'RUS'
            );
            if ($author->address_en) $parts[] = "Address: {$author->address_en}";
            if ($author->email) $parts[] = "E-mail: {$author->email}";
        }

        return implode(' | ', $parts);
    }

    /**
     * Convert <i>...</i> เป็น italic runs
     */
    protected function addInlineRuns($textRun, string $text, int $size): void
    {
        $parts = preg_split('/(<i>.*?<\/i>)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $part) {
            if (preg_match('/^<i>(.*)<\/i>$/u', $part, $m)) {
                $textRun->addText($m[1], ['name' => self::FONT, 'size' => $size, 'italic' => true]);
            } elseif (!empty($part)) {
                $textRun->addText($part, ['name' => self::FONT, 'size' => $size]);
            }
        }
    }

    protected function convertItalicMarkers(string $s): string
    {
        // ensure <i></i> markers ใช้งานได้ตอน split
        return $s;
    }
}
