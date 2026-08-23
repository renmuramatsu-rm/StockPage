<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TSE has issued alphanumeric 4-character stock codes since 2024 (e.g.
 * "167A" for Ryosan-Hishoyo Holdings, "285A" for Kioxia Holdings). The
 * `stocks.code` column (and everything that references it) was originally
 * an integer, which silently truncated these codes (e.g. "167A" -> 167).
 * This widens code columns to strings so they round-trip correctly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('financial_statements', function (Blueprint $table) {
            $table->dropForeign(['code']);
        });
        Schema::table('stock_theme', function (Blueprint $table) {
            $table->dropForeign(['stock_code']);
        });
        Schema::table('sbi_holdings', function (Blueprint $table) {
            $table->dropForeign(['code']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->string('code', 10)->change();
        });
        Schema::table('financial_statements', function (Blueprint $table) {
            $table->string('code', 10)->change();
        });
        Schema::table('stock_theme', function (Blueprint $table) {
            $table->string('stock_code', 10)->change();
        });
        Schema::table('sbi_holdings', function (Blueprint $table) {
            $table->string('code', 10)->change();
        });

        Schema::table('financial_statements', function (Blueprint $table) {
            $table->foreign('code')->references('code')->on('stocks')->cascadeOnDelete();
        });
        Schema::table('stock_theme', function (Blueprint $table) {
            $table->foreign('stock_code')->references('code')->on('stocks')->cascadeOnDelete();
        });
        Schema::table('sbi_holdings', function (Blueprint $table) {
            $table->foreign('code')->references('code')->on('stocks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('financial_statements', function (Blueprint $table) {
            $table->dropForeign(['code']);
        });
        Schema::table('stock_theme', function (Blueprint $table) {
            $table->dropForeign(['stock_code']);
        });
        Schema::table('sbi_holdings', function (Blueprint $table) {
            $table->dropForeign(['code']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->integer('code')->change();
        });
        Schema::table('financial_statements', function (Blueprint $table) {
            $table->integer('code')->change();
        });
        Schema::table('stock_theme', function (Blueprint $table) {
            $table->integer('stock_code')->change();
        });
        Schema::table('sbi_holdings', function (Blueprint $table) {
            $table->integer('code')->change();
        });

        Schema::table('financial_statements', function (Blueprint $table) {
            $table->foreign('code')->references('code')->on('stocks')->cascadeOnDelete();
        });
        Schema::table('stock_theme', function (Blueprint $table) {
            $table->foreign('stock_code')->references('code')->on('stocks')->cascadeOnDelete();
        });
        Schema::table('sbi_holdings', function (Blueprint $table) {
            $table->foreign('code')->references('code')->on('stocks')->cascadeOnDelete();
        });
    }
};
