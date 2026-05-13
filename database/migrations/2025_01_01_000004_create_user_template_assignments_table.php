<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserTemplateAssignmentsTable extends Migration
{
    public function up(): void
    {
        Schema::create('user_template_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('section_templates')->cascadeOnDelete();
            $table->boolean('is_default')->default(true)
                ->comment('default template สำหรับ user');
            $table->foreignId('assigned_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->useCurrent();

            $table->unique(['user_id', 'template_id']);
            // หนึ่ง user มี default ได้แค่ 1 template ใช้ unique index แบบ partial ผ่าน raw
            // (MySQL 5.7 รองรับ unique index ปกติ แต่ partial index ต้อง custom — จัดการใน Model boot)
        });

        // เพิ่ม partial unique index ผ่าน raw SQL (สำหรับ MySQL 5.7)
        // ใช้ generated column เพื่อจำลอง partial index
        \DB::statement('
            ALTER TABLE user_template_assignments
            ADD COLUMN default_marker INT GENERATED ALWAYS AS (
                IF(is_default = 1, user_id, NULL)
            ) VIRTUAL
        ');
        \DB::statement('
            CREATE UNIQUE INDEX user_template_one_default_idx
            ON user_template_assignments (default_marker)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('user_template_assignments');
    }
}
