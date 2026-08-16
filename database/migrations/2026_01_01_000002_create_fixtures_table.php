<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->string('competition')->nullable();
            $table->string('home_team');
            $table->string('away_team');
            $table->string('venue')->nullable();
            $table->dateTime('kickoff_at')->nullable()->index();

            // result (nullable until played)
            $table->unsignedTinyInteger('home_score')->nullable();
            $table->unsignedTinyInteger('away_score')->nullable();
            $table->enum('status', ['scheduled', 'live', 'finished', 'postponed'])
                  ->default('scheduled');

            // dedupe key built from teams + kickoff
            $table->string('fingerprint')->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
