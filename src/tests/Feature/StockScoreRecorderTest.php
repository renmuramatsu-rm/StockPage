<?php

namespace Tests\Feature;

use App\Models\FinancialStatement;
use App\Models\Stock;
use App\Services\FinancialAnalysis\StockScoreRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StockScoreRecorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_record_persists_a_score_row(): void
    {
        DB::table('markets')->insert(['market' => 'プライム']);
        DB::table('stocks')->insert(['code' => 7203, 'stockName' => 'トヨタ自動車', 'market_id' => 1]);

        FinancialStatement::create([
            'code' => 7203,
            'fiscal_year' => 2025,
            'period_type' => 'FY',
            'net_sales' => 48000000000000,
            'eps' => 300,
            'bps' => 3000,
            'roe' => 16.0,
            'equity_ratio' => 40.0,
            'source' => 'jquants',
        ]);

        Http::fake([
            '*/equities/bars/daily*' => Http::response(['data' => [
                ['Date' => '2026-08-20', 'C' => '2700'],
                ['Date' => '2026-08-19', 'C' => '2650'],
            ]]),
        ]);

        $stock = Stock::find('7203');
        $recorder = app(StockScoreRecorder::class);
        $score = $recorder->record($stock);

        $this->assertDatabaseHas('stock_scores', ['code' => 7203]);
        $this->assertNotNull($score->overall_score);
        $this->assertEquals(2700, $score->current_price);
        $this->assertEquals(9.0, $score->per);
        $this->assertEquals(50, $score->price_change);
        $this->assertEquals('2026-08-20', $score->price_date->format('Y-m-d'));
    }

    public function test_record_is_idempotent_per_stock(): void
    {
        DB::table('markets')->insert(['market' => 'プライム']);
        DB::table('stocks')->insert(['code' => 7203, 'stockName' => 'トヨタ自動車', 'market_id' => 1]);

        Http::fake(['*/equities/bars/daily*' => Http::response(['data' => []])]);

        $stock = Stock::find('7203');
        $recorder = app(StockScoreRecorder::class);
        $recorder->record($stock);
        $recorder->record($stock);

        $this->assertDatabaseCount('stock_scores', 1);
    }
}
