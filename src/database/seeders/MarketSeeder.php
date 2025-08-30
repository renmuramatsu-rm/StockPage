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
        DB::table('markets')->insert([
            'market' => 'プライム',
        ]);
        DB::table('markets')->insert([
            'market' => 'スタンダード',
        ]);
        DB::table('markets')->insert([
            'market' => 'グロース',
        ]);
    }
}
