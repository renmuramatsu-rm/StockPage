<?php

namespace Tests\Unit;

use App\Services\FinancialAnalysis\StockScorer;
use PHPUnit\Framework\TestCase;

class StockScorerTest extends TestCase
{
    public function test_high_growth_cheap_and_healthy_stock_scores_as_buy_candidate(): void
    {
        $result = (new StockScorer())->score([
            'sales_growth' => 18.0,
            'profit_growth' => 20.0,
            'per' => 8.0,
            'pbr' => 0.7,
            'roe' => 16.0,
            'equity_ratio' => 55.0,
        ]);

        $this->assertSame(100, $result['growth']['score']);
        $this->assertSame(100, $result['valuation']['score']);
        $this->assertSame(100, $result['quality']['score']);
        $this->assertSame('買い候補', $result['overall']['badge']);
    }

    public function test_expensive_shrinking_stock_scores_low(): void
    {
        $result = (new StockScorer())->score([
            'sales_growth' => -5.0,
            'profit_growth' => -10.0,
            'per' => 45.0,
            'pbr' => 4.0,
            'roe' => -2.0,
            'equity_ratio' => 10.0,
        ]);

        $this->assertSame('非推奨', $result['overall']['badge']);
    }

    public function test_missing_valuation_data_falls_back_gracefully(): void
    {
        $result = (new StockScorer())->score([
            'sales_growth' => 10.0,
            'profit_growth' => 10.0,
            'per' => null,
            'pbr' => null,
            'roe' => 12.0,
            'equity_ratio' => 45.0,
        ]);

        $this->assertNull($result['valuation']['score']);
        $this->assertSame('データ不足', $result['valuation']['label']);
        // Overall should still compute from the two available axes.
        $this->assertNotNull($result['overall']['score']);
    }

    public function test_all_missing_data_yields_no_overall_score(): void
    {
        $result = (new StockScorer())->score([]);

        $this->assertNull($result['overall']['score']);
        $this->assertSame('データ不足', $result['overall']['badge']);
    }

    public function test_roa_pulls_down_a_highly_leveraged_stocks_quality_score(): void
    {
        // Same ROE/equity ratio as the "buy candidate" case above, but a
        // weak ROA — the leverage inflating ROE should no longer produce
        // a perfect quality score once ROA is factored in.
        $result = (new StockScorer())->score([
            'roe' => 16.0,
            'equity_ratio' => 55.0,
            'roa' => 1.0,
        ]);

        $this->assertLessThan(100, $result['quality']['score']);
    }

    public function test_quality_score_still_works_when_roa_is_missing(): void
    {
        $result = (new StockScorer())->score([
            'roe' => 16.0,
            'equity_ratio' => 55.0,
        ]);

        $this->assertSame(100, $result['quality']['score']);
    }
}
