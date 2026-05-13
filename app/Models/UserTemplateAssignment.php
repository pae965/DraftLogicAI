<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTemplateAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'template_id', 'is_default', 'assigned_by', 'assigned_at',
    ];

    protected $casts = [
        'is_default'   => 'boolean',
        'assigned_at'  => 'datetime',
    ];

    public $timestamps = false;

    // ============ Relationships ============

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(SectionTemplate::class, 'template_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    // ============ Hooks ============

    protected static function booted()
    {
        // ensure only one default per user
        static::saving(function (UserTemplateAssignment $a) {
            if ($a->is_default && $a->isDirty('is_default')) {
                static::where('user_id', $a->user_id)
                    ->where('id', '!=', $a->id ?? 0)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }
}
