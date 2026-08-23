<?php

namespace App\Services\FinancialData\Contracts;

use App\Services\FinancialData\DTO\FinancialStatementData;
use Illuminate\Support\Collection;

interface FinancialStatementProviderInterface
{
    /**
     * Fetch all available fiscal periods for a stock code.
     *
     * @return Collection<int, FinancialStatementData>
     */
    public function fetchStatements(string $code): Collection;

    /**
     * Fetch the most recent available quote for a stock code, including the
     * prior trading day's close (for a 前日比 display). Note: on the J-Quants
     * free plan, price data is delayed ~12 weeks, so "latest" here means
     * "most recent available under the plan", not necessarily today.
     *
     * @return array{date: ?string, close: ?float, previous_close: ?float, change: ?float, change_percent: ?float}|null
     */
    public function fetchLatestQuote(string $code): ?array;

    /**
     * Fetch the full listed-issue master as of a given date (YYYYMMDD).
     *
     * @return Collection<int, array{code: string, name: string, market: string, sector17: ?string, scale_category: ?string}>
     */
    public function fetchListedIssues(string $date): Collection;
}
