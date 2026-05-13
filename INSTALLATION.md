# 📥 Installation Guide

คู่มือติดตั้ง RUS Research CMS แบบ step-by-step

## 📋 Prerequisites

- **PHP 7.4.13** (หรือสูงกว่าใน 7.4.x)
- **MySQL 5.7+** (recommended 5.7.22 or 8.0+)
- **Composer 2.x**
- **Node.js 16+ + npm**
- **Web server**: Apache/Nginx (production) หรือ artisan serve (dev)

## ⚙️ PHP Extensions ที่ต้องเปิด

```ini
extension=mbstring
extension=openssl
extension=pdo
extension=pdo_mysql
extension=tokenizer
extension=xml
extension=ctype
extension=json
extension=bcmath
extension=fileinfo
extension=gd          # สำหรับ image upload
extension=zip         # สำหรับ PhpWord
extension=intl        # สำหรับ locale handling
```

## 🚀 ขั้นตอนติดตั้ง

### 1. Clone & Install

```bash
git clone <your-repo> research-cms-rus
cd research-cms-rus

# Install PHP dependencies
composer install

# Install JS dependencies
npm install
```

### 2. Configure .env

```bash
cp .env.example .env
php artisan key:generate
```

**แก้ไขใน .env:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=DraftLogicAI            # ใช้ชื่อนี้ตามที่กำหนด
DB_USERNAME=root
DB_PASSWORD=your-password

# AI keys (optional — user สามารถใช้ BYOK ได้)
ANTHROPIC_API_KEY=sk-ant-...
OPENAI_API_KEY=sk-...
GEMINI_API_KEY=...
```

### 3. ตรวจสอบ MySQL

```sql
-- เช็ค database มีและใช้ utf8mb4
SHOW CREATE DATABASE DraftLogicAI;
-- ควรเห็น: CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci

-- เช็ค max_allowed_packet (ต้อง ≥ 64MB)
SHOW VARIABLES LIKE 'max_allowed_packet';
```

ถ้ายังไม่มี database:
```sql
CREATE DATABASE DraftLogicAI
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

### 4. Migrate + Seed

```bash
# สร้าง tables ทั้งหมด
php artisan migrate

# Seed templates + default super admin
php artisan db:seed
```

### 5. ติดตั้ง Jetstream

```bash
php artisan jetstream:install livewire --teams=false
php artisan migrate
```

### 6. ติดตั้ง Filament

```bash
php artisan vendor:publish --tag=filament-config
php artisan filament:upgrade
```

### 7. ติดตั้งฟอนต์ TH Sarabun New

ดาวน์โหลดฟอนต์จาก Sipa หรือแหล่งทางการ → วางใน:

```
public/fonts/th-sarabun-new/
├── THSarabunNew.ttf
├── THSarabunNew Bold.ttf
├── THSarabunNew Italic.ttf
└── THSarabunNew BoldItalic.ttf
```

### 8. Build Frontend

```bash
npm run dev
# หรือ production
npm run prod
```

### 9. Generate API Docs

```bash
php artisan scribe:generate
# Docs จะอยู่ที่ /docs (URL)
```

### 10. Start Server

**Development:**
```bash
php artisan serve
# เข้าที่ http://localhost:8000
# Admin: http://localhost:8000/admin
```

**Production (Nginx):**
- ตั้ง document root ที่ `public/`
- ตั้ง permissions: `chmod -R 775 storage bootstrap/cache`

## 🔐 Default Login

หลัง seed:
- **Email:** `admin@rus.ac.th`
- **Password:** `password`

⚠️ **เปลี่ยนรหัสผ่านทันที!**

```bash
php artisan tinker
>>> $user = App\Models\User::where('email', 'admin@rus.ac.th')->first();
>>> $user->password = Hash::make('your-strong-password');
>>> $user->save();
```

## 🧪 Verify Installation

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Test seeded templates
>>> App\Models\SectionTemplate::pluck('key');
# ควรเห็น: ['rus_strict_v1', 'rus_flexible_v1', 'rus_minimal_v1']

# Test super admin
>>> App\Models\User::where('role', 'super_admin')->first();
```

## 🐛 Troubleshooting

### ปัญหา: MySQL collation error
```
SQLSTATE[42000]: Syntax error... 'utf8mb4_unicode_ci'
```

**แก้:** ตรวจ MySQL version ≥ 5.7.7
```sql
SELECT VERSION();
```

### ปัญหา: Class 'Filament\FilamentServiceProvider' not found
```bash
composer require filament/filament:^2.17
php artisan vendor:publish --tag=filament-config
```

### ปัญหา: Tiptap import error
ใช้ `npm install` ใหม่ และตรวจ `package.json` มี `@tiptap/core` เวอร์ชัน ^2.1.0

### ปัญหา: PDF Thai ตัวอักษรเป็น "?"
ตรวจฟอนต์ใน `public/fonts/th-sarabun-new/` มีครบ 4 ไฟล์

### ปัญหา: Filament 2 + Livewire 2 ขัดกัน
ตรวจ `composer.json` ให้ใช้ `livewire/livewire:^2.12` ไม่ใช่ ^3.x

## 🔄 Update Procedure

```bash
git pull
composer install
npm install
npm run prod
php artisan migrate
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🚢 Deployment Notes

### Production checklist
- [ ] `APP_ENV=production` + `APP_DEBUG=false`
- [ ] เปลี่ยนรหัสผ่าน super_admin
- [ ] Setup HTTPS (Let's Encrypt)
- [ ] Setup queue worker (`php artisan queue:work`)
- [ ] Setup cron: `* * * * * php artisan schedule:run`
- [ ] Setup backup database (mysqldump cron)
- [ ] Set max_execution_time ≥ 60s (สำหรับ AI calls)
- [ ] ตั้ง storage path: `chmod -R 775 storage`

### ⚠️ Security warning

**Laravel 8 EOL ตั้งแต่ Jan 2023** — ไม่มี security update ทางการแล้ว
- ผ่าน Anthropic prompt: ใช้ stack นี้ตามที่ user ยืนยัน
- **แนะนำ:** วางแผน upgrade เป็น PHP 8.1+ + Laravel 11 ภายใน 2026

ดู [PROJECT_HANDOFF.md](PROJECT_HANDOFF.md) สำหรับ upgrade path

---

📚 **Next steps:**
- อ่าน [README.md](README.md) สำหรับ overview
- ดู [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) สำหรับ technical detail
- ดู [docs/USER_GUIDE.md](docs/USER_GUIDE.md) สำหรับวิธีใช้งาน
