<?php

namespace Database\Seeders;

use App\Models\SectionTemplate;
use Illuminate\Database\Seeder;

class SectionTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedStrict();
        $this->seedFlexible();
        $this->seedMinimal();
    }

    protected function seedStrict(): void
    {
        if (SectionTemplate::where('key', 'rus_strict_v1')->exists()) {
            return;
        }

        $template = SectionTemplate::create([
            'key'               => 'rus_strict_v1',
            'name_th'           => 'RUS มาตรฐาน 7 หัวข้อ (เคร่งครัด)',
            'name_en'           => 'RUS Standard 7 Sections (Strict)',
            'description_th'    => 'รูปแบบบทความวิจัยตามมาตรฐาน คณะนิติศาสตร์ มหาวิทยาลัยเทคโนโลยีราชมงคลสุวรรณภูมิ — ใช้สำหรับงานค้นคว้าอิสระที่ส่งคณะ',
            'description_en'    => 'Strict format per RUS Faculty of Law publication standard — for Independent Study submission',
            'is_active'         => true,
            'is_system_default' => true,
        ]);

        $items = [
            ['order' => 1,  'key' => 'abstract',         'label_th' => 'บทคัดย่อ',                'label_en' => 'Abstract',              'required' => true,  'numbered' => false, 'default_visible' => true,  'type' => 'abstract'],
            ['order' => 2,  'key' => 'abstract_en',      'label_th' => 'Abstract',                'label_en' => 'Abstract',              'required' => true,  'numbered' => false, 'default_visible' => true,  'type' => 'abstract_en'],
            ['order' => 3,  'key' => 'keywords',         'label_th' => 'คำสำคัญ',                 'label_en' => 'Keywords',              'required' => true,  'numbered' => false, 'default_visible' => true,  'type' => 'keywords'],
            ['order' => 4,  'key' => 'introduction',     'label_th' => 'บทนำ',                    'label_en' => 'Introduction',          'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 5,  'key' => 'objectives',       'label_th' => 'วัตถุประสงค์ของการศึกษา', 'label_en' => 'Objective of Study',    'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 6,  'key' => 'hypothesis',       'label_th' => 'สมมุติฐานการศึกษา',       'label_en' => 'Hypothesis',            'required' => false, 'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 7,  'key' => 'methodology',      'label_th' => 'วิธีการศึกษา',            'label_en' => 'Research Methodology',  'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 8,  'key' => 'results',          'label_th' => 'ผลการศึกษาและการอภิปรายผล','label_en' => 'Result and Discussion', 'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 9,  'key' => 'conclusion',       'label_th' => 'บทสรุปผลการศึกษา',         'label_en' => 'Conclusion',            'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 10, 'key' => 'recommendations',  'label_th' => 'ข้อเสนอแนะ',              'label_en' => 'Recommendation',        'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 11, 'key' => 'bibliography',     'label_th' => 'บรรณานุกรม',              'label_en' => 'Bibliography',          'required' => true,  'numbered' => false, 'default_visible' => true,  'type' => 'bibliography'],
        ];

        foreach ($items as $item) {
            $template->items()->create($item);
        }
    }

    protected function seedFlexible(): void
    {
        if (SectionTemplate::where('key', 'rus_flexible_v1')->exists()) {
            return;
        }

        $template = SectionTemplate::create([
            'key'               => 'rus_flexible_v1',
            'name_th'           => 'RUS แบบขยาย (ทบทวนวรรณกรรม + กิตติกรรมประกาศ)',
            'name_en'           => 'RUS Extended (Lit Review + Acknowledgements)',
            'description_th'    => 'รูปแบบ RUS + เพิ่มหัวข้อทบทวนวรรณกรรม และกิตติกรรมประกาศ — เหมาะสำหรับบทความที่ส่งวารสารอื่น',
            'description_en'    => 'RUS format extended with Literature Review and Acknowledgements',
            'is_active'         => true,
            'is_system_default' => false,
        ]);

        $items = [
            ['order' => 1,  'key' => 'abstract',          'label_th' => 'บทคัดย่อ',                'label_en' => 'Abstract',              'required' => true,  'numbered' => false, 'default_visible' => true,  'type' => 'abstract'],
            ['order' => 2,  'key' => 'abstract_en',       'label_th' => 'Abstract',                'label_en' => 'Abstract',              'required' => true,  'numbered' => false, 'default_visible' => true,  'type' => 'abstract_en'],
            ['order' => 3,  'key' => 'keywords',          'label_th' => 'คำสำคัญ',                 'label_en' => 'Keywords',              'required' => true,  'numbered' => false, 'default_visible' => true,  'type' => 'keywords'],
            ['order' => 4,  'key' => 'introduction',      'label_th' => 'บทนำ',                    'label_en' => 'Introduction',          'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 5,  'key' => 'literature_review', 'label_th' => 'ทบทวนวรรณกรรม',           'label_en' => 'Literature Review',     'required' => false, 'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 6,  'key' => 'objectives',        'label_th' => 'วัตถุประสงค์ของการศึกษา', 'label_en' => 'Objective of Study',    'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 7,  'key' => 'hypothesis',        'label_th' => 'สมมุติฐานการศึกษา',       'label_en' => 'Hypothesis',            'required' => false, 'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 8,  'key' => 'methodology',       'label_th' => 'วิธีการศึกษา',            'label_en' => 'Research Methodology',  'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 9,  'key' => 'results',           'label_th' => 'ผลการศึกษาและการอภิปรายผล','label_en' => 'Result and Discussion', 'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 10, 'key' => 'conclusion',        'label_th' => 'บทสรุปผลการศึกษา',         'label_en' => 'Conclusion',            'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 11, 'key' => 'recommendations',   'label_th' => 'ข้อเสนอแนะ',              'label_en' => 'Recommendation',        'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 12, 'key' => 'acknowledgements',  'label_th' => 'กิตติกรรมประกาศ',          'label_en' => 'Acknowledgements',      'required' => false, 'numbered' => false, 'default_visible' => false, 'type' => 'richtext'],
            ['order' => 13, 'key' => 'bibliography',      'label_th' => 'บรรณานุกรม',              'label_en' => 'Bibliography',          'required' => true,  'numbered' => false, 'default_visible' => true,  'type' => 'bibliography'],
        ];

        foreach ($items as $item) {
            $template->items()->create($item);
        }
    }

    /**
     * Template ใหม่ — เหมาะสำหรับบทความสั้น (ตามตัวอย่างกัสยา สุพล)
     */
    protected function seedMinimal(): void
    {
        if (SectionTemplate::where('key', 'rus_minimal_v1')->exists()) {
            return;
        }

        $template = SectionTemplate::create([
            'key'               => 'rus_minimal_v1',
            'name_th'           => 'RUS แบบย่อ 5 หัวข้อ (สำหรับบทความสั้น)',
            'name_en'           => 'RUS Minimal 5 Sections (Short Article)',
            'description_th'    => 'รูปแบบบทความสั้น มี 5 หัวข้อหลัก (ไม่มีสมมุติฐาน, รวมขอบเขต) เหมาะสำหรับบทความวิจัยเชิงกฎหมายที่กระชับ',
            'description_en'    => 'Compact format with 5 main sections (no hypothesis, includes scope) — for concise legal research articles',
            'is_active'         => true,
            'is_system_default' => false,
        ]);

        $items = [
            ['order' => 1,  'key' => 'abstract',         'label_th' => 'บทคัดย่อ',                'label_en' => 'Abstract',              'required' => true,  'numbered' => false, 'default_visible' => true,  'type' => 'abstract'],
            ['order' => 2,  'key' => 'abstract_en',      'label_th' => 'Abstract',                'label_en' => 'Abstract',              'required' => true,  'numbered' => false, 'default_visible' => true,  'type' => 'abstract_en'],
            ['order' => 3,  'key' => 'keywords',         'label_th' => 'คำสำคัญ',                 'label_en' => 'Keywords',              'required' => true,  'numbered' => false, 'default_visible' => true,  'type' => 'keywords'],
            ['order' => 4,  'key' => 'introduction',     'label_th' => 'บทนำ',                    'label_en' => 'Introduction',          'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 5,  'key' => 'objectives',       'label_th' => 'วัตถุประสงค์ของการศึกษา', 'label_en' => 'Objective of Study',    'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 6,  'key' => 'scope',            'label_th' => 'ขอบเขตของการศึกษา',        'label_en' => 'Scope of Study',        'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 7,  'key' => 'results',          'label_th' => 'ผลการศึกษา',              'label_en' => 'Results',               'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 8,  'key' => 'conclusion',       'label_th' => 'บทสรุป',                  'label_en' => 'Conclusion',            'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 9,  'key' => 'recommendations',  'label_th' => 'ข้อเสนอแนะ',              'label_en' => 'Recommendation',        'required' => true,  'numbered' => true,  'default_visible' => true,  'type' => 'richtext'],
            ['order' => 10, 'key' => 'bibliography',     'label_th' => 'บรรณานุกรม',              'label_en' => 'Bibliography',          'required' => true,  'numbered' => false, 'default_visible' => true,  'type' => 'bibliography'],
        ];

        foreach ($items as $item) {
            $template->items()->create($item);
        }
    }
}
