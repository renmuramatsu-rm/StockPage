<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncFinancialsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('markets')->insert(['market' => 'プライム']);
        DB::table('stocks')->insert(['code' => 7203, 'stockName' => 'トヨタ自動車', 'market_id' => 1]);
    }

    public function test_sync_fetches_and_stores_financial_statements(): void
    {
        Http::fake([
            '*/fins/summary*' => Http::response(['data' => [
                [
                    'CurPerType' => 'FY',
                    'CurFYEn' => '2024-03-31',
                    'CurPerEn' => '2024-03-31',
                    'DiscDate' => '2024-05-08',
                    'Sales' => '45000000000000',
                    'OP' => '5000000000000',
                    'OdP' => '5200000000000',
                    'NP' => '4900000000000',
                    'EPS' => '350.5',
                    'TA' => '90000000000000',
                    'Eq' => '30000000000000',
                    'EqAR' => '33.3',
                    'ROE' => '16.3',
                ],
            ]]),
            '*/equities/bars/daily*' => Http::response(['data' => []]),
        ]);

        Artisan::call('financials:sync', ['code' => '7203', '--sleep' => 0]);

        $this->assertDatabaseHas('financial_statements', [
            'code' => 7203,
            'fiscal_year' => 2024,
            'period_type' => 'FY',
            'net_sales' => 45000000000000,
        ]);
    }

    public function test_sync_is_safely_rerunnable(): void
    {
        Http::fake([
            '*/fins/summary*' => Http::response(['data' => [
                [
                    'CurPerType' => 'FY',
                    'CurFYEn' => '2024-03-31',
                    'CurPerEn' => '2024-03-31',
                    'DiscDate' => '2024-05-08',
                    'Sales' => '1000',
                ],
            ]]),
            '*/equities/bars/daily*' => Http::response(['data' => []]),
        ]);

        Artisan::call('financials:sync', ['code' => '7203', '--sleep' => 0]);
        Artisan::call('financials:sync', ['code' => '7203', '--sleep' => 0]);

        $this->assertDatabaseCount('financial_statements', 1);
    }

    public function test_sync_skips_failed_codes_without_aborting(): void
    {
        DB::table('stocks')->insert(['code' => 6758, 'stockName' => 'ソニーグループ', 'market_id' => 1]);

        Http::fake([
            '*/fins/summary*' => Http::response(['message' => 'Forbidden'], 403),
        ]);

        $exitCode = Artisan::call('financials:sync', ['--sleep' => 0, '--no-score' => true]);

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseCount('financial_statements', 0);
    }

    public function test_bulk_run_skips_recently_synced_stocks_unless_forced(): void
    {
        Http::fake([
            '*/fins/summary*' => Http::response(['data' => [
                [
                    'CurPerType' => 'FY',
                    'CurFYEn' => '2024-03-31',
                    'CurPerEn' => '2024-03-31',
                    'DiscDate' => '2024-05-08',
                    'Sales' => '1000',
                ],
            ]]),
            '*/equities/bars/daily*' => Http::response(['data' => []]),
        ]);

        // First (bulk) run syncs the one stock.
        Artisan::call('financials:sync', ['--sleep' => 0, '--no-score' => true]);
        Http::assertSentCount(1);

        // A second bulk run without --force should skip it (still fresh).
        Artisan::call('financials:sync', ['--sleep' => 0, '--no-score' => true]);
        Http::assertSentCount(1);

        // --force should re-sync it regardless of freshness.
        Artisan::call('financials:sync', ['--sleep' => 0, '--no-score' => true, '--force' => true]);
        Http::assertSentCount(2);
    }

    public function test_limit_option_caps_how_many_stocks_are_processed(): void
    {
        DB::table('stocks')->insert(['code' => 6758, 'stockName' => 'ソニーグループ', 'market_id' => 1]);
        DB::table('stocks')->insert(['code' => 9432, 'stockName' => 'NTT', 'market_id' => 1]);

        Http::fake([
            '*/fins/summary*' => Http::response(['data' => []]),
        ]);

        Artisan::call('financials:sync', ['--sleep' => 0, '--no-score' => true, '--limit' => 2]);

        Http::assertSentCount(2);
    }
}
