<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleSectionsTable extends Migration
{
    public function up(): void
    {
        Schema::create('article_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->string('template_item_key', 64)
                ->comment('match กับ section_template_items.key');
            $table->unsignedInteger('order');
            $table->string('label_th', 255);
            $table->string('label_en', 255);
            $table->boolean('visible')->default(true);
            $table->boolean('numbered')->default(true);
            $table->enum('type', ['abstract', 'abstract_en', 'keywords', 'richtext', 'bibliography'])
                ->default('richtext');
            $table->json('content')->nullable()
                ->comment('Tiptap JSON สำหรับ section ภาษาเดียว');
            $table->json('content_th')->nullable();
            $table->json('content_en')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();

            $table->index(['article_id', 'order']);
            $table->unique(['article_id', 'template_item_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_sections');
    }
}
