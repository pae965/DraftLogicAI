<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Citation extends Model
{
    use HasFactory;

    public const TYPE_BOOK            = 'book';
    public const TYPE_ARTICLE         = 'article';
    public const TYPE_ARTICLE_IN_BOOK = 'article_in_book';
    public const TYPE_NEWSPAPER       = 'newspaper';
    public const TYPE_THESIS          = 'thesis';
    public const TYPE_WEBSITE         = 'website';
    public const TYPE_UNPUBLISHED     = 'unpublished';
    public const TYPE_OTHER           = 'other';

    public const AI_MODE_MANUAL     = 'manual';
    public const AI_MODE_URL_LOOKUP = 'url_lookup';
    public const AI_MODE_REFORMAT   = 'reformat';

    protected $fillable = [
        'article_id', 'citation_type', 'language', 'data',
        'formatted_footnote', 'formatted_bibliography',
        'ai_normalized', 'ai_mode',
        'source_url', 'source_isbn', 'notes',
    ];

    protected $casts = [
        'data'          => 'array',
        'ai_normalized' => 'boolean',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function uses()
    {
        return $this->hasMany(CitationUse::class);
    }
}
