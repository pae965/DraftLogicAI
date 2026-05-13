# 📚 RUS Research CMS

ระบบจัดการบทความวิจัย/ค้นคว้าอิสระ — **คณะนิติศาสตร์ มหาวิทยาลัยเทคโนโลยีราชมงคลสุวรรณภูมิ**

[![PHP](https://img.shields.io/badge/PHP-7.4-blue)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-8.x-red)](https://laravel.com/docs/8.x)
[![Filament](https://img.shields.io/badge/Filament-2.17-orange)](https://filamentphp.com/docs/2.x)

## ✨ Features

- 📝 **Bilingual content** — ทุก field รองรับไทย+อังกฤษ
- 🎨 **Tiptap rich-text editor** — แก้ไขเนื้อหาแบบ visual
- 📑 **Section templates** — 3 templates พร้อมใช้ + ปรับแต่งได้อิสระ
- 🤖 **Multi-provider AI** — Claude / OpenAI / Gemini (BYOK)
- 📚 **Citation manager** — 8 รูปแบบ × 2 ภาษา + 3 modes (manual/lookup/reformat)
- 🌍 **Abstract translation** — TH ↔ EN ผ่าน AI พร้อม approval workflow
- 📄 **Export Word + PDF** — ตามมาตรฐาน RUS Faculty of Law
- 👮 **Role-based access** — Super Admin / Admin / Editor / Author
- 🔐 **Encrypted API keys** — BYOK พร้อม Laravel Crypt encryption

## 🛠 Tech Stack

| Component | Version |
|---|---|
| PHP | 7.4.13 |
| Laravel | 8.83 |
| MySQL | 5.7+ (utf8mb4) |
| Filament | 2.17 (Admin Panel) |
| Jetstream | 2.x (Auth) |
| Livewire | 2.x |
| Tiptap | 2.x (vanilla JS) |
| PhpWord | 0.18+ (Word export) |
| mPDF | 8.x (PDF export) |
| Scribe | 3.32 (API docs) |

## 📦 Installation

ดูรายละเอียดใน [INSTALLATION.md](INSTALLATION.md)

```bash
# 1. Install dependencies
composer install
npm install

# 2. Configure .env
cp .env.example .env
php artisan key:generate
# แก้ DB_DATABASE=DraftLogicAI ใน .env

# 3. Run migrations + seeders
php artisan migrate --seed

# 4. Install Jetstream
php artisan jetstream:install livewire --teams=false

# 5. Build frontend
npm run dev

# 6. Generate API docs
php artisan scribe:generate

# 7. Start server
php artisan serve
```

## 📝 Default Login

หลัง seed:
- Email: `admin@rus.ac.th`
- Password: `password`
- ⚠️ **เปลี่ยนรหัสผ่านทันทีใน production!**

## 📂 Project Structure

```
research-cms-rus/
├── app/
│   ├── Models/            # 12 Eloquent models
│   ├── Services/          # SectionService, CitationFormatter, AbstractService
│   │   └── AI/            # Multi-provider AI (Claude/OpenAI/Gemini)
│   ├── Exports/           # Word + PDF generators
│   ├── Filament/          # Admin Panel resources
│   └── Http/Controllers/  # API endpoints
├── database/
│   ├── migrations/        # 13 migrations
│   └── seeders/           # 3 templates + super admin
├── resources/
│   ├── js/editor/         # Tiptap integration
│   ├── lang/{th,en}/      # i18n
│   └── views/articles/    # Editor blade
├── routes/
│   ├── api.php            # REST API
│   └── web.php            # Web routes
└── docs/
    ├── ARCHITECTURE.md
    ├── INSTALLATION.md
    ├── CITATION_SCHEMA.md
    └── USER_GUIDE.md
```

## 🔌 API Endpoints

### Articles
- `GET    /api/articles` — list
- `POST   /api/articles` — create
- `GET    /api/articles/{id}` — show
- `PATCH  /api/articles/{id}` — update
- `DELETE /api/articles/{id}` — delete

### Sections
- `GET    /api/articles/{id}/sections`
- `PATCH  /api/articles/{id}/sections/{section}`
- `POST   /api/articles/{id}/sections/insert`
- `POST   /api/articles/{id}/sections/reorder`
- `DELETE /api/articles/{id}/sections/{section}`

### Citations (3 modes)
- `POST /api/articles/{id}/citations/manual`
- `POST /api/articles/{id}/citations/lookup`
- `POST /api/articles/{id}/citations/reformat`

### Export
- `GET /api/articles/{id}/export/word?language=both|th|en`
- `GET /api/articles/{id}/export/pdf?language=both|th|en`

### AI
- `GET  /api/ai/settings` — list user's BYOK
- `POST /api/ai/settings` — save BYOK
- `POST /api/articles/{id}/abstracts/translate` — TH↔EN

## 🎨 Section Templates (built-in)

| Key | Sections | Use case |
|---|---|---|
| `rus_strict_v1` | 7 มาตรฐาน + abstract/keywords/biblio | งานค้นคว้าอิสระตามมาตรฐานคณะ |
| `rus_flexible_v1` | + ทบทวนวรรณกรรม + กิตติกรรมประกาศ | บทความวารสารทั่วไป |
| `rus_minimal_v1` | 5 หัวข้อหลัก (รวมขอบเขต) | บทความสั้น/กระชับ |

## 📜 Spec อ้างอิง

- มาตรฐานการตีพิมพ์: NIDA Faculty of Law (ใช้เป็น base)
- ฟอนต์: TH Sarabun New
- กระดาษ: A4 ≤25 หน้า, margin 1″ ทั้ง 4 ด้าน
- ขนาดตัวอักษร: title 18pt / heading 16pt bold / body 16pt / footnote 14pt

## 🔐 License

MIT — Free to use for educational and commercial purposes.

## 👥 Credits

- มหาวิทยาลัยเทคโนโลยีราชมงคลสุวรรณภูมิ — คณะนิติศาสตร์
- Built with Claude (Anthropic AI)

---

📖 ดูเพิ่มเติม: [docs/](docs/) | [INSTALLATION.md](INSTALLATION.md) | [PROJECT_HANDOFF.md](PROJECT_HANDOFF.md)
