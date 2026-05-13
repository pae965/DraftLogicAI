<?php

return [
    // ==== Article ====
    'article' => [
        'title_th'    => 'ชื่อบทความ (ไทย)',
        'title_en'    => 'ชื่อบทความ (อังกฤษ)',
        'subtitle_th' => 'หัวข้อรอง (ไทย)',
        'subtitle_en' => 'หัวข้อรอง (อังกฤษ)',
        'status'      => 'สถานะ',
        'created'     => 'สร้างบทความแล้ว',
        'updated'     => 'อัปเดตบทความแล้ว',
        'deleted'     => 'ลบบทความแล้ว',
    ],

    // ==== Status ====
    'status' => [
        'draft'          => 'ร่าง',
        'pending_review' => 'รอตรวจสอบ',
        'scheduled'      => 'กำหนดเวลาเผยแพร่',
        'published'      => 'เผยแพร่แล้ว',
        'archived'       => 'จัดเก็บ',
    ],

    // ==== Section ====
    'section' => [
        'add'      => 'เพิ่มหัวข้อ',
        'edit'     => 'แก้ไขหัวข้อ',
        'delete'   => 'ลบหัวข้อ',
        'reorder'  => 'จัดลำดับหัวข้อ',
        'visible'  => 'แสดง',
        'hidden'   => 'ซ่อน',
        'numbered' => 'ใส่เลขกำกับ',
    ],

    // ==== Citation ====
    'citation' => [
        'add'        => 'เพิ่มอ้างอิง',
        'mode_a'     => 'กรอกข้อมูลด้วยตนเอง',
        'mode_b'     => 'ค้นจาก URL/ISBN',
        'mode_c'     => 'แปลงข้อความที่มี',
        'footnote'   => 'เชิงอรรถ',
        'bibliography' => 'บรรณานุกรม',
    ],

    // ==== Citation Type ====
    'citation_type' => [
        'book'            => 'หนังสือ',
        'article'         => 'บทความวารสาร',
        'article_in_book' => 'บทความในหนังสือ',
        'newspaper'       => 'หนังสือพิมพ์',
        'thesis'          => 'วิทยานิพนธ์',
        'website'         => 'เว็บไซต์',
        'unpublished'     => 'เอกสารไม่ตีพิมพ์',
        'other'           => 'อื่นๆ',
    ],

    // ==== Export ====
    'export' => [
        'word'    => 'ส่งออก Word',
        'pdf'     => 'ส่งออก PDF',
        'preview' => 'ดูตัวอย่าง',
    ],

    // ==== AI ====
    'ai' => [
        'translate_abstract' => 'แปลบทคัดย่อ',
        'approve'            => 'อนุมัติ',
        'reject'             => 'ปฏิเสธ',
        'settings'           => 'ตั้งค่า AI',
        'api_key'            => 'API Key',
        'provider'           => 'ผู้ให้บริการ',
    ],

    // ==== Common ====
    'common' => [
        'save'    => 'บันทึก',
        'cancel'  => 'ยกเลิก',
        'delete'  => 'ลบ',
        'edit'    => 'แก้ไข',
        'create'  => 'สร้าง',
        'preview' => 'ดูตัวอย่าง',
        'submit'  => 'ส่ง',
    ],

    // ==== RUS specific ====
    'rus' => [
        'institution' => 'มหาวิทยาลัยเทคโนโลยีราชมงคลสุวรรณภูมิ',
        'faculty'     => 'คณะนิติศาสตร์',
        'system_name' => 'ระบบจัดการบทความวิจัย คณะนิติศาสตร์',
    ],
];
