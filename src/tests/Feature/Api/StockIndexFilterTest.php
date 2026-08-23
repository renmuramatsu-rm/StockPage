<?php

namespace Tests\Feature\Api;

use App\Models\StockScore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockIndexFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('markets')->insert(['market' => 'プライム']);
        DB::table('stocks')->insert(['code' => 7203, 'stockName' => 'トヨタ自動車', 'market_id' => 1]);
        DB::table('stocks')->insert(['code' => 6758, 'stockName' => 'ソニーグループ', 'market_id' => 1]);
        DB::table('stocks')->insert(['code' => 9999, 'stockName' => '未評価テスト', 'market_id' => 1]);

        StockScore::create(['code' => 7203, 'overall_score' => 90, 'badge' => '買い候補', 'badge_color' => 'green']);
        StockScore::create(['code' => 6758, 'overall_score' => 30, 'badge' => '割高・要注意', 'badge_color' => 'orange']);
    }

    private function names($response): array
    {
        return collect($response->json('stocks.data'))->pluck('stockName')->all();
    }

    public function test_filtering_by_badge_shows_only_matching_stocks(): void
    {
        $response = $this->getJson('/api/stocks?badge='.urlencode('買い候補'));

        $response->assertOk();
        $names = $this->names($response);
        $this->assertContains('トヨタ自動車', $names);
        $this->assertNotContains('ソニーグループ', $names);
        $this->assertNotContains('未評価テスト', $names);
    }

    public function test_filtering_by_unevaluated_shows_stocks_without_a_score(): void
    {
        $response = $this->getJson('/api/stocks?badge='.urlencode('未評価'));

        $response->assertOk();
        $names = $this->names($response);
        $this->assertContains('未評価テスト', $names);
        $this->assertNotContains('トヨタ自動車', $names);
        $this->assertNotContains('ソニーグループ', $names);
    }

    public function test_no_filter_shows_all_stocks(): void
    {
        $response = $this->getJson('/api/stocks');

        $response->assertOk();
        $names = $this->names($response);
        $this->assertContains('トヨタ自動車', $names);
        $this->assertContains('ソニーグループ', $names);
        $this->assertContains('未評価テスト', $names);
    }

    public function test_search_by_name_narrows_results(): void
    {
        $response = $this->getJson('/api/stocks?q='.urlencode('トヨタ'));

        $response->assertOk();
        $names = $this->names($response);
        $this->assertContains('トヨタ自動車', $names);
        $this->assertNotContains('ソニーグループ', $names);
    }

    public function test_search_by_code_narrows_results(): void
    {
        $response = $this->getJson('/api/stocks?q=6758');

        $response->assertOk();
        $names = $this->names($response);
        $this->assertContains('ソニーグループ', $names);
        $this->assertNotContains('トヨタ自動車', $names);
    }
}
