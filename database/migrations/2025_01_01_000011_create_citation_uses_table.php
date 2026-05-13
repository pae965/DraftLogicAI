<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitationUsesTable extends Migration
{
    public function up(): void
    {
        Schema::create('citation_uses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('article_sections')->cascadeOnDelete();
            $table->foreignId('citation_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('footnote_number')
                ->comment('เลขเชิงอรรถใน article (1, 2, 3, ...)');
            $table->json('position_in_section')->nullable()
                ->comment('Tiptap node position');
            $table->string('pages_cited', 64)->nullable();
            $table->boolean('is_repeat')->default(false);
            $table->enum('repeat_style', ['ibid', 'op_cit', 'same_doc', 'none'])
                ->default('none');
            $table->timestamps();

            $table->unique(['article_id', 'footnote_number']);
            $table->index('citation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citation_uses');
    }
}
