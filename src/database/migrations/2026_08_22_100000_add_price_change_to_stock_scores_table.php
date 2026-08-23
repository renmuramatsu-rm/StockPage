<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_scores', function (Blueprint $table) {
            $table->date('price_date')->nullable()->after('current_price');
            $table->decimal('price_change', 10, 2)->nullable()->after('price_date');
            $table->decimal('price_change_percent', 6, 2)->nullable()->after('price_change');
        });
    }

    public function down(): void
    {
        Schema::table('stock_scores', function (Blueprint $table) {
            $table->dropColumn(['price_date', 'price_change', 'price_change_percent']);
        });
    }
};
