<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Themes become per-user from here on, except system-generated sector
 * tags (source = 'jquants_17'), which keep a null user_id and stay
 * visible to every logged-in user. The old global-unique name index is
 * replaced with one scoped per owner, since two users (or a user and
 * the system) may legitimately want the same theme name.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('themes', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'name']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('themes', function (Blueprint $table) {
            $table->unique('name');
        });
    }
};
