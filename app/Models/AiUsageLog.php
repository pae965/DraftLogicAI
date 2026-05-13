<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'article_id', 'provider', 'model', 'purpose',
        'tokens_input', 'tokens_output', 'cost_estimate',
        'success', 'error_message', 'requested_at',
    ];

    protected $casts = [
        'tokens_input'  => 'integer',
        'tokens_output' => 'integer',
        'cost_estimate' => 'decimal:6',
        'success'       => 'boolean',
        'requested_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
