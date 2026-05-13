<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $title_th
 * @property string|null $title_en
 * @property string|null $name_th
 * @property string|null $name_en
 * @property string $role
 * @property string $preferred_language
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, SoftDeletes;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN       = 'admin';
    public const ROLE_EDITOR      = 'editor';
    public const ROLE_AUTHOR      = 'author';

    protected $fillable = [
        'name', 'email', 'password',
        'title_th', 'title_en', 'name_th', 'name_en',
        'default_address_th', 'default_address_en',
        'default_affiliation_th', 'default_affiliation_en',
        'orcid_id', 'profile_url',
        'preferred_language', 'role',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['profile_photo_url'];

    // ============ Relationships ============

    public function authoredArticles()
    {
        return $this->hasMany(Article::class, 'primary_author_id');
    }

    public function articleAuthorships()
    {
        return $this->hasMany(ArticleAuthor::class);
    }

    public function templateAssignments()
    {
        return $this->hasMany(UserTemplateAssignment::class);
    }

    public function aiSettings()
    {
        return $this->hasMany(AiSetting::class);
    }

    public function aiUsageLogs()
    {
        return $this->hasMany(AiUsageLog::class);
    }

    // ============ Helpers ============

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN]);
    }

    public function isEditor(): bool
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN, self::ROLE_EDITOR]);
    }

    public function getDisplayName(string $lang = 'th'): string
    {
        if ($lang === 'th') {
            return trim(($this->title_th ?? '') . ' ' . ($this->name_th ?? $this->name));
        }
        return trim(($this->title_en ?? '') . ' ' . ($this->name_en ?? $this->name));
    }

    /**
     * Filament Admin access (Filament 2.x interface)
     */
    public function canAccessFilament(): bool
    {
        return $this->isAdmin();
    }
}
