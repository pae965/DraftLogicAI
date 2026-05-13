<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * เทมเพลตหัวข้อบทความ — admin/super_admin จัดการ
 */
class CreateSectionTemplatesTable extends Migration
{
    public function up(): void
    {
        Schema::create('section_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique()
                ->comment('a-z, 0-9, _ เช่น rus_strict_v1');
            $table->string('name_th', 255);
            $table->string('name_en', 255);
            $table->text('description_th')->nullable();
            $table->text('description_en')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_system_default')->default(false)
                ->comment('ถ้า true จะใช้เมื่อ user ไม่มี assignment');
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_templates');
    }
}
