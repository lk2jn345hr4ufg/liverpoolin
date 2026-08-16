<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standings', function (Blueprint $table) {
            $table->id();

            $table->string('competition_code', 10)->default('PL');
            $table->unsignedSmallInteger('season');            // год старта сезона, напр. 2026
            $table->unsignedTinyInteger('matchday')->default(0); // тур на момент снимка

            $table->unsignedInteger('team_id');                // id команды в football-data
            $table->string('team_name');
            $table->string('team_short', 60)->nullable();
            $table->string('team_crest', 500)->nullable();

            $table->unsignedTinyInteger('position');
            $table->unsignedTinyInteger('played')->default(0);
            $table->unsignedTinyInteger('won')->default(0);
            $table->unsignedTinyInteger('draw')->default(0);
            $table->unsignedTinyInteger('lost')->default(0);
            $table->smallInteger('points')->default(0);
            $table->smallInteger('goals_for')->default(0);
            $table->smallInteger('goals_against')->default(0);
            $table->smallInteger('goal_difference')->default(0);
            $table->string('form', 20)->nullable();            // напр. W,W,D,L,W

            $table->timestamps();

            // Один снимок на команду в конкретном туре сезона.
            // Это даёт и текущую таблицу (максимальный тур), и историю по турам.
            $table->unique(['competition_code', 'season', 'matchday', 'team_id'], 'standings_snapshot_unique');
            $table->index(['competition_code', 'season', 'matchday']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standings');
    }
};
