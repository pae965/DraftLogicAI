<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleKeyword extends Model
{
    use HasFactory;

    protected $fillable = ['article_id', 'language', 'keyword', 'order'];

    protected $casts = [
        'order' => 'integer',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
