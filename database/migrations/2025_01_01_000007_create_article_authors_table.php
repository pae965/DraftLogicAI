<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticleAuthorsTable extends Migration
{
    public function up(): void
    {
        Schema::create('article_authors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete()
                ->comment('nullable: external author');

            $table->string('title_th', 64)->nullable()->comment('นาย/นาง/ดร./ศ.ดร.');
            $table->string('title_en', 64)->nullable()->comment('Mr./Mrs./Dr./Prof.');
            $table->string('display_name_th', 255);
            $table->string('display_name_en', 255);

            $table->text('affiliation_th')->nullable();
            $table->text('affiliation_en')->nullable();
            $table->text('address_th')->nullable();
            $table->text('address_en')->nullable();
            $table->string('email', 255)->nullable();

            // ===== Links (ใหม่) =====
            $table->string('affiliation_url', 500)->nullable();
            $table->string('profile_url', 500)->nullable();
            $table->string('orcid_id', 32)->nullable();

            $table->enum('role', ['primary_author', 'co_author', 'advisor'])
                ->default('co_author');
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();

            $table->index(['article_id', 'role', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_authors');
    }
}
