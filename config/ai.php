<?php

/**
 * AI Provider Configuration
 *
 * BYOK: ในระบบจริงให้ user กำหนด API key เอง (เก็บใน ai_settings table)
 * ENV defaults ใช้เป็น fallback สำหรับ dev/testing
 */
return [
    'claude' => [
        'api_key'       => env('ANTHROPIC_API_KEY'),
        'default_model' => env('ANTHROPIC_DEFAULT_MODEL', 'claude-3-5-sonnet-20241022'),
    ],

    'openai' => [
        'api_key'       => env('OPENAI_API_KEY'),
        'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-4o-mini'),
    ],

    'gemini' => [
        'api_key'       => env('GEMINI_API_KEY'),
        'default_model' => env('GEMINI_DEFAULT_MODEL', 'gemini-1.5-flash'),
    ],

    'rate_limit' => [
        'per_user_per_hour' => 100,
        'per_user_per_day'  => 500,
    ],

    'cost_limits' => [
        'daily_per_user_usd'   => 5.0,
        'monthly_per_user_usd' => 50.0,
    ],
];
