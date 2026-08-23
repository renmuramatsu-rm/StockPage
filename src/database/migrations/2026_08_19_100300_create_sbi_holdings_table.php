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
        Schema::create('sbi_holdings', function (Blueprint $table) {
            $table->id();
            $table->integer('code')->unique();
            $table->foreign('code')->references('code')->on('stocks')->cascadeOnDelete();
            $table->integer('shares');
            $table->decimal('average_acquisition_price', 10, 2);
            $table->date('acquisition_date')->nullable();
            $table->string('account_type')->nullable();
            $table->text('memo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sbi_holdings');
    }
};
