<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();

            // Ключ дедупликации: игрок + дата + клубы.
            // У API-Football нет собственного id трансфера.
            $table->string('external_key', 64)->unique();

            $table->unsignedInteger('player_id')->nullable();
            $table->string('player_name');

            $table->date('transfer_date')->nullable()->index();
            $table->unsignedSmallInteger('season')->index();   // год старта сезона

            // 'in' — приход в «Ливерпуль», 'out' — уход
            $table->enum('direction', ['in', 'out'])->index();

            $table->string('club_from')->nullable();
            $table->string('club_from_logo', 500)->nullable();
            $table->string('club_to')->nullable();
            $table->string('club_to_logo', 500)->nullable();

            // Сырое значение из API: 'Free', 'Loan', 'N/A' или сумма '€ 40M'
            $table->string('type_raw', 60)->nullable();
            // Нормализованный тип: free | loan | fee | unknown
            $table->string('kind', 20)->default('unknown')->index();
            $table->string('fee_text', 60)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
