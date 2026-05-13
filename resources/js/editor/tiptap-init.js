/**
 * Tiptap Editor Initialization
 * RUS Research CMS — Section Editor
 *
 * Usage in Blade:
 *   <div id="editor-{{ $section->id }}" class="tiptap-editor"></div>
 *   <script type="module">
 *     import { initSectionEditor } from '/resources/js/editor/tiptap-init.js';
 *     initSectionEditor('editor-{{ $section->id }}', {
 *       initialContent: @json($section->content),
 *       saveUrl: '/api/articles/{{ $article->id }}/sections/{{ $section->id }}',
 *       csrfToken: '{{ csrf_token() }}',
 *     });
 *   </script>
 */

import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Underline from '@tiptap/extension-underline';
import Placeholder from '@tiptap/extension-placeholder';
import { CitationNode } from './extensions/citation-node.js';

/**
 * Initialize a section editor
 */
export function initSectionEditor(elementId, options = {}) {
    const el = document.getElementById(elementId);
    if (!el) {
        console.error(`Element ${elementId} not found`);
        return null;
    }

    const editor = new Editor({
        element: el,
        extensions: [
            StarterKit,
            Underline,
            Link.configure({ openOnClick: false }),
            Placeholder.configure({
                placeholder: options.placeholder || 'เริ่มเขียนเนื้อหา...',
            }),
            CitationNode,
        ],
        content: options.initialContent || '',
        editorProps: {
            attributes: {
                class: 'prose prose-thai focus:outline-none min-h-[200px] p-4',
            },
        },
        onUpdate: ({ editor }) => {
            // debounced auto-save
            clearTimeout(el._saveTimer);
            el._saveTimer = setTimeout(() => {
                saveContent(editor.getJSON(), options);
            }, 1500);
        },
    });

    // Toolbar setup
    if (options.toolbarId) {
        setupToolbar(options.toolbarId, editor);
    }

    return editor;
}

function setupToolbar(toolbarId, editor) {
    const toolbar = document.getElementById(toolbarId);
    if (!toolbar) return;

    toolbar.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const action = btn.dataset.action;

            switch (action) {
                case 'bold':       editor.chain().focus().toggleBold().run(); break;
                case 'italic':     editor.chain().focus().toggleItalic().run(); break;
                case 'underline':  editor.chain().focus().toggleUnderline().run(); break;
                case 'h2':         editor.chain().focus().toggleHeading({ level: 2 }).run(); break;
                case 'h3':         editor.chain().focus().toggleHeading({ level: 3 }).run(); break;
                case 'bulletList': editor.chain().focus().toggleBulletList().run(); break;
                case 'orderedList':editor.chain().focus().toggleOrderedList().run(); break;
                case 'blockquote': editor.chain().focus().toggleBlockquote().run(); break;
                case 'undo':       editor.chain().focus().undo().run(); break;
                case 'redo':       editor.chain().focus().redo().run(); break;
                case 'link': {
                    const url = window.prompt('URL', '');
                    if (url) {
                        editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
                    }
                    break;
                }
                case 'citation': {
                    if (typeof options.onCitation === 'function') {
                        options.onCitation((number) => {
                            editor.chain().focus().insertContent({
                                type: 'citation',
                                attrs: { number },
                            }).run();
                        });
                    }
                    break;
                }
            }
        });
    });
}

/**
 * Save content via API
 */
async function saveContent(jsonContent, options) {
    if (!options.saveUrl) return;

    try {
        const response = await fetch(options.saveUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': options.csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ content: jsonContent }),
            credentials: 'same-origin',
        });

        if (!response.ok) {
            console.error('Failed to save section', response.status);
        }
    } catch (e) {
        console.error('Save error:', e);
    }
}
