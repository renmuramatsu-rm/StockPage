<?php

namespace Tests\Feature;

use App\Services\FinancialData\Contracts\FinancialStatementProviderInterface;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JQuantsQuoteTest extends TestCase
{
    public function test_fetch_latest_quote_computes_change_from_previous_close(): void
    {
        Http::fake([
            '*/equities/bars/daily*' => Http::response(['data' => [
                ['Date' => '2026-05-20', 'C' => '2500'],
                ['Date' => '2026-05-19', 'C' => '2450'],
            ]]),
        ]);

        $provider = app(FinancialStatementProviderInterface::class);
        $quote = $provider->fetchLatestQuote('7203');

        $this->assertEquals('2026-05-20', $quote['date']);
        $this->assertEquals(2500.0, $quote['close']);
        $this->assertEquals(2450.0, $quote['previous_close']);
        $this->assertEquals(50.0, $quote['change']);
        $this->assertEqualsWithDelta(2.04, $quote['change_percent'], 0.01);
    }

    public function test_fetch_latest_quote_has_no_change_with_only_one_data_point(): void
    {
        Http::fake([
            '*/equities/bars/daily*' => Http::response(['data' => [
                ['Date' => '2026-05-20', 'C' => '2500'],
            ]]),
        ]);

        $provider = app(FinancialStatementProviderInterface::class);
        $quote = $provider->fetchLatestQuote('7203');

        $this->assertEquals(2500.0, $quote['close']);
        $this->assertNull($quote['previous_close']);
        $this->assertNull($quote['change']);
    }

    public function test_fetch_latest_quote_returns_null_when_no_data_available(): void
    {
        Http::fake([
            '*/equities/bars/daily*' => Http::response(['data' => []]),
        ]);

        $provider = app(FinancialStatementProviderInterface::class);

        $this->assertNull($provider->fetchLatestQuote('7203'));
    }

    public function test_fetch_latest_quote_returns_null_on_connection_failure(): void
    {
        Http::fake([
            '*/equities/bars/daily*' => Http::failedConnection('cURL error 28: timeout'),
        ]);

        $provider = app(FinancialStatementProviderInterface::class);

        $this->assertNull($provider->fetchLatestQuote('7203'));
    }
}
