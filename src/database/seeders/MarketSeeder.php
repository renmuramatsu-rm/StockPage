<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['プライム', 'スタンダード', 'グロース'] as $market) {
            DB::table('markets')->updateOrInsert(['market' => $market]);
        }
    }
}
