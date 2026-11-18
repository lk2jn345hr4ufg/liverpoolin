<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Матчи всего тура (АПЛ, Лига чемпионов) для блока «Матчи сегодня».
        // Отдельно от fixtures, где хранятся только матчи «Ливерпуля».
        Schema::create('league_matches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('external_id')->unique();
            $table->string('competition_code', 10)->index();
            $table->string('competition')->nullable();
            $table->unsignedTinyInteger('matchday')->nullable();
            $table->string('home_team');
            $table->string('away_team');
            $table->string('home_crest', 500)->nullable();
            $table->string('away_crest', 500)->nullable();
            $table->string('venue')->nullable();
            $table->string('venue_tz', 64)->nullable();
            $table->dateTime('kickoff_at')->nullable()->index();
            $table->unsignedTinyInteger('home_score')->nullable();
            $table->unsignedTinyInteger('away_score')->nullable();
            $table->enum('status', ['scheduled', 'live', 'finished', 'postponed'])->default('scheduled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('league_matches');
    }
};
