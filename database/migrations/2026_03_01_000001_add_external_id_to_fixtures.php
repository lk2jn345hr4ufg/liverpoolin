<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            // football-data.org match id — the reliable key for updating
            // a fixture when the date changes or the score comes in.
            $table->unsignedBigInteger('external_id')->nullable()->unique()->after('id');
            $table->string('source', 40)->default('manual')->after('external_id');
            $table->unsignedTinyInteger('matchday')->nullable()->after('competition');
        });
    }

    public function down(): void
    {
        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropColumn(['external_id', 'source', 'matchday']);
        });
    }
};
