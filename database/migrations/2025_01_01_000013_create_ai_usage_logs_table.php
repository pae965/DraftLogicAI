<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAiUsageLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->enum('provider', ['claude', 'openai', 'gemini']);
            $table->string('model', 64);
            $table->string('purpose', 64)
                ->comment('citation/abstract/section/translation/other');
            $table->unsignedInteger('tokens_input')->default(0);
            $table->unsignedInteger('tokens_output')->default(0);
            $table->decimal('cost_estimate', 10, 6)->default(0);
            $table->boolean('success')->default(true);
            $table->text('error_message')->nullable();
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'requested_at']);
            $table->index('provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
}
