<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();

            // Provenance
            $table->string('source_name')->nullable();
            $table->string('source_url', 1000)->unique(); // dedupe key
            $table->string('image_url', 1000)->nullable();

            // Raw scraped content (never published as-is)
            $table->string('original_title', 500);
            $table->longText('original_content')->nullable();
            $table->string('original_excerpt', 1000)->nullable();

            // AI-edited content
            $table->string('edited_title', 500)->nullable();
            $table->longText('edited_content')->nullable();

            // Workflow state: scraped -> edited -> published
            // 'scraped' can never be shown publicly.
            $table->enum('status', ['scraped', 'editing', 'edited', 'published', 'failed'])
                  ->default('scraped')
                  ->index();

            $table->text('edit_error')->nullable();     // last AI error, if any
            $table->timestamp('scraped_at')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('published_at')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
