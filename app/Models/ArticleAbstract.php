<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleAbstract extends Model
{
    use HasFactory;

    public const MODE_MANUAL        = 'manual';
    public const MODE_AI_TRANSLATED = 'ai_translated';

    protected $fillable = [
        'article_id', 'language', 'mode', 'content_text',
        'source_language', 'ai_provider', 'ai_model', 'translated_at',
        'approved_by_author', 'approved_at',
    ];

    protected $casts = [
        'translated_at'      => 'datetime',
        'approved_at'        => 'datetime',
        'approved_by_author' => 'boolean',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    protected static function booted()
    {
        static::saving(function (ArticleAbstract $abs) {
            if ($abs->approved_by_author && $abs->isDirty('approved_by_author')) {
                $abs->approved_at = now();
            }
        });
    }
}
