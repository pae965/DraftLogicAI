<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleKeywordsTable extends Migration
{
    public function up(): void
    {
        Schema::create('article_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->enum('language', ['th', 'en']);
            $table->string('keyword', 255);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['article_id', 'language', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_keywords');
    }
}
