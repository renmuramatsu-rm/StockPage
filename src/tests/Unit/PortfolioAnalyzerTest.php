<?php

namespace Tests\Unit;

use App\Services\FinancialAnalysis\PortfolioAnalyzer;
use PHPUnit\Framework\TestCase;

class PortfolioAnalyzerTest extends TestCase
{
    public function test_flags_a_single_stock_concentration(): void
    {
        $result = (new PortfolioAnalyzer())->analyze([
            ['code' => '7203', 'name' => 'トヨタ自動車', 'value' => 800_000, 'themes' => ['自動車'], 'badge' => '様子見'],
            ['code' => '6758', 'name' => 'ソニーグループ', 'value' => 200_000, 'themes' => ['電機'], 'badge' => '様子見'],
        ]);

        $this->assertSame(80.0, $result['by_stock'][0]['share']);
        $this->assertStringContainsString('トヨタ自動車', $result['suggestions'][0]);
    }

    public function test_flags_a_single_theme_concentration(): void
    {
        $result = (new PortfolioAnalyzer())->analyze([
            ['code' => '7203', 'name' => 'トヨタ自動車', 'value' => 500_000, 'themes' => ['自動車'], 'badge' => '様子見'],
            ['code' => '7267', 'name' => 'ホンダ', 'value' => 500_000, 'themes' => ['自動車'], 'badge' => '様子見'],
        ]);

        $this->assertSame(100.0, $result['by_theme'][0]['share']);
        $found = collect($result['suggestions'])->contains(fn ($s) => str_contains($s, '自動車'));
        $this->assertTrue($found);
    }

    public function test_flags_weak_and_unscored_holdings(): void
    {
        $result = (new PortfolioAnalyzer())->analyze([
            ['code' => '1111', 'name' => 'A社', 'value' => 100_000, 'themes' => ['食品'], 'badge' => '非推奨'],
            ['code' => '2222', 'name' => 'B社', 'value' => 100_000, 'themes' => ['化学'], 'badge' => null],
        ]);

        $this->assertSame(1, $result['badge_counts']['非推奨']);
        $this->assertSame(1, $result['badge_counts']['未評価']);

        $joined = implode(' ', $result['suggestions']);
        $this->assertStringContainsString('非推奨', $joined);
        $this->assertStringContainsString('評価されていません', $joined);
    }

    public function test_balanced_portfolio_has_no_warnings(): void
    {
        $result = (new PortfolioAnalyzer())->analyze([
            ['code' => '1', 'name' => 'A', 'value' => 250_000, 'themes' => ['食品'], 'badge' => '様子見'],
            ['code' => '2', 'name' => 'B', 'value' => 250_000, 'themes' => ['化学'], 'badge' => '様子見'],
            ['code' => '3', 'name' => 'C', 'value' => 250_000, 'themes' => ['電機'], 'badge' => '様子見'],
            ['code' => '4', 'name' => 'D', 'value' => 250_000, 'themes' => ['金融'], 'badge' => '様子見'],
        ]);

        $this->assertSame(['現時点で特に大きな偏りは見られません。'], $result['suggestions']);
    }

    public function test_empty_portfolio_does_not_error(): void
    {
        $result = (new PortfolioAnalyzer())->analyze([]);

        $this->assertEquals(0, $result['total_value']);
        $this->assertSame([], $result['by_stock']);
    }
}
