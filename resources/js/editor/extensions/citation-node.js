/**
 * Custom Tiptap Node for Citations (Footnotes)
 *
 * Renders as <sup class="citation">[N]</sup> in HTML
 * Stores citation_id and footnote number in JSON
 */

import { Node, mergeAttributes } from '@tiptap/core';

export const CitationNode = Node.create({
    name: 'citation',

    group: 'inline',
    inline: true,
    atom: true,
    selectable: true,

    addAttributes() {
        return {
            number: {
                default: null,
                parseHTML: el => parseInt(el.getAttribute('data-number'), 10),
                renderHTML: attrs => ({ 'data-number': attrs.number }),
            },
            citationId: {
                default: null,
                parseHTML: el => el.getAttribute('data-citation-id'),
                renderHTML: attrs => ({ 'data-citation-id': attrs.citationId }),
            },
            pages: {
                default: null,
                parseHTML: el => el.getAttribute('data-pages'),
                renderHTML: attrs => attrs.pages ? { 'data-pages': attrs.pages } : {},
            },
        };
    },

    parseHTML() {
        return [
            { tag: 'sup.citation' },
            { tag: 'sup[data-citation-id]' },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return [
            'sup',
            mergeAttributes(HTMLAttributes, { class: 'citation' }),
            `[${HTMLAttributes['data-number'] ?? '?'}]`,
        ];
    },

    addCommands() {
        return {
            insertCitation: (attrs) => ({ commands }) => {
                return commands.insertContent({
                    type: this.name,
                    attrs,
                });
            },
        };
    },
});
