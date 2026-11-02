<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            // Ручное переопределение часового пояса стадиона
            // (например, финал на нейтральном поле). Пусто = авто.
            $table->string('venue_tz', 64)->nullable()->after('venue');
        });
    }

    public function down(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropColumn('venue_tz');
        });
    }
};
