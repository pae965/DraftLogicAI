# 📖 User Guide

คู่มือการใช้งาน RUS Research CMS สำหรับนักศึกษา/อาจารย์

## 👤 Roles

| Role | สิทธิ์ |
|---|---|
| **Super Admin** | จัดการทุกอย่าง รวมถึง roles อื่น ๆ |
| **Admin** | จัดการ users + templates + บทความทั้งหมด |
| **Editor** | ดู/แก้บทความทั้งหมด ตรวจสอบ + อนุมัติ |
| **Author** | สร้าง/แก้บทความของตัวเอง + co-authored |

## 📝 สร้างบทความใหม่

### ผ่าน Admin Panel (Filament)
1. Login → `/admin`
2. ไปที่ **บทความ** → **+ สร้างใหม่**
3. กรอก:
   - ชื่อบทความ TH + EN
   - เลือก Template (`rus_strict_v1` แนะนำ)
   - กรอก IS info (ชื่อ Independent Study, หลักสูตร, คณะ)
4. **บันทึก**
5. ระบบจะสร้าง sections ตาม template อัตโนมัติ

### ผ่าน API
```bash
curl -X POST http://localhost:8000/api/articles \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title_th": "มาตรการทางกฎหมาย...",
    "title_en": "The Legal Measures...",
    "primary_language": "th"
  }'
```

## ✍️ เขียนเนื้อหา (Section Editor)

1. ในหน้าแก้ไขบทความ คลิก section ที่ต้องการ
2. ใช้ toolbar:
   - **B** = Bold
   - **I** = Italic
   - **U** = Underline
   - **H2/H3** = Heading
   - **• List** / **1. List** = Lists
   - **🔗** = Link
   - **📚 Cite** = แทรก citation
3. ระบบ auto-save ทุก 1.5 วินาทีหลังหยุดพิมพ์

## 🎨 ปรับแต่งหัวข้อบทความ

### แก้ชื่อหัวข้อ
1. คลิก section → แก้ `label_th` / `label_en` → บันทึก
2. ตัวอย่าง: เปลี่ยน "ขอบเขตการศึกษา" → "ขอบเขตการวิจัย"

### เพิ่มหัวข้อแทรก
ผ่าน API:
```bash
POST /api/articles/{id}/sections/insert
{
  "after_order": 4,
  "label_th": "วิธีการศึกษา",
  "label_en": "Methodology"
}
```

ระบบจะ:
- แทรก section ใหม่ระหว่างหัวข้อที่ 4 กับ 5
- shift หัวข้อหลังให้ลำดับเลื่อนไป
- **renumber อัตโนมัติ**

### ลบหัวข้อ
ผ่าน API:
```bash
DELETE /api/articles/{id}/sections/{section}
```

### Drag-Reorder (Phase 3)
ใน UI: ลาก ↕ icon เพื่อจัดเรียงใหม่ → ระบบ POST `/sections/reorder`

## 📚 จัดการการอ้างอิง (Citations)

### Mode A: กรอกเอง (ไม่ใช้ AI)
```bash
POST /api/articles/{id}/citations/manual
{
  "citation_type": "book",
  "language": "th",
  "data": {
    "authors": ["กมลชัย รัตนสกาววงศ์"],
    "title": "กฎหมายปกครอง",
    "edition": "พิมพ์ครั้งที่ 8",
    "city": "กรุงเทพมหานคร",
    "publisher": "วิญญูชน",
    "year": "2554"
  }
}
```

ระบบจะ format อัตโนมัติเป็น:
- **Footnote:** `กมลชัย รัตนสกาววงศ์, กฎหมายปกครอง, พิมพ์ครั้งที่ 8 (กรุงเทพมหานคร: วิญญูชน, 2554).`
- **Bibliography:** `กมลชัย รัตนสกาววงศ์. กฎหมายปกครอง. พิมพ์ครั้งที่ 8. กรุงเทพมหานคร: วิญญูชน, 2554.`

### Mode B: ค้นจาก URL/ISBN (AI ช่วย)
```bash
POST /api/articles/{id}/citations/lookup
{
  "language": "th",
  "url": "https://www.example.com/book"
}
```
AI จะ:
1. ดึง metadata จาก URL
2. แปลงเป็นโครงสร้าง
3. Format ตามรูปแบบมาตรฐาน

### Mode C: แปลงข้อความ (Free-form)
```bash
POST /api/articles/{id}/citations/reformat
{
  "raw_text": "John Smith, Public Law (Oxford 2020) p.45",
  "language": "en"
}
```
AI parse แล้ว format ใหม่ให้ถูกต้อง

## 🌍 บทคัดย่อสองภาษา (Abstract)

### กรอก Abstract (Manual)
```bash
POST /api/articles/{id}/abstracts
{
  "language": "th",
  "content_text": "การศึกษาวิจัยครั้งนี้มีวัตถุประสงค์..."
}
```

### แปล Abstract ด้วย AI
```bash
POST /api/articles/{id}/abstracts/translate
{
  "source_language": "th",
  "target_language": "en"
}
```

### Approve Abstract
```bash
POST /api/articles/{id}/abstracts/approve
{ "language": "en" }
```

## 🤖 ตั้งค่า AI (BYOK)

แต่ละ user ต้องมี API key ของตัวเอง

```bash
POST /api/ai/settings
{
  "provider": "claude",
  "api_key": "sk-ant-...",
  "model_default": "claude-3-5-sonnet-20241022",
  "is_active": true
}
```

API key จะถูก encrypt ก่อนเก็บ — admin เองก็อ่านไม่ได้

## 📤 Export

### Word
```
GET /api/articles/{id}/export/word?language=both
```
- `language=th` — ภาษาไทยเท่านั้น
- `language=en` — ภาษาอังกฤษเท่านั้น
- `language=both` — ทั้ง 2 ภาษา

### PDF
```
GET /api/articles/{id}/export/pdf?language=both
```

### ตรวจสอบก่อน export (warnings)
```
GET /api/articles/{id}/export/validate
```
จะ return:
```json
{
  "warnings": [
    "Article has no authors",
    "Thai abstract not approved or empty"
  ]
}
```

## ⚠️ Common Issues

### "Source abstract has no content"
แปล abstract ไม่ได้ — กรอก source language ก่อน

### "User has no active AI setting"
ตั้งค่า `ai/settings` ก่อนใช้ AI features

### Section ลบไม่ได้
ตรวจ `required` ของ template item — section ที่ required=true ลบไม่ได้

### Footnote เลขผิด
ระบบ auto-renumber ตอน export — ไม่ต้องตั้งเลขเอง

## 🔄 Workflow แนะนำ

1. **สร้างบทความ** (เลือก template)
2. **เพิ่มผู้เขียน** + advisor
3. **กรอกบทคัดย่อ TH** → AI แปล EN → Approve ทั้งคู่
4. **ใส่คำสำคัญ** TH+EN (3-5 คำ)
5. **เขียนเนื้อหาแต่ละ section** (Tiptap)
6. **เพิ่ม citations** ตาม mode ที่เหมาะสม
7. **แทรก citation marker** ในเนื้อหา (📚 button)
8. **Validate** → แก้ warnings
9. **Export** → ส่งคณะ
