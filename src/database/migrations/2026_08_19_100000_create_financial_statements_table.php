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
        Schema::create('financial_statements', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->foreign('code')->references('code')->on('stocks')->cascadeOnDelete();
            $table->integer('fiscal_year');
            $table->string('period_type');
            $table->date('fiscal_period_end')->nullable();
            $table->date('disclosed_date')->nullable();
            $table->bigInteger('net_sales')->nullable();
            $table->bigInteger('operating_profit')->nullable();
            $table->bigInteger('ordinary_profit')->nullable();
            $table->bigInteger('profit')->nullable();
            $table->decimal('eps', 12, 2)->nullable();
            $table->decimal('roe', 8, 4)->nullable();
            $table->decimal('equity_ratio', 8, 4)->nullable();
            $table->bigInteger('total_assets')->nullable();
            $table->bigInteger('net_assets')->nullable();
            $table->decimal('dividend_per_share', 10, 2)->nullable();
            $table->string('source')->default('jquants');
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['code', 'fiscal_year', 'period_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_statements');
    }
};
