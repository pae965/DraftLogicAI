<?php

namespace App\Services;

/**
 * Convert Tiptap JSON ↔ HTML / Plain Text
 *
 * Tiptap JSON format example:
 * {
 *   "type": "doc",
 *   "content": [
 *     {
 *       "type": "paragraph",
 *       "content": [
 *         { "type": "text", "text": "Hello" },
 *         { "type": "text", "text": "world", "marks": [{"type": "bold"}] }
 *       ]
 *     }
 *   ]
 * }
 */
class TiptapConverter
{
    /**
     * Tiptap JSON → plain text (สำหรับ search/SEO)
     */
    public function toPlainText(?array $tiptapJson): string
    {
        if (empty($tiptapJson)) return '';

        $output = [];
        $this->walkPlain($tiptapJson, $output);
        return trim(implode('', $output));
    }

    protected function walkPlain($node, array &$output): void
    {
        if (!is_array($node)) return;

        if (($node['type'] ?? '') === 'text') {
            $output[] = $node['text'] ?? '';
            return;
        }

        if (!empty($node['content']) && is_array($node['content'])) {
            foreach ($node['content'] as $child) {
                $this->walkPlain($child, $output);
            }
        }

        // paragraph break
        if (in_array($node['type'] ?? '', ['paragraph', 'heading'], true)) {
            $output[] = "\n\n";
        }

        // root doc node
        if (($node['type'] ?? '') === 'doc') {
            // already handled via children
        }
    }

    /**
     * Tiptap JSON → HTML
     */
    public function toHtml(?array $tiptapJson): string
    {
        if (empty($tiptapJson)) return '';

        return $this->renderNode($tiptapJson);
    }

    protected function renderNode(array $node): string
    {
        $type = $node['type'] ?? 'text';
        $content = $node['content'] ?? [];

        switch ($type) {
            case 'doc':
                return $this->renderChildren($content);

            case 'paragraph':
                return '<p>' . $this->renderChildren($content) . '</p>';

            case 'heading':
                $level = $node['attrs']['level'] ?? 2;
                return "<h{$level}>" . $this->renderChildren($content) . "</h{$level}>";

            case 'bulletList':
                return '<ul>' . $this->renderChildren($content) . '</ul>';

            case 'orderedList':
                return '<ol>' . $this->renderChildren($content) . '</ol>';

            case 'listItem':
                return '<li>' . $this->renderChildren($content) . '</li>';

            case 'blockquote':
                return '<blockquote>' . $this->renderChildren($content) . '</blockquote>';

            case 'hardBreak':
                return '<br>';

            case 'horizontalRule':
                return '<hr>';

            case 'text':
                return $this->renderText($node);

            case 'citation':
                // custom node สำหรับ footnote
                $number = $node['attrs']['number'] ?? '?';
                return "<sup class=\"citation\">[{$number}]</sup>";

            default:
                return $this->renderChildren($content);
        }
    }

    protected function renderChildren(array $content): string
    {
        $html = '';
        foreach ($content as $child) {
            $html .= $this->renderNode($child);
        }
        return $html;
    }

    protected function renderText(array $node): string
    {
        $text = htmlspecialchars($node['text'] ?? '', ENT_QUOTES, 'UTF-8');
        $marks = $node['marks'] ?? [];

        foreach ($marks as $mark) {
            switch ($mark['type'] ?? '') {
                case 'bold':       $text = "<strong>{$text}</strong>"; break;
                case 'italic':     $text = "<em>{$text}</em>"; break;
                case 'underline':  $text = "<u>{$text}</u>"; break;
                case 'strike':     $text = "<s>{$text}</s>"; break;
                case 'code':       $text = "<code>{$text}</code>"; break;
                case 'link':
                    $href = htmlspecialchars($mark['attrs']['href'] ?? '#', ENT_QUOTES, 'UTF-8');
                    $text = "<a href=\"{$href}\">{$text}</a>";
                    break;
            }
        }

        return $text;
    }

    /**
     * แตก Tiptap JSON เป็น array ของ blocks สำหรับ PhpWord rendering
     *
     * @return array<array{type: string, text: string, marks: array}>
     */
    public function toPhpWordBlocks(?array $tiptapJson): array
    {
        if (empty($tiptapJson)) return [];

        $blocks = [];
        $this->walkBlocks($tiptapJson, $blocks);
        return $blocks;
    }

    protected function walkBlocks(array $node, array &$blocks, array $inheritedMarks = []): void
    {
        $type = $node['type'] ?? '';

        if ($type === 'paragraph') {
            $blocks[] = ['type' => 'paragraph_start'];
            foreach ($node['content'] ?? [] as $child) {
                $this->walkBlocks($child, $blocks, $inheritedMarks);
            }
            $blocks[] = ['type' => 'paragraph_end'];
            return;
        }

        if ($type === 'text') {
            $marks = array_merge($inheritedMarks, $node['marks'] ?? []);
            $blocks[] = [
                'type'  => 'text',
                'text'  => $node['text'] ?? '',
                'marks' => $marks,
            ];
            return;
        }

        if ($type === 'citation') {
            $blocks[] = [
                'type'   => 'citation',
                'number' => $node['attrs']['number'] ?? null,
            ];
            return;
        }

        // walk children
        foreach ($node['content'] ?? [] as $child) {
            $this->walkBlocks($child, $blocks, $inheritedMarks);
        }
    }
}
