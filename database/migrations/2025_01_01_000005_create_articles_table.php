<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * บทความวิจัย/ค้นคว้าอิสระ (RUS Faculty of Law)
 */
class CreateArticlesTable extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();

            // ===== Title =====
            $table->string('title_th', 500);
            $table->string('title_en', 500);
            $table->string('subtitle_th', 500)->nullable();
            $table->string('subtitle_en', 500)->nullable();
            $table->string('slug', 255)->unique()->index();
            $table->enum('primary_language', ['th', 'en'])->default('th');

            // ===== Template =====
            $table->foreignId('template_id')->nullable()
                ->constrained('section_templates')->nullOnDelete();
            $table->json('template_snapshot')->nullable()
                ->comment('snapshot ของ template ตอนสร้างบทความ');

            // ===== Independent Study Info (สำหรับ footnote หน้า 1) =====
            $table->text('independent_study_title_th')->nullable();
            $table->text('independent_study_title_en')->nullable();
            $table->string('degree_program_th', 255)->default('นิติศาสตรมหาบัณฑิต');
            $table->string('degree_program_en', 255)->default('Master of Laws');
            $table->string('faculty_th', 255)->default('คณะนิติศาสตร์');
            $table->string('faculty_en', 255)->default('Faculty of Law');
            $table->string('institution_th', 255)
                ->default('มหาวิทยาลัยเทคโนโลยีราชมงคลสุวรรณภูมิ');
            $table->string('institution_en', 255)
                ->default('Rajamangala University of Technology Suvarnabhumi');

            // ===== Status & Publishing =====
            $table->enum('status', [
                'draft', 'pending_review', 'scheduled', 'published', 'archived',
            ])->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('primary_author_id')->constrained('users');

            // ===== Stats =====
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('read_time')->default(0)->comment('minutes');

            // ===== Categorization (optional) =====
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('cover_image_id')->nullable();

            // ===== Metadata =====
            $table->json('seo_meta')->nullable();
            $table->json('ai_metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('template_id');
            $table->index('primary_author_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
}
