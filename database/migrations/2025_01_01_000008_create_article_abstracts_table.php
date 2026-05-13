<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleAbstractsTable extends Migration
{
    public function up(): void
    {
        Schema::create('article_abstracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->enum('language', ['th', 'en']);
            $table->enum('mode', ['manual', 'ai_translated'])->default('manual');
            $table->text('content_text')->nullable()
                ->comment('plain text — ย่อหน้าเดียวตาม spec');

            // ===== AI translation metadata =====
            $table->enum('source_language', ['th', 'en'])->nullable();
            $table->string('ai_provider', 32)->nullable();
            $table->string('ai_model', 64)->nullable();
            $table->timestamp('translated_at')->nullable();

            // ===== Approval =====
            $table->boolean('approved_by_author')->default(false);
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique(['article_id', 'language']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_abstracts');
    }
}
