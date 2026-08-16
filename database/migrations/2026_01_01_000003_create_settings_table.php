<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });

        // Seed the default editing prompt.
        DB::table('settings')->insert([
            'key' => 'edit_prompt',
            'value' => "You are the editor of LiverpoolIn.com, a fan site for Liverpool FC supporters.\n"
                . "Rewrite the following football news into an original, engaging article in your own words.\n"
                . "Rules:\n"
                . "- Do NOT copy sentences verbatim from the source. Fully rewrite.\n"
                . "- Keep it factual and neutral; do not invent quotes, stats, or transfers.\n"
                . "- Write from a Liverpool-fan perspective but stay professional.\n"
                . "- Length: 150-300 words.\n"
                . "- Return STRICT JSON only: {\"title\": \"...\", \"content\": \"...\"} with no markdown.\n",
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
