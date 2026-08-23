<?php

namespace Tests\Unit;

use App\Services\FinancialAnalysis\CompanyOverviewBuilder;
use PHPUnit\Framework\TestCase;

class CompanyOverviewBuilderTest extends TestCase
{
    public function test_builds_a_full_overview_with_all_data_present(): void
    {
        $text = (new CompanyOverviewBuilder())->build('トヨタ自動車', [
            'market' => 'プライム',
            'scale_category' => 'TOPIX Large70',
            'themes' => ['自動車・輸送機'],
            'latest' => [
                'fiscal_year' => 2026,
                'net_sales' => 5_068_495_200_000,
                'yoy_net_sales' => 5.5,
                'operating_profit' => 376_620_000_000,
                'roe' => 10.1,
            ],
        ]);

        $this->assertStringContainsString('東証プライム市場に上場', $text);
        $this->assertStringContainsString('自動車・輸送機に分類される', $text);
        $this->assertStringContainsString('TOPIX Large70', $text);
        $this->assertStringContainsString('売上高は50,685億円（前年比5.5%）、営業利益は3,766億円、ROEは10.1%です。', $text);
    }

    public function test_handles_missing_data_gracefully(): void
    {
        $text = (new CompanyOverviewBuilder())->build('テスト企業', [
            'market' => null,
            'scale_category' => null,
            'themes' => [],
            'latest' => null,
        ]);

        $this->assertSame('テスト企業の情報です。', $text);
    }

    public function test_omits_yoy_and_operating_profit_when_unavailable(): void
    {
        $text = (new CompanyOverviewBuilder())->build('テスト企業', [
            'market' => 'グロース',
            'scale_category' => null,
            'themes' => [],
            'latest' => [
                'fiscal_year' => 2026,
                'net_sales' => 1_000_000_000,
                'yoy_net_sales' => null,
                'operating_profit' => null,
                'roe' => null,
            ],
        ]);

        $this->assertStringContainsString('売上高は10億円', $text);
        $this->assertStringNotContainsString('前年比', $text);
        $this->assertStringNotContainsString('営業利益', $text);
        $this->assertStringNotContainsString('ROE', $text);
    }
}
