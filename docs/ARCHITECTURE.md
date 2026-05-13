# 🏗 Architecture Overview

## High-Level Diagram

```
┌─────────────────────────────────────────────────────────┐
│                     User (Browser)                       │
└────────┬───────────────────────────┬────────────────────┘
         │                           │
         ▼                           ▼
┌────────────────┐          ┌──────────────────┐
│  Tiptap Editor │          │ Filament Admin   │
│  (Vanilla JS)  │          │ (PHP/Livewire)   │
└────────┬───────┘          └────────┬─────────┘
         │                           │
         │  REST API + Sanctum Auth  │
         │                           │
         ▼                           ▼
┌─────────────────────────────────────────────────────────┐
│              Laravel 8 Application Layer                 │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌─────────┐ │
│  │Articles  │  │Sections  │  │Citations │  │Exports  │ │
│  │Controller│  │Controller│  │Controller│  │Controller│ │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬────┘ │
│       │             │             │              │      │
│       └─────────────┴─────────────┴──────────────┘      │
│                          │                              │
│  ┌──────────────────────┴────────────────────────┐    │
│  │         Service Layer                           │    │
│  │  ┌──────────────┐  ┌─────────────────┐        │    │
│  │  │SectionService│  │CitationFormatter│        │    │
│  │  └──────────────┘  └─────────────────┘        │    │
│  │  ┌──────────────┐  ┌─────────────────┐        │    │
│  │  │AbstractServ. │  │  AIService      │        │    │
│  │  └──────────────┘  └────────┬────────┘        │    │
│  │                              │                  │    │
│  │       ┌──────────────────────┴─────────────┐    │    │
│  │       ▼                                    ▼    │    │
│  │  ┌──────────────┐  ┌──────────┐  ┌──────────┐  │    │
│  │  │ClaudeProvider│  │OpenAI    │  │Gemini    │  │    │
│  │  └──────┬───────┘  └─────┬────┘  └─────┬────┘  │    │
│  └────────│────────────────│──────────────│──────┘    │
│           │                │              │           │
└───────────│────────────────│──────────────│───────────┘
            │                │              │
            ▼                ▼              ▼
       Anthropic         OpenAI         Google
        API              API            Gemini API

┌────────────────────────────────────────────────────────┐
│                 MySQL 5.7+ (DraftLogicAI)              │
│  utf8mb4_unicode_ci, JSON columns for Tiptap content   │
└────────────────────────────────────────────────────────┘
```

## Layers

### 1. Presentation Layer
- **Filament 2.17** — Admin Panel (CRUD ทั้งหมด)
- **Tiptap Editor** — Frontend editor สำหรับ user
- **Blade Views** — หน้า public/dashboard

### 2. API Layer
- **Sanctum auth** — token-based สำหรับ frontend
- **Policies** — authorization (ArticlePolicy, etc.)
- **Form Requests** — validation
- **Scribe** — auto-generate API docs

### 3. Service Layer (Business Logic)

| Service | Responsibility |
|---|---|
| `SectionService` | template instantiation, section CRUD, numbering |
| `CitationFormatter` | format 8 types × 2 langs |
| `AICitationService` | 3 modes (manual/lookup/reformat) |
| `AbstractService` | manual + AI translate + approval |
| `AIService` | provider selection + logging |
| `TiptapConverter` | JSON ↔ HTML/PhpWord |
| `WordExport` | PhpWord rendering |
| `PdfExport` | mPDF rendering |
| `ExportService` | orchestrator + validation |

### 4. Data Layer
- **Eloquent models** with relationships
- **Soft deletes** บนตารางหลัก
- **Encrypted fields** (`ai_settings.api_key`)
- **JSON columns** สำหรับ Tiptap content

## Key Design Patterns

### Strategy Pattern (AI Providers)
ทุก provider implement `AIProvider` interface → `AIProviderFactory` เลือก concrete class ตาม user setting

### Snapshot Pattern (Templates)
ตอนสร้าง article → snapshot template ลง `articles.template_snapshot` (JSON) เพื่อให้บทความเก่าไม่เสียหายเมื่อ template เปลี่ยน

### Hook-Based Auto-Sectioning
`Article::created` event → `SectionService::instantiateForArticle()` สร้าง sections อัตโนมัติ

### Encrypted At-Rest
`AiSetting::setApiKeyAttribute()` ใช้ `Crypt::encryptString()` (Laravel) เก็บ key encrypted

## Database Relationships

```
User
 ├─ hasMany  Article (primary_author)
 ├─ hasMany  ArticleAuthor
 ├─ hasMany  AiSetting
 └─ hasMany  UserTemplateAssignment

Article
 ├─ belongsTo  User (primary_author)
 ├─ belongsTo  SectionTemplate
 ├─ hasMany    ArticleSection
 ├─ hasMany    ArticleAuthor
 ├─ hasMany    ArticleAbstract  (TH + EN)
 ├─ hasMany    ArticleKeyword
 ├─ hasMany    Citation
 └─ hasMany    CitationUse

SectionTemplate
 ├─ hasMany    SectionTemplateItem
 └─ hasMany    UserTemplateAssignment

Citation
 └─ hasMany    CitationUse

ArticleSection
 └─ hasMany    CitationUse
```

## Concurrency & Auto-Save

- Tiptap → debounced save (1.5s) → PATCH `/api/articles/{id}/sections/{section}`
- Last-write-wins (no optimistic lock)
- Future: ETag/version field สำหรับ collaborative editing

## Performance Considerations

- Eager loading ใน Article model: `with(['authors', 'sections', ...])`
- JSON column queries ใช้ `JSON_EXTRACT` (MySQL 5.7+)
- Indexes บน foreign keys + status + slug
- Soft delete ผ่าน `deleted_at` index

## Security

- Sanctum token rotation
- Policies enforce author/editor boundaries
- API keys encrypted at rest (`Crypt::encryptString`)
- CSRF protected routes
- XSS protected via Blade `{{ $var }}` + Tiptap sanitization
- SQL injection protected via Eloquent

## Future Enhancements

- WebSocket real-time collaboration (Pusher/Reverb)
- Full-text search (MeiliSearch)
- Citation cycle detection (รายการที่อ้างอิงกันเอง)
- Plagiarism check integration
- ORCID OAuth import
- Export to LaTeX
