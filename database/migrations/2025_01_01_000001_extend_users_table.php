<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: extend default Laravel users table
 * เพิ่ม fields สำหรับ RUS Research CMS
 *
 * NOTE: รันหลังจาก laravel default users migration เสร็จแล้ว
 */
class ExtendUsersTable extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ==== Bilingual name fields ====
            $table->string('title_th', 64)->nullable()->after('name')
                ->comment('คำนำหน้าภาษาไทย เช่น นาย, นาง, ดร.');
            $table->string('title_en', 64)->nullable()->after('title_th');
            $table->string('name_th', 255)->nullable()->after('title_en');
            $table->string('name_en', 255)->nullable()->after('name_th');

            // ==== Default user info (used when authoring articles) ====
            $table->text('default_address_th')->nullable();
            $table->text('default_address_en')->nullable();
            $table->text('default_affiliation_th')->nullable();
            $table->text('default_affiliation_en')->nullable();

            // ==== ORCID / Profile links ====
            $table->string('orcid_id', 32)->nullable();
            $table->string('profile_url', 500)->nullable();

            // ==== Preferences ====
            $table->string('preferred_language', 2)->default('th')
                ->comment('UI language: th or en');

            // ==== Role ====
            $table->enum('role', ['super_admin', 'admin', 'editor', 'author'])
                ->default('author')
                ->after('email')
                ->index();

            // ==== Soft delete ====
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'title_th', 'title_en', 'name_th', 'name_en',
                'default_address_th', 'default_address_en',
                'default_affiliation_th', 'default_affiliation_en',
                'orcid_id', 'profile_url',
                'preferred_language', 'role',
            ]);
            $table->dropSoftDeletes();
        });
    }
}
