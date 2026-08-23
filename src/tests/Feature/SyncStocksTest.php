<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncStocksTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_imports_only_the_requested_market_when_pinned(): void
    {
        Http::fake([
            '*/equities/master*' => Http::response(['data' => [
                ['Code' => '72030', 'CoName' => 'トヨタ自動車', 'MktNm' => 'プライム'],
                ['Code' => '13010', 'CoName' => '極洋', 'MktNm' => 'プライム'],
                ['Code' => '99990', 'CoName' => 'グロース企業', 'MktNm' => 'グロース'],
            ]]),
        ]);

        Artisan::call('stocks:sync', ['--date' => '20260528', '--market' => 'プライム']);

        $this->assertDatabaseHas('stocks', ['code' => 7203, 'stockName' => 'トヨタ自動車']);
        $this->assertDatabaseHas('stocks', ['code' => 1301, 'stockName' => '極洋']);
        $this->assertDatabaseMissing('stocks', ['code' => 9999]);

        $marketId = DB::table('stocks')->where('code', 7203)->value('market_id');
        $this->assertSame('プライム', DB::table('markets')->where('id', $marketId)->value('market'));
    }

    public function test_sync_imports_both_prime_and_growth_by_default(): void
    {
        Http::fake([
            '*/equities/master*' => Http::response(['data' => [
                ['Code' => '72030', 'CoName' => 'トヨタ自動車', 'MktNm' => 'プライム'],
                ['Code' => '99990', 'CoName' => 'グロース企業', 'MktNm' => 'グロース'],
                ['Code' => '88880', 'CoName' => 'スタンダード企業', 'MktNm' => 'スタンダード'],
            ]]),
        ]);

        Artisan::call('stocks:sync', ['--date' => '20260528']);

        $this->assertDatabaseHas('stocks', ['code' => 7203, 'stockName' => 'トヨタ自動車']);
        $this->assertDatabaseHas('stocks', ['code' => 9999, 'stockName' => 'グロース企業']);
        $this->assertDatabaseMissing('stocks', ['code' => 8888]);
    }

    public function test_sync_preserves_alphanumeric_tse_codes(): void
    {
        Http::fake([
            '*/equities/master*' => Http::response(['data' => [
                ['Code' => '167A0', 'CoName' => 'リョーサン菱洋ホールディングス', 'MktNm' => 'プライム'],
            ]]),
        ]);

        Artisan::call('stocks:sync', ['--date' => '20260528']);

        $this->assertDatabaseHas('stocks', ['code' => '167A', 'stockName' => 'リョーサン菱洋ホールディングス']);
    }

    public function test_sync_is_safely_rerunnable(): void
    {
        Http::fake([
            '*/equities/master*' => Http::response(['data' => [
                ['Code' => '72030', 'CoName' => 'トヨタ自動車', 'MktNm' => 'プライム'],
            ]]),
        ]);

        Artisan::call('stocks:sync', ['--date' => '20260528']);
        Artisan::call('stocks:sync', ['--date' => '20260528']);

        $this->assertDatabaseCount('stocks', 1);
    }

    public function test_sync_auto_tags_stocks_with_a_sector_theme(): void
    {
        Http::fake([
            '*/equities/master*' => Http::response(['data' => [
                ['Code' => '72030', 'CoName' => 'トヨタ自動車', 'MktNm' => 'プライム', 'S17Nm' => '自動車・輸送機'],
                ['Code' => '67580', 'CoName' => 'ソニーグループ', 'MktNm' => 'プライム', 'S17Nm' => '電機・精密'],
            ]]),
        ]);

        Artisan::call('stocks:sync', ['--date' => '20260528']);

        $this->assertDatabaseHas('themes', ['name' => '自動車・輸送機', 'source' => 'jquants_17']);

        $themeId = DB::table('themes')->where('name', '自動車・輸送機')->value('id');
        $this->assertDatabaseHas('stock_theme', ['stock_code' => 7203, 'theme_id' => $themeId]);
    }

    public function test_sync_does_not_duplicate_theme_tags_on_rerun(): void
    {
        Http::fake([
            '*/equities/master*' => Http::response(['data' => [
                ['Code' => '72030', 'CoName' => 'トヨタ自動車', 'MktNm' => 'プライム', 'S17Nm' => '自動車・輸送機'],
            ]]),
        ]);

        Artisan::call('stocks:sync', ['--date' => '20260528']);
        Artisan::call('stocks:sync', ['--date' => '20260528']);

        $this->assertDatabaseCount('themes', 1);
        $this->assertDatabaseCount('stock_theme', 1);
    }

    public function test_sync_retries_earlier_dates_when_the_default_date_has_no_data(): void
    {
        // No --date given, so the command walks backward on its own. The
        // first two attempts (e.g. a weekend/holiday) come back empty; the
        // third succeeds — this should not be treated as a hard failure.
        Http::fake([
            '*/equities/master*' => Http::sequence()
                ->push(['data' => []])
                ->push(['data' => []])
                ->push(['data' => [
                    ['Code' => '72030', 'CoName' => 'トヨタ自動車', 'MktNm' => 'プライム'],
                ]]),
        ]);

        $exitCode = Artisan::call('stocks:sync');

        $this->assertSame(0, $exitCode);
        $this->assertDatabaseHas('stocks', ['code' => 7203]);
    }

    public function test_sync_does_not_retry_when_an_explicit_date_is_given(): void
    {
        Http::fake([
            '*/equities/master*' => Http::response(['data' => []]),
        ]);

        $exitCode = Artisan::call('stocks:sync', ['--date' => '20260524']);

        $this->assertSame(1, $exitCode);
        Http::assertSentCount(1);
    }
}
