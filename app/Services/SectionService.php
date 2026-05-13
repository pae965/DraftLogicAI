<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleSection;
use App\Models\SectionTemplate;
use App\Models\User;
use App\Models\UserTemplateAssignment;
use Illuminate\Support\Collection;

class SectionService
{
    /**
     * สร้าง article_sections สำหรับบทความตาม template
     *
     * @param Article $article
     * @param int     $templateId
     * @param bool    $replaceExisting ลบ sections เดิมก่อน (true ตอน user เปลี่ยน template)
     */
    public function instantiateForArticle(
        Article $article,
        int $templateId,
        bool $replaceExisting = false
    ): void {
        $template = SectionTemplate::with('items')->findOrFail($templateId);

        if ($template->items->isEmpty()) {
            throw new \RuntimeException("Template {$templateId} has no items");
        }

        if ($replaceExisting) {
            $article->sections()->delete();
        } elseif ($article->sections()->exists()) {
            return; // skip ถ้ามีอยู่แล้ว
        }

        // เก็บ snapshot ของ template
        $article->update([
            'template_snapshot' => [
                'template_id'     => $template->id,
                'template_key'    => $template->key,
                'instantiated_at' => now()->toIso8601String(),
                'items'           => $template->items->map(fn($i) => $i->toArray())->all(),
            ],
        ]);

        // create sections
        foreach ($template->items as $item) {
            ArticleSection::create([
                'article_id'        => $article->id,
                'template_item_key' => $item->key,
                'order'             => $item->order,
                'label_th'          => $item->label_th,
                'label_en'          => $item->label_en,
                'visible'           => $item->default_visible,
                'numbered'          => $item->numbered,
                'type'              => $item->type,
            ]);
        }
    }

    /**
     * คำนวณเลขหัวข้อใหม่
     * - เฉพาะ section ที่ numbered=true และ visible=true และ type=richtext
     * - เลขเริ่มที่ 1 เรียงตาม order
     *
     * @return array<int, int> map: section_id => sectionNumber
     */
    public function computeNumbering(Collection $sections): array
    {
        $numbering = [];
        $counter = 1;
        $sorted = $sections->sortBy('order');

        foreach ($sorted as $section) {
            if ($section->numbered
                && $section->visible
                && $section->type === 'richtext'
            ) {
                $numbering[$section->id] = $counter++;
            }
        }

        return $numbering;
    }

    /**
     * ดึง sections ที่ visible เรียงตาม order
     */
    public function getOrderedSections(Article $article, bool $includeHidden = false): Collection
    {
        $query = $article->sections();
        if (!$includeHidden) {
            $query->where('visible', true);
        }
        return $query->orderBy('order')->get();
    }

    /**
     * หา default template สำหรับ user
     *  1. ดู user_template_assignments ที่ is_default=true
     *  2. ถ้าไม่มี → ใช้ system default
     */
    public function getDefaultTemplateForUser(User $user): ?SectionTemplate
    {
        $assignment = UserTemplateAssignment::with('template')
            ->where('user_id', $user->id)
            ->where('is_default', true)
            ->first();

        if ($assignment && $assignment->template && $assignment->template->is_active) {
            return $assignment->template;
        }

        return SectionTemplate::systemDefault()->first();
    }

    /**
     * Templates ที่ user เลือกใช้ได้
     *
     * @return array{templates: Collection, defaultTemplateId: int|null}
     */
    public function getAvailableTemplatesForUser(User $user): array
    {
        $templates = SectionTemplate::active()->orderBy('name_th')->get();
        $default = $this->getDefaultTemplateForUser($user);

        return [
            'templates'         => $templates,
            'defaultTemplateId' => $default?->id,
        ];
    }

    /**
     * เพิ่ม section ใหม่แทรกระหว่าง sections เดิม
     * จะ shift order ของ section ที่อยู่หลัง
     */
    public function insertSection(
        Article $article,
        int $afterOrder,
        array $data
    ): ArticleSection {
        // shift order ของ sections ที่อยู่หลัง
        $article->sections()
            ->where('order', '>', $afterOrder)
            ->orderBy('order', 'desc')
            ->each(function ($s) {
                $s->update(['order' => $s->order + 1]);
            });

        return ArticleSection::create(array_merge($data, [
            'article_id' => $article->id,
            'order'      => $afterOrder + 1,
        ]));
    }

    /**
     * Reorder sections ตาม array ของ section IDs (drag-and-drop)
     */
    public function reorderSections(Article $article, array $orderedIds): void
    {
        foreach ($orderedIds as $newOrder => $sectionId) {
            ArticleSection::where('id', $sectionId)
                ->where('article_id', $article->id)
                ->update(['order' => $newOrder + 1]);
        }
    }
}
