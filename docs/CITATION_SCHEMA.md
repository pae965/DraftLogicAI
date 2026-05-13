# 📑 Citation Schema Reference

8 รูปแบบ citation × 2 ภาษา (TH/EN) ตามมาตรฐาน NIDA Faculty of Law (ใช้สำหรับ RUS)

## Common: ฟิลด์ที่ใช้ทุกประเภท

```json
{
  "authors": ["string", ...],   // array ของชื่อผู้เขียน
}
```

---

## 1. `book` — หนังสือ

```json
{
  "authors": ["กมลชัย รัตนสกาววงศ์"],
  "title": "กฎหมายปกครอง",
  "edition": "พิมพ์ครั้งที่ 8",     // optional
  "city": "กรุงเทพมหานคร",
  "publisher": "วิญญูชน",
  "year": "2554",
  "pages": "120"                    // optional, footnote ใช้
}
```

**Output (TH):**
- Footnote: `กมลชัย รัตนสกาววงศ์, <i>กฎหมายปกครอง</i>, พิมพ์ครั้งที่ 8 (กรุงเทพมหานคร: วิญญูชน, 2554), หน้า 120.`
- Bibliography: `กมลชัย รัตนสกาววงศ์. <i>กฎหมายปกครอง</i>. พิมพ์ครั้งที่ 8. กรุงเทพมหานคร: วิญญูชน, 2554.`

## 2. `article` — บทความวารสาร

```json
{
  "authors": ["สมยศ เชื้อไทย"],
  "title": "การกระทำทางปกครอง",
  "journal": "วารสารนิติศาสตร์",
  "volume": "ปีที่ 30",
  "issue": "ฉบับที่ 2",
  "monthYear": "มิถุนายน 2543",
  "pages": "1-25"
}
```

## 3. `article_in_book` — บทความในหนังสือ

```json
{
  "authors": ["...","..."],
  "articleTitle": "...",
  "bookTitle": "...",
  "editors": ["..."],
  "edition": "...",
  "city": "...",
  "publisher": "...",
  "year": "...",
  "pages": "..."
}
```

## 4. `newspaper` — หนังสือพิมพ์

```json
{
  "authors": ["..."],         // optional
  "title": "ชื่อบทความ",
  "newspaper": "ไทยรัฐ",
  "date": "1 มกราคม 2566",
  "pages": "5"
}
```

## 5. `thesis` — วิทยานิพนธ์

```json
{
  "authors": ["กัสยา สุพล"],
  "title": "มาตรการทางกฎหมายในการบังคับชำระหนี้...",
  "degreeAndDept": "วิทยานิพนธ์นิติศาสตรมหาบัณฑิต สาขาวิชานิติศาสตร์ บัณฑิตวิทยาลัย",
  "year": "2554",
  "pages": "45"
}
```

## 6. `website` — เว็บไซต์ (compound type)

```json
{
  "baseType": "book",          // หรือ article, etc.
  "base": { ... },              // ตามรูปแบบของ baseType
  "retrievedDate": "14 กันยายน 2559",
  "url": "http://www.pub-law.net"
}
```

**Output (TH):** `<base citation>, ค้นวันที่ 14 กันยายน 2559 จาก http://www.pub-law.net.`

## 7. `unpublished` — ไม่ตีพิมพ์

```json
{
  "authors": ["..."],
  "title": "...",
  "context": "เอกสารประกอบการประชุม",
  "organizer": "คณะนิติศาสตร์",     // optional
  "date": "10 มีนาคม 2566",
  "pages": "..."
}
```

ลงท้ายด้วย `(เอกสารไม่ตีพิมพ์เผยแพร่)` / `(Unpublished Manuscript)`

## 8. `other` — รูปแบบเอกสาร

```json
{
  "freeForm": "ข้อความ citation ทั้งหมดที่ user กรอก"
}
```

ระบบไม่ format — ใช้ตามที่ user กรอก

---

## Repeat Citation Styles

### `ibid` (เรื่องเดียวกัน)
ใช้เมื่ออ้างอิงเล่มเดียวกันติดกัน

```php
$formatter->formatRepeat('ibid', 'th', null, '50');
// → "เรื่องเดียวกัน, หน้า 50."
```

### `op_cit` (เรื่องเดิม)
ใช้เมื่ออ้างอิงเล่มเดิมหลังเว้นไประยะ

```php
$formatter->formatRepeat('op_cit', 'th', 'กมลชัย รัตนสกาววงศ์', '60');
// → "กมลชัย รัตนสกาววงศ์, เรื่องเดิม, หน้า 60."
```

### `same_doc`
รูปแบบ alternative

---

## Author Format Rules

### TH
- 1 คน: `ชื่อ สกุล`
- 2 คน: `A และ B`
- 3+ คน: `A, B และ C`

### EN
- Footnote: `John Smith` (first-last)
- Bibliography: `Smith, John` (last-first สำหรับคนแรก)
- 2 คน: `A and B`
- 3+ คน: `A, B, and C`

## Page Marker

| Lang | Single | Multiple (มี - หรือ ,) |
|---|---|---|
| TH | `หน้า X` | `หน้า X-Y` |
| EN | `p. X` | `pp. X-Y` |

## Italic Markers

ใช้ `<i>...</i>` ใน formatted output → จะแปลงเป็น italic ตอน render Word/PDF

---

## Sorting Rule (Bibliography)

1. **TH ขึ้นก่อน EN** (เสมอ)
2. ภายในภาษาเดียวกัน → alphabetical (ตาม `formatted_bibliography`)

## Database Schema (citations)

```sql
CREATE TABLE citations (
  id BIGINT UNSIGNED AUTO_INCREMENT,
  article_id BIGINT,
  citation_type ENUM('book','article',...),
  language ENUM('th','en'),
  data JSON,                          -- structured fields
  formatted_footnote TEXT,
  formatted_bibliography TEXT,
  ai_normalized BOOLEAN,
  ai_mode ENUM('manual','url_lookup','reformat'),
  source_url VARCHAR(500),
  source_isbn VARCHAR(32),
  notes TEXT,
  ...
);
```
