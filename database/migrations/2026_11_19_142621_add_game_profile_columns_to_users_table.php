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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
            if (! Schema::hasColumn('users', 'level')) {
                $table->integer('level')->default(1)->after('password');
            }
            if (! Schema::hasColumn('users', 'mmr')) {
                $table->integer('mmr')->default(1000)->after('level');
            }
            if (! Schema::hasColumn('users', 'sabotage_accuracy')) {
                $table->integer('sabotage_accuracy')->default(0)->after('mmr');
            }
            if (! Schema::hasColumn('users', 'player_title')) {
                $table->string('player_title')->default('Pemula Spidol')->after('sabotage_accuracy');
            }
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('player_title');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['level', 'mmr', 'sabotage_accuracy', 'player_title', 'avatar'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $columnsToDrop[] = $column;
                }
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
