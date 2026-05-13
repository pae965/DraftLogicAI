<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI BYOK (Bring Your Own Key) settings per user
 */
class CreateAiSettingsTable extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('provider', ['claude', 'openai', 'gemini']);
            $table->text('api_key')
                ->comment('encrypted via Laravel Crypt::encryptString()');
            $table->string('model_default', 64)->nullable();
            $table->json('options')->nullable()
                ->comment('temperature, max_tokens, etc.');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
}
