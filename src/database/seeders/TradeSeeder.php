<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('trades')->insert([
            'code' => 7201,
            'buy_sell' => '買',
            'shares' => 100,
            'price' => 500.0,
            'trading_date' => '20230304',
        ]);
    }
}
