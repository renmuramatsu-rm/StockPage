<?php

namespace Tests\Feature;

use App\Models\Stock;
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

    public function test_filtering_by_badge_shows_only_matching_stocks(): void
    {
        $response = $this->get('/stocks?badge='.urlencode('買い候補'));

        $response->assertStatus(200);
        $response->assertSee('トヨタ自動車');
        $response->assertDontSee('ソニーグループ');
        $response->assertDontSee('未評価テスト');
    }

    public function test_filtering_by_unevaluated_shows_stocks_without_a_score(): void
    {
        $response = $this->get('/stocks?badge='.urlencode('未評価'));

        $response->assertStatus(200);
        $response->assertSee('未評価テスト');
        $response->assertDontSee('トヨタ自動車');
        $response->assertDontSee('ソニーグループ');
    }

    public function test_no_filter_shows_all_stocks(): void
    {
        $response = $this->get('/stocks');

        $response->assertStatus(200);
        $response->assertSee('トヨタ自動車');
        $response->assertSee('ソニーグループ');
        $response->assertSee('未評価テスト');
    }

    public function test_search_by_name_narrows_results(): void
    {
        $response = $this->get('/stocks?q='.urlencode('トヨタ'));

        $response->assertStatus(200);
        $response->assertSee('トヨタ自動車');
        $response->assertDontSee('ソニーグループ');
    }

    public function test_search_by_code_narrows_results(): void
    {
        $response = $this->get('/stocks?q=6758');

        $response->assertStatus(200);
        $response->assertSee('ソニーグループ');
        $response->assertDontSee('トヨタ自動車');
    }
}
