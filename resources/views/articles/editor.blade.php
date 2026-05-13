@extends('layouts.app')

@section('content')
<div class="py-8 max-w-6xl mx-auto px-4">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $article->title_th }}</h1>
            <p class="text-gray-600 text-sm">{{ $article->title_en }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ url('/api/articles/' . $article->id . '/export/word?language=both') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded">{{ __('messages.export.word') }}</a>
            <a href="{{ url('/api/articles/' . $article->id . '/export/pdf?language=both') }}"
               class="px-4 py-2 bg-red-600 text-white rounded">{{ __('messages.export.pdf') }}</a>
        </div>
    </div>

    {{-- Toolbar --}}
    <div id="editor-toolbar" class="bg-gray-100 border rounded p-2 mb-4 flex gap-1 flex-wrap">
        <button data-action="bold" class="px-3 py-1 hover:bg-gray-200 rounded font-bold">B</button>
        <button data-action="italic" class="px-3 py-1 hover:bg-gray-200 rounded italic">I</button>
        <button data-action="underline" class="px-3 py-1 hover:bg-gray-200 rounded underline">U</button>
        <span class="border-l mx-1"></span>
        <button data-action="h2" class="px-3 py-1 hover:bg-gray-200 rounded">H2</button>
        <button data-action="h3" class="px-3 py-1 hover:bg-gray-200 rounded">H3</button>
        <span class="border-l mx-1"></span>
        <button data-action="bulletList" class="px-3 py-1 hover:bg-gray-200 rounded">• List</button>
        <button data-action="orderedList" class="px-3 py-1 hover:bg-gray-200 rounded">1. List</button>
        <button data-action="blockquote" class="px-3 py-1 hover:bg-gray-200 rounded">"Quote"</button>
        <span class="border-l mx-1"></span>
        <button data-action="link" class="px-3 py-1 hover:bg-gray-200 rounded">🔗</button>
        <button data-action="citation" class="px-3 py-1 hover:bg-gray-200 rounded">📚 Cite</button>
        <span class="border-l mx-1"></span>
        <button data-action="undo" class="px-3 py-1 hover:bg-gray-200 rounded">↶</button>
        <button data-action="redo" class="px-3 py-1 hover:bg-gray-200 rounded">↷</button>
    </div>

    {{-- Sections --}}
    @foreach($article->sections->where('visible', true)->sortBy('order') as $section)
        <div class="bg-white border rounded mb-4">
            <div class="bg-gray-50 px-4 py-2 border-b flex justify-between items-center">
                <h3 class="font-semibold">
                    {{ $section->order }}. {{ app()->getLocale() === 'th' ? $section->label_th : $section->label_en }}
                </h3>
                <span class="text-xs text-gray-500">{{ $section->type }}</span>
            </div>
            <div id="editor-{{ $section->id }}" class="tiptap-editor"></div>
        </div>
    @endforeach
</div>

@push('scripts')
<script type="module">
    import { initSectionEditor } from '{{ asset('js/editor/tiptap-init.js') }}';

    @foreach($article->sections->where('visible', true)->where('type', 'richtext') as $section)
        initSectionEditor('editor-{{ $section->id }}', {
            initialContent: @json($section->content ?? null),
            saveUrl: '{{ url("/api/articles/{$article->id}/sections/{$section->id}") }}',
            csrfToken: '{{ csrf_token() }}',
            toolbarId: 'editor-toolbar',
            placeholder: 'เริ่มเขียนเนื้อหาในส่วน "{{ $section->label_th }}"...',
        });
    @endforeach
</script>
@endpush
@endsection
