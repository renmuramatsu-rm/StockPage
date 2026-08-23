<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = [
            ['name' => '自動車', 'color' => '#2563eb'],
            ['name' => '輸出関連', 'color' => '#16a34a'],
            ['name' => 'エレクトロニクス', 'color' => '#f59e0b'],
        ];

        foreach ($themes as $theme) {
            DB::table('themes')->updateOrInsert(
                ['name' => $theme['name']],
                $theme + [
                    'source' => 'manual',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $themeIds = DB::table('themes')->pluck('id', 'name');

        $assignments = [
            ['stock_code' => 7201, 'theme' => '自動車'],
            ['stock_code' => 7201, 'theme' => '輸出関連'],
            ['stock_code' => 7203, 'theme' => '自動車'],
            ['stock_code' => 7203, 'theme' => '輸出関連'],
            ['stock_code' => 6758, 'theme' => 'エレクトロニクス'],
            ['stock_code' => 6758, 'theme' => '輸出関連'],
        ];

        foreach ($assignments as $assignment) {
            DB::table('stock_theme')->updateOrInsert(
                [
                    'stock_code' => $assignment['stock_code'],
                    'theme_id' => $themeIds[$assignment['theme']],
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
