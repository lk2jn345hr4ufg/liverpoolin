<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            // 191 — безопасная длина для unique-индекса в utf8mb4.
            $table->string('slug', 191)->unique();
            $table->string('title', 300);
            $table->string('excerpt', 500)->nullable();
            $table->longText('body')->nullable();        // HTML из редактора
            $table->string('image_url', 1000)->nullable(); // обложка
            $table->foreignId('category_id')->nullable()
                  ->constrained('categories')->nullOnDelete();
            $table->string('author')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
