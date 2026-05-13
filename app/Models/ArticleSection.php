<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id', 'template_item_key', 'order',
        'label_th', 'label_en',
        'visible', 'numbered', 'type',
        'content', 'content_th', 'content_en', 'extra',
    ];

    protected $casts = [
        'order'      => 'integer',
        'visible'    => 'boolean',
        'numbered'   => 'boolean',
        'content'    => 'array',
        'content_th' => 'array',
        'content_en' => 'array',
        'extra'      => 'array',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function citationUses()
    {
        return $this->hasMany(CitationUse::class, 'section_id');
    }

    public function getLabel(string $lang = 'th'): string
    {
        return $lang === 'th' ? $this->label_th : $this->label_en;
    }

    public function scopeVisible($query)
    {
        return $query->where('visible', true);
    }
}
