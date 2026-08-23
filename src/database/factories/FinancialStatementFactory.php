<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FinancialStatement>
 */
class FinancialStatementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $netSales = fake()->numberBetween(1_000_000_000, 5_000_000_000_000);
        $operatingProfit = (int) ($netSales * fake()->randomFloat(2, 0.02, 0.15));
        $profit = (int) ($operatingProfit * fake()->randomFloat(2, 0.5, 0.9));

        return [
            'code' => fake()->numberBetween(1300, 9999),
            'fiscal_year' => fake()->numberBetween(2021, 2025),
            'period_type' => 'FY',
            'fiscal_period_end' => fake()->date(),
            'disclosed_date' => fake()->date(),
            'net_sales' => $netSales,
            'operating_profit' => $operatingProfit,
            'ordinary_profit' => $operatingProfit,
            'profit' => $profit,
            'eps' => fake()->randomFloat(2, 10, 500),
            'bps' => fake()->randomFloat(2, 500, 5000),
            'roe' => fake()->randomFloat(4, 1, 20),
            'equity_ratio' => fake()->randomFloat(4, 20, 70),
            'total_assets' => $netSales * 2,
            'net_assets' => $netSales,
            'dividend_per_share' => fake()->randomFloat(2, 0, 100),
            'source' => 'jquants',
            'raw_payload' => [],
        ];
    }
}
