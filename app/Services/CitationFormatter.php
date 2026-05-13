<?php

namespace App\Services;

/**
 * CitationFormatter
 *
 * Format citation 8 รูปแบบตาม spec มาตรฐาน NIDA
 * (ใช้เป็นมาตรฐานเดียวกันสำหรับ มทร.สุวรรณภูมิ)
 *
 * - Deterministic — ไม่ใช้ AI ในการ format (AI ใช้แค่ extract data จาก URL/free-form)
 * - HTML <i></i> สำหรับ italic (จะแปลง runtime ตอน render Word/PDF)
 */
class CitationFormatter
{
    /**
     * Main entry point — format ตาม type
     *
     * @param string $type     book|article|article_in_book|newspaper|thesis|website|unpublished|other
     * @param array  $data     structured data
     * @param string $language th|en
     *
     * @return array{footnote: string, bibliography: string}
     */
    public function format(string $type, array $data, string $language = 'th'): array
    {
        $method = 'format' . str_replace('_', '', ucwords($type, '_'));

        if (!method_exists($this, $method)) {
            throw new \InvalidArgumentException("Unknown citation type: {$type}");
        }

        return $this->{$method}($data, $language);
    }

    /**
     * Format repeat citation (Ibid./op.cit./เรื่องเดียวกัน/เรื่องเดิม)
     *
     * @param string $style       ibid|op_cit|same_doc
     * @param string $lang        th|en
     * @param string|null $authorName ใช้กับ op_cit
     * @param string|null $pages
     */
    public function formatRepeat(
        string $style,
        string $lang,
        ?string $authorName = null,
        ?string $pages = null
    ): string {
        switch ($style) {
            case 'ibid':
                if ($pages) {
                    return $lang === 'th'
                        ? "เรื่องเดียวกัน, หน้า {$pages}."
                        : "Ibid., " . $this->pageMarker($pages, $lang) . ".";
                }
                return $lang === 'th' ? 'เรื่องเดียวกัน.' : 'Ibid.';

            case 'op_cit':
                $name = $authorName ?? '';
                if ($lang === 'th') {
                    $tail = $pages ? ", หน้า {$pages}" : '';
                    return ltrim("{$name}, เรื่องเดิม{$tail}.", ', ');
                }
                $tail = $pages ? ', ' . $this->pageMarker($pages, $lang) : '';
                return ltrim("{$name}, op. cit.{$tail}.", ', ');

            case 'same_doc':
                $name = $authorName ?? '';
                if ($lang === 'th') {
                    $tail = $pages ? ", หน้า {$pages}" : '';
                    return ltrim("{$name}, เรื่องเดียวกัน{$tail}.", ', ');
                }
                $tail = $pages ? ', ' . $this->pageMarker($pages, $lang) : '';
                return ltrim("{$name}, ibid.{$tail}.", ', ');
        }
        return '';
    }

    // ============ Helpers ============

    protected function it(string $s): string
    {
        return "<i>{$s}</i>";
    }

    protected function authorsForFootnote(array $authors, string $lang): string
    {
        if (empty($authors)) return '';
        if (count($authors) === 1) return $authors[0];
        if (count($authors) === 2) {
            return $lang === 'th'
                ? "{$authors[0]} และ {$authors[1]}"
                : "{$authors[0]} and {$authors[1]}";
        }
        $last = array_pop($authors);
        $rest = implode(', ', $authors);
        return $lang === 'th' ? "{$rest} และ {$last}" : "{$rest}, and {$last}";
    }

    protected function authorsForBibliography(array $authors, string $lang): string
    {
        if (empty($authors)) return '';
        if (count($authors) === 1) return $authors[0];
        if ($lang === 'th') {
            return $this->authorsForFootnote($authors, $lang);
        }
        // English: keep natural order
        $first = array_shift($authors);
        return $first . ' and ' . implode(' and ', $authors);
    }

    protected function pageMarker(?string $pages, string $lang): string
    {
        if (!$pages) return '';
        $isMulti = (bool) preg_match('/[-–,]/', $pages);
        if ($lang === 'th') return "หน้า {$pages}";
        return ($isMulti ? 'pp.' : 'p.') . " {$pages}";
    }

    // ============ 1. Book ============

    protected function formatBook(array $d, string $lang): array
    {
        $authorsFn  = $this->authorsForFootnote($d['authors'] ?? [], $lang);
        $authorsBib = $this->authorsForBibliography($d['authors'] ?? [], $lang);
        $title      = $d['title'] ?? '';
        $edition    = $d['edition'] ?? '';
        $city       = $d['city'] ?? '';
        $publisher  = $d['publisher'] ?? '';
        $year       = $d['year'] ?? '';
        $pages      = $d['pages'] ?? null;

        $place = "{$city}: {$publisher}, {$year}";

        // Footnote
        $fn = "{$authorsFn}, " . $this->it($title);
        if ($edition) $fn .= ", {$edition}";
        $fn .= " ({$place})";
        if ($pages) $fn .= ", " . $this->pageMarker($pages, $lang);
        $fn .= ".";

        // Bibliography
        $bib = "{$authorsBib}. " . $this->it($title) . ".";
        if ($edition) $bib .= " {$edition}.";
        $bib .= " {$city}: {$publisher}, {$year}.";

        return ['footnote' => $fn, 'bibliography' => $bib];
    }

    // ============ 2. Article (Journal) ============

    protected function formatArticle(array $d, string $lang): array
    {
        $authorsFn  = $this->authorsForFootnote($d['authors'] ?? [], $lang);
        $authorsBib = $this->authorsForBibliography($d['authors'] ?? [], $lang);
        $title      = $d['title'] ?? '';
        $journal    = $d['journal'] ?? '';
        $volume     = $d['volume'] ?? '';
        $issue      = $d['issue'] ?? '';
        $monthYear  = $d['monthYear'] ?? $d['month_year'] ?? '';
        $pages      = $d['pages'] ?? '';

        $volIssue = trim(implode(', ', array_filter([$volume, $issue])));

        $fn = "{$authorsFn}, \"{$title},\" " . $this->it($journal) . " {$volIssue} ({$monthYear}): {$pages}.";
        $bib = "{$authorsBib}. {$title}. " . $this->it($journal) . " {$volIssue} ({$monthYear}): {$pages}.";

        return ['footnote' => $fn, 'bibliography' => $bib];
    }

    // ============ 3. Article in Book ============

    protected function formatArticleInBook(array $d, string $lang): array
    {
        $authorsFn  = $this->authorsForFootnote($d['authors'] ?? [], $lang);
        $authorsBib = $this->authorsForBibliography($d['authors'] ?? [], $lang);
        $articleTitle = $d['articleTitle'] ?? $d['article_title'] ?? '';
        $bookTitle    = $d['bookTitle'] ?? $d['book_title'] ?? '';
        $editors      = $d['editors'] ?? [];
        $edition      = $d['edition'] ?? '';
        $city         = $d['city'] ?? '';
        $publisher    = $d['publisher'] ?? '';
        $year         = $d['year'] ?? '';
        $pages        = $d['pages'] ?? null;

        $editorPart = !empty($editors)
            ? ($lang === 'th' ? 'รวบรวมโดย ' . implode(', ', $editors) : 'ed. ' . implode(', ', $editors))
            : '';
        $inWord = $lang === 'th' ? 'ใน' : 'in';
        $place = "{$city}: {$publisher}, {$year}";

        $fn = "{$authorsFn}, \"{$articleTitle},\" {$inWord} " . $this->it($bookTitle);
        if ($editorPart) $fn .= ", {$editorPart}";
        if ($edition) $fn .= ", {$edition}";
        $fn .= " ({$place})";
        if ($pages) $fn .= ", " . $this->pageMarker($pages, $lang);
        $fn .= ".";

        $bib = "{$authorsBib}. {$articleTitle}. {$inWord} ";
        if ($lang === 'th' && $editorPart) {
            $bib .= "{$editorPart} (บรรณาธิการ). ";
        } elseif ($editorPart) {
            $bib .= "{$editorPart}. ";
        }
        $bib .= $this->it($bookTitle) . ". {$city}: {$publisher}, {$year}.";
        if ($pages) {
            $bib .= $lang === 'th' ? " หน้า {$pages}." : " Pp. {$pages}.";
        }

        return ['footnote' => $fn, 'bibliography' => $bib];
    }

    // ============ 4. Newspaper ============

    protected function formatNewspaper(array $d, string $lang): array
    {
        $authors = $d['authors'] ?? [];
        $authorsFn  = !empty($authors) ? $this->authorsForFootnote($authors, $lang) : '';
        $authorsBib = !empty($authors) ? $this->authorsForBibliography($authors, $lang) : '';
        $title      = $d['title'] ?? '';
        $newspaper  = $d['newspaper'] ?? '';
        $date       = $d['date'] ?? '';
        $pages      = $d['pages'] ?? '';

        $fn = '';
        if ($authorsFn) $fn .= "{$authorsFn}, ";
        $fn .= "\"{$title},\" " . $this->it($newspaper) . " ({$date}): {$pages}.";

        $bib = '';
        if ($authorsBib) $bib .= "{$authorsBib}. ";
        $bib .= "{$title}. " . $this->it($newspaper) . " ({$date}): {$pages}.";

        return ['footnote' => $fn, 'bibliography' => $bib];
    }

    // ============ 5. Thesis ============

    protected function formatThesis(array $d, string $lang): array
    {
        $authorsFn  = $this->authorsForFootnote($d['authors'] ?? [], $lang);
        $authorsBib = $this->authorsForBibliography($d['authors'] ?? [], $lang);
        $title      = $d['title'] ?? '';
        $degreeAndDept = $d['degreeAndDept'] ?? $d['degree_and_dept'] ?? '';
        $year       = $d['year'] ?? '';
        $pages      = $d['pages'] ?? null;

        $fn = "{$authorsFn}, " . $this->it($title) . ", ({$degreeAndDept}, {$year})";
        if ($pages) $fn .= ", " . $this->pageMarker($pages, $lang);
        $fn .= ".";

        $bib = "{$authorsBib}. " . $this->it($title) . ". {$degreeAndDept}, {$year}.";

        return ['footnote' => $fn, 'bibliography' => $bib];
    }

    // ============ 6. Website ============

    protected function formatWebsite(array $d, string $lang): array
    {
        $baseType = $d['baseType'] ?? $d['base_type'] ?? 'book';
        $base = $d['base'] ?? [];
        $retrievedDate = $d['retrievedDate'] ?? $d['retrieved_date'] ?? '';
        $url = $d['url'] ?? '';

        $baseFormatted = $this->format($baseType, $base, $lang);
        $retrievedFn  = $lang === 'th'
            ? "ค้นวันที่ {$retrievedDate} จาก {$url}"
            : "Retrieved {$retrievedDate}, from {$url}";
        $retrievedBib = $lang === 'th'
            ? "ค้นวันที่ {$retrievedDate}. จาก {$url}"
            : "Retrieved {$retrievedDate}. From {$url}";

        $fnBody = preg_replace('/\.$/', '', $baseFormatted['footnote']);
        $bibBody = preg_replace('/\.$/', '', $baseFormatted['bibliography']);

        return [
            'footnote'     => "{$fnBody}, {$retrievedFn}.",
            'bibliography' => "{$bibBody}. {$retrievedBib}.",
        ];
    }

    // ============ 7. Unpublished ============

    protected function formatUnpublished(array $d, string $lang): array
    {
        $authorsFn  = $this->authorsForFootnote($d['authors'] ?? [], $lang);
        $authorsBib = $this->authorsForBibliography($d['authors'] ?? [], $lang);
        $title      = $d['title'] ?? '';
        $context    = $d['context'] ?? '';
        $organizer  = $d['organizer'] ?? '';
        $date       = $d['date'] ?? '';
        $pages      = $d['pages'] ?? null;

        $tag = $lang === 'th' ? '(เอกสารไม่ตีพิมพ์เผยแพร่)' : '(Unpublished Manuscript)';
        $orgPart = $organizer ? ", {$organizer}" : '';

        $fn = "{$authorsFn}, \"{$title},\" " . $this->it($context) . "{$orgPart}, {$date}";
        if ($pages) $fn .= ", " . $this->pageMarker($pages, $lang);
        $fn .= ". {$tag}";

        $bib = "{$authorsBib}. {$title}. " . $this->it($context) . "{$orgPart}, {$date}. {$tag}";

        return ['footnote' => $fn, 'bibliography' => $bib];
    }

    // ============ 8. Other (free-form) ============

    protected function formatOther(array $d, string $lang): array
    {
        $text = trim($d['freeForm'] ?? $d['free_form'] ?? '');
        $text = rtrim($text, '.') . '.';
        return ['footnote' => $text, 'bibliography' => $text];
    }
}
