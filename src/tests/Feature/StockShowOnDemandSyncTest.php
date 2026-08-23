<?php

namespace Tests\Feature;

use App\Models\FinancialStatement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StockShowOnDemandSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('markets')->insert(['market' => 'プライム']);
        DB::table('stocks')->insert(['code' => 7203, 'stockName' => 'トヨタ自動車', 'market_id' => 1]);
    }

    public function test_first_visit_triggers_a_sync_when_no_data_exists(): void
    {
        Http::fake([
            '*/fins/summary*' => Http::response(['data' => [
                [
                    'CurPerType' => 'FY',
                    'CurFYEn' => '2025-03-31',
                    'CurPerEn' => '2025-03-31',
                    'DiscDate' => '2025-05-08',
                    'Sales' => '48000000000000',
                ],
            ]]),
            '*/equities/bars/daily*' => Http::response(['data' => []]),
        ]);

        $response = $this->get('/stocks/7203');

        $response->assertStatus(200);
        $this->assertDatabaseHas('financial_statements', ['code' => 7203, 'fiscal_year' => 2025]);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/fins/summary'));
    }

    public function test_visiting_again_soon_after_does_not_resync_statements(): void
    {
        FinancialStatement::create([
            'code' => 7203,
            'fiscal_year' => 2025,
            'period_type' => 'FY',
            'net_sales' => 48000000000000,
            'source' => 'jquants',
        ]);

        Http::fake([
            '*/equities/bars/daily*' => Http::response(['data' => []]),
        ]);

        $response = $this->get('/stocks/7203');

        $response->assertStatus(200);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/fins/summary'));
    }

    public function test_page_still_renders_when_price_lookup_fails_to_connect(): void
    {
        FinancialStatement::create([
            'code' => 7203,
            'fiscal_year' => 2025,
            'period_type' => 'FY',
            'net_sales' => 48000000000000,
            'source' => 'jquants',
        ]);

        Http::fake([
            '*/equities/bars/daily*' => Http::failedConnection('cURL error 28: SSL connection timeout'),
        ]);

        $response = $this->get('/stocks/7203');

        $response->assertStatus(200);
        $response->assertSee('取得失敗');
    }
}
