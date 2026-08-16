<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // Russian display name
            $table->string('slug')->unique();       // used in URLs
            $table->string('color', 9)->default('#c8102e');
            $table->text('description')->nullable(); // helps the AI decide
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('category_id')
                  ->nullable()
                  ->after('source_name')
                  ->constrained('categories')
                  ->nullOnDelete();
        });

        // Default categories. The description is fed to Gemini so it can
        // classify accurately — edit these in the admin at any time.
        $now = now();
        DB::table('categories')->insert([
            ['name' => 'Трансферы', 'slug' => 'transfers', 'color' => '#c8102e', 'position' => 1,
             'description' => 'Трансферные слухи, подписания, продления контрактов, интерес к игрокам, суммы сделок.',
             'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Матчи', 'slug' => 'matches', 'color' => '#6a0f1e', 'position' => 2,
             'description' => 'Отчёты о матчах, превью, результаты, ход игры, разбор конкретных встреч.',
             'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Игроки', 'slug' => 'players', 'color' => '#f5b22d', 'position' => 3,
             'description' => 'Новости об отдельных игроках: форма, статистика, интервью, карьера, молодёжь академии.',
             'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Травмы', 'slug' => 'injuries', 'color' => '#8a1f2b', 'position' => 4,
             'description' => 'Травмы, сроки восстановления, медицинские заключения, возвращение в строй.',
             'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Клуб', 'slug' => 'club', 'color' => '#4c0a16', 'position' => 5,
             'description' => 'Дела клуба: тренерский штаб, руководство, стадион, финансы, форма, болельщики.',
             'created_at' => $now, 'updated_at' => $now],

            ['name' => 'Аналитика', 'slug' => 'analysis', 'color' => '#2f6f6a', 'position' => 6,
             'description' => 'Тактический разбор, статистический анализ, мнения и колонки.',
             'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('categories');
    }
};
