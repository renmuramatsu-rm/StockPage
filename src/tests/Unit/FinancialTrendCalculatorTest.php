<?php

namespace Tests\Unit;

use App\Models\FinancialStatement;
use App\Services\FinancialAnalysis\FinancialTrendCalculator;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class FinancialTrendCalculatorTest extends TestCase
{
    private function makeStatement(int $fiscalYear, ?int $netSales, ?int $operatingProfit = null, ?int $profit = null): FinancialStatement
    {
        return new FinancialStatement([
            'fiscal_year' => $fiscalYear,
            'period_type' => 'FY',
            'net_sales' => $netSales,
            'operating_profit' => $operatingProfit,
            'profit' => $profit,
        ]);
    }

    public function test_yoy_growth_and_margins_are_computed(): void
    {
        $statements = new Collection([
            $this->makeStatement(2022, 1000, 100, 60),
            $this->makeStatement(2023, 1100, 110, 66),
        ]);

        $rows = (new FinancialTrendCalculator($statements))->toTrendRows();

        $this->assertNull($rows[0]['yoy_net_sales']);
        $this->assertEquals(10.0, $rows[1]['yoy_net_sales']);
        $this->assertEquals(10.0, $rows[1]['operating_margin']);
    }

    public function test_yoy_growth_is_null_when_previous_value_missing(): void
    {
        $statements = new Collection([
            $this->makeStatement(2022, null),
            $this->makeStatement(2023, 1100),
        ]);

        $rows = (new FinancialTrendCalculator($statements))->toTrendRows();

        $this->assertNull($rows[1]['yoy_net_sales']);
    }

    public function test_cagr_over_three_years(): void
    {
        $statements = new Collection([
            $this->makeStatement(2021, 1000),
            $this->makeStatement(2022, 1100),
            $this->makeStatement(2023, 1210),
            $this->makeStatement(2024, 1331),
        ]);

        $cagr = (new FinancialTrendCalculator($statements))->cagr('net_sales', 3);

        $this->assertEqualsWithDelta(10.0, $cagr, 0.1);
    }

    public function test_cagr_is_null_with_fewer_than_two_data_points(): void
    {
        $statements = new Collection([
            $this->makeStatement(2024, 1000),
        ]);

        $this->assertNull((new FinancialTrendCalculator($statements))->cagr('net_sales', 3));
    }

    public function test_roa_and_payout_ratio_are_computed(): void
    {
        $statement = new FinancialStatement([
            'fiscal_year' => 2024,
            'period_type' => 'FY',
            'net_sales' => 1000,
            'profit' => 100,
            'total_assets' => 2000,
            'eps' => 50,
            'dividend_per_share' => 20,
        ]);

        $rows = (new FinancialTrendCalculator(new Collection([$statement])))->toTrendRows();

        $this->assertEquals(5.0, $rows[0]['roa']);
        $this->assertEquals(40.0, $rows[0]['payout_ratio']);
        $this->assertEquals(20, $rows[0]['dividend_per_share']);
    }

    public function test_roa_is_null_when_total_assets_missing(): void
    {
        $statements = new Collection([$this->makeStatement(2024, 1000, null, 100)]);

        $rows = (new FinancialTrendCalculator($statements))->toTrendRows();

        $this->assertNull($rows[0]['roa']);
    }
}
