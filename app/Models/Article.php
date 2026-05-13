<?php

namespace App\Models;

use App\Services\SectionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT          = 'draft';
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_SCHEDULED      = 'scheduled';
    public const STATUS_PUBLISHED      = 'published';
    public const STATUS_ARCHIVED       = 'archived';

    protected $fillable = [
        'title_th', 'title_en', 'subtitle_th', 'subtitle_en', 'slug',
        'primary_language',
        'template_id', 'template_snapshot',
        'independent_study_title_th', 'independent_study_title_en',
        'degree_program_th', 'degree_program_en',
        'faculty_th', 'faculty_en',
        'institution_th', 'institution_en',
        'status', 'published_at', 'primary_author_id',
        'view_count', 'read_time',
        'category_id', 'cover_image_id',
        'seo_meta', 'ai_metadata',
    ];

    protected $casts = [
        'published_at'     => 'datetime',
        'template_snapshot'=> 'array',
        'seo_meta'         => 'array',
        'ai_metadata'      => 'array',
    ];

    // ============ Relationships ============

    public function primaryAuthor()
    {
        return $this->belongsTo(User::class, 'primary_author_id');
    }

    public function template()
    {
        return $this->belongsTo(SectionTemplate::class, 'template_id');
    }

    public function sections()
    {
        return $this->hasMany(ArticleSection::class)->orderBy('order');
    }

    public function authors()
    {
        return $this->hasMany(ArticleAuthor::class)
            ->orderByRaw("FIELD(role, 'primary_author', 'co_author', 'advisor')")
            ->orderBy('order');
    }

    public function abstracts()
    {
        return $this->hasMany(ArticleAbstract::class);
    }

    public function keywords()
    {
        return $this->hasMany(ArticleKeyword::class)->orderBy('order');
    }

    public function citations()
    {
        return $this->hasMany(Citation::class);
    }

    public function citationUses()
    {
        return $this->hasMany(CitationUse::class)->orderBy('footnote_number');
    }

    // ============ Hooks ============

    protected static function booted()
    {
        static::creating(function (Article $article) {
            // auto-slug
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title_en ?: $article->title_th);
            }
            // auto-set primary_author
            if (empty($article->primary_author_id) && auth()->check()) {
                $article->primary_author_id = auth()->id();
            }
        });

        static::created(function (Article $article) {
            // instantiate sections from template
            if ($article->template_id) {
                app(SectionService::class)
                    ->instantiateForArticle($article, $article->template_id);
            }
        });

        static::updating(function (Article $article) {
            // ถ้าเปลี่ยน template → regenerate sections
            if ($article->isDirty('template_id') && $article->template_id) {
                app(SectionService::class)
                    ->instantiateForArticle($article, $article->template_id, true);
            }
        });
    }

    // ============ Helpers ============

    public function getTitle(string $lang = 'th'): string
    {
        return $lang === 'th' ? $this->title_th : $this->title_en;
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }
}
