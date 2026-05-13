<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'key', 'name_th', 'name_en',
        'description_th', 'description_en',
        'is_active', 'is_system_default',
        'created_by',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'is_system_default' => 'boolean',
    ];

    // ============ Relationships ============

    public function items()
    {
        return $this->hasMany(SectionTemplateItem::class, 'template_id')->orderBy('order');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'template_id');
    }

    public function userAssignments()
    {
        return $this->hasMany(UserTemplateAssignment::class, 'template_id');
    }

    // ============ Hooks: ensure only one system default ============

    protected static function booted()
    {
        static::saving(function (SectionTemplate $template) {
            if ($template->is_system_default && $template->isDirty('is_system_default')) {
                static::where('id', '!=', $template->id ?? 0)
                    ->where('is_system_default', true)
                    ->update(['is_system_default' => false]);
            }
        });
    }

    // ============ Scopes ============

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSystemDefault($query)
    {
        return $query->where('is_system_default', true)->active();
    }
}
