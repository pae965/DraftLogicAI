<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitationsTable extends Migration
{
    public function up(): void
    {
        Schema::create('citations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->enum('citation_type', [
                'book', 'article', 'article_in_book', 'newspaper',
                'thesis', 'website', 'unpublished', 'other',
            ])->index();
            $table->enum('language', ['th', 'en']);
            $table->json('data')->comment('structured citation fields');
            $table->text('formatted_footnote')->nullable();
            $table->text('formatted_bibliography')->nullable();
            $table->boolean('ai_normalized')->default(false);
            $table->enum('ai_mode', ['manual', 'url_lookup', 'reformat'])->nullable();
            $table->string('source_url', 500)->nullable();
            $table->string('source_isbn', 32)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('article_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citations');
    }
}
