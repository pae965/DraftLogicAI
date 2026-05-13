<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionTemplateItem extends Model
{
    use HasFactory;

    public const TYPE_ABSTRACT     = 'abstract';
    public const TYPE_ABSTRACT_EN  = 'abstract_en';
    public const TYPE_KEYWORDS     = 'keywords';
    public const TYPE_RICHTEXT     = 'richtext';
    public const TYPE_BIBLIOGRAPHY = 'bibliography';

    protected $fillable = [
        'template_id', 'order', 'key',
        'label_th', 'label_en',
        'required', 'numbered', 'default_visible',
        'type', 'hint_th', 'hint_en',
    ];

    protected $casts = [
        'order'           => 'integer',
        'required'        => 'boolean',
        'numbered'        => 'boolean',
        'default_visible' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(SectionTemplate::class, 'template_id');
    }
}
