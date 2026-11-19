<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lobbies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 4)->unique();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->integer('max_players')->default(8);
            $table->integer('round_duration')->default(80);
            $table->string('game_mode', 20)->default('classic');
            $table->json('word_packs');
            $table->boolean('is_public')->default(true);
            $table->enum('status', ['waiting', 'playing', 'finished'])->default('waiting');
            $table->timestamps();

            $table->index(['status', 'is_public']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lobbies');
    }
};
