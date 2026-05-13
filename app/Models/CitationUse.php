<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CitationUse extends Model
{
    use HasFactory;

    public const STYLE_IBID     = 'ibid';
    public const STYLE_OP_CIT   = 'op_cit';
    public const STYLE_SAME_DOC = 'same_doc';
    public const STYLE_NONE     = 'none';

    protected $fillable = [
        'article_id', 'section_id', 'citation_id',
        'footnote_number', 'position_in_section', 'pages_cited',
        'is_repeat', 'repeat_style',
    ];

    protected $casts = [
        'footnote_number'    => 'integer',
        'position_in_section' => 'array',
        'is_repeat'           => 'boolean',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function section()
    {
        return $this->belongsTo(ArticleSection::class, 'section_id');
    }

    public function citation()
    {
        return $this->belongsTo(Citation::class);
    }
}
