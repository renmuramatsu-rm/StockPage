<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persists the last-computed "should I buy this?" score per stock so the
 * stock list page can filter/display badges without recomputing them (and
 * without a live J-Quants price call) for every row. Recomputed whenever a
 * stock's financial statements are (re)synced — see StockScoreRecorder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_scores', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->foreign('code')->references('code')->on('stocks')->cascadeOnDelete();
            $table->integer('overall_score')->nullable();
            $table->string('badge')->nullable();
            $table->string('badge_color')->nullable();
            $table->integer('growth_score')->nullable();
            $table->string('growth_label')->nullable();
            $table->integer('valuation_score')->nullable();
            $table->string('valuation_label')->nullable();
            $table->integer('quality_score')->nullable();
            $table->string('quality_label')->nullable();
            $table->decimal('current_price', 12, 2)->nullable();
            $table->decimal('per', 10, 2)->nullable();
            $table->decimal('pbr', 10, 2)->nullable();
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_scores');
    }
};
