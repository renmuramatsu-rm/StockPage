<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A personal watchlist, distinct from Themes (categorization) and
 * SbiHolding (actual owned shares) — just "I want to keep an eye on
 * this stock", one row per user per stock.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 10);
            $table->foreign('code')->references('code')->on('stocks')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
