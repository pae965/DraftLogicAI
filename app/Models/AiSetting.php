<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class AiSetting extends Model
{
    use HasFactory;

    public const PROVIDER_CLAUDE = 'claude';
    public const PROVIDER_OPENAI = 'openai';
    public const PROVIDER_GEMINI = 'gemini';

    protected $fillable = [
        'user_id', 'provider', 'api_key', 'model_default', 'options', 'is_active',
    ];

    protected $casts = [
        'options'   => 'array',
        'is_active' => 'boolean',
    ];

    protected $hidden = ['api_key'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Auto-encrypt api_key on set
     */
    public function setApiKeyAttribute($value): void
    {
        $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Auto-decrypt api_key on get
     */
    public function getApiKeyAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
