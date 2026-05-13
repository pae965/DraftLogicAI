<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectionTemplateItemsTable extends Migration
{
    public function up(): void
    {
        Schema::create('section_template_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('section_templates')->cascadeOnDelete();
            $table->unsignedInteger('order')->index();
            $table->string('key', 64);
            $table->string('label_th', 255);
            $table->string('label_en', 255);
            $table->boolean('required')->default(true);
            $table->boolean('numbered')->default(true);
            $table->boolean('default_visible')->default(true);
            $table->enum('type', ['abstract', 'abstract_en', 'keywords', 'richtext', 'bibliography'])
                ->default('richtext');
            $table->text('hint_th')->nullable();
            $table->text('hint_en')->nullable();
            $table->timestamps();

            $table->unique(['template_id', 'key']);
            $table->unique(['template_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_template_items');
    }
}
