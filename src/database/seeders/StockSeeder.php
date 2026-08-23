<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stocks = [
            ['code' => 7201, 'stockName' => '日産自動車', 'market_id' => 1],
            ['code' => 7203, 'stockName' => 'トヨタ自動車', 'market_id' => 1],
            ['code' => 6758, 'stockName' => 'ソニーグループ', 'market_id' => 1],
        ];

        foreach ($stocks as $stock) {
            DB::table('stocks')->updateOrInsert(['code' => $stock['code']], $stock);
        }
    }
}
