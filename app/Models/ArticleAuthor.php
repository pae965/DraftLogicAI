<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleAuthor extends Model
{
    use HasFactory;

    public const ROLE_PRIMARY  = 'primary_author';
    public const ROLE_CO       = 'co_author';
    public const ROLE_ADVISOR  = 'advisor';

    protected $fillable = [
        'article_id', 'user_id',
        'title_th', 'title_en',
        'display_name_th', 'display_name_en',
        'affiliation_th', 'affiliation_en',
        'address_th', 'address_en',
        'email',
        'affiliation_url', 'profile_url', 'orcid_id',
        'role', 'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDisplayName(string $lang = 'th'): string
    {
        return $lang === 'th'
            ? trim(($this->title_th ?? '') . ' ' . $this->display_name_th)
            : trim(($this->title_en ?? '') . ' ' . $this->display_name_en);
    }

    public function isAdvisor(): bool
    {
        return $this->role === self::ROLE_ADVISOR;
    }
}
