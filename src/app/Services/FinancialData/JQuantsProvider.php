<?php

namespace App\Services\FinancialData;

use App\Services\FinancialData\Contracts\FinancialStatementProviderInterface;
use App\Services\FinancialData\DTO\FinancialStatementData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches financial statement history from JPX's official J-Quants API
 * (https://jpx-jquants.com), a free/paid registration-based API that
 * distributes the same disclosed financial data underlying the Kaisha
 * Shikiho, without touching Toyo Keizai's copyrighted publication itself.
 *
 * As of the V2 API (accounts registered on/after 2025-12-22 are V2-only),
 * authentication is a single API key issued from the J-Quants dashboard,
 * sent via the `x-api-key` header — the old V1 email/password -> refresh
 * token -> id token flow has been discontinued for these accounts.
 */
class JQuantsProvider implements FinancialStatementProviderInterface
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $apiKey,
    ) {
    }

    public function fetchStatements(string $code): Collection
    {
        $response = $this->authedGet('/fins/summary', ['code' => $code]);

        if ($response === null || ! $response->successful()) {
            Log::warning("JQuantsProvider: failed to fetch statements for code {$code}", [
                'status' => $response?->status(),
                'body' => $response?->body(),
            ]);

            return collect();
        }

        $rows = $response->json('data') ?? [];

        return collect($rows)
            ->map(fn (array $row) => $this->mapRow($row))
            ->filter()
            ->values();
    }

    public function fetchLatestQuote(string $code): ?array
    {
        // Bounded to a window inside the free plan's ~12-week-delayed
        // availability, so the response stays small and we don't need to
        // guess today's exact cutoff — see stocks:sync's date fallback for
        // the same constraint.
        $to = now()->subDays(85);
        $from = $to->copy()->subDays(20);

        $response = $this->authedGet('/equities/bars/daily', [
            'code' => $code,
            'from' => $from->format('Ymd'),
            'to' => $to->format('Ymd'),
        ]);

        if ($response === null || ! $response->successful()) {
            Log::warning("JQuantsProvider: failed to fetch price for code {$code}", [
                'status' => $response?->status(),
            ]);

            return null;
        }

        $quotes = collect($response->json('data') ?? [])->sortByDesc('Date')->values();

        if ($quotes->isEmpty()) {
            return null;
        }

        $latest = $quotes->get(0);
        $previous = $quotes->get(1);

        $close = $this->numeric($latest['C'] ?? null);
        $previousClose = $previous ? $this->numeric($previous['C'] ?? null) : null;
        $change = ($close !== null && $previousClose !== null) ? round($close - $previousClose, 1) : null;
        $changePercent = ($change !== null && $previousClose) ? round(($change / $previousClose) * 100, 2) : null;

        return [
            'date' => $latest['Date'] ?? null,
            'close' => $close,
            'previous_close' => $previousClose,
            'change' => $change,
            'change_percent' => $changePercent,
        ];
    }

    public function fetchListedIssues(string $date): Collection
    {
        $response = $this->authedGet('/equities/master', ['date' => $date]);

        if ($response === null || ! $response->successful()) {
            Log::warning('JQuantsProvider: failed to fetch listed issues', [
                'status' => $response?->status(),
                'body' => $response?->body(),
            ]);

            return collect();
        }

        $rows = $response->json('data') ?? [];

        return collect($rows)
            ->map(fn (array $row) => $this->mapListedIssueRow($row))
            ->filter()
            ->values();
    }

    private function mapListedIssueRow(array $row): ?array
    {
        $code = $row['Code'] ?? null;
        $name = $row['CoName'] ?? null;
        $market = $row['MktNm'] ?? null;
        $sector17 = $row['S17Nm'] ?? null;
        $scaleCategory = $row['ScaleCat'] ?? null;

        if (! $code || ! $name || ! $market) {
            return null;
        }

        // J-Quants uses a 5-character code (trailing 0 for the primary
        // listing); our stocks table keys on the traditional 4-character
        // code. Since 2024 TSE also issues alphanumeric codes (e.g.
        // "167A"), so this must stay a string — never cast to int.
        if (strlen($code) === 5 && str_ends_with($code, '0')) {
            $code = substr($code, 0, 4);
        }

        return [
            'code' => $code,
            'name' => $name,
            'market' => $market,
            'sector17' => $sector17,
            'scale_category' => $scaleCategory ?: null,
        ];
    }

    private function mapRow(array $row): ?FinancialStatementData
    {
        $periodType = $row['CurPerType'] ?? null;
        $fiscalYearEnd = $row['CurFYEn'] ?? null;

        if (! $periodType || ! $fiscalYearEnd) {
            return null;
        }

        return new FinancialStatementData(
            fiscalYear: (int) substr($fiscalYearEnd, 0, 4),
            periodType: $periodType,
            fiscalPeriodEnd: $row['CurPerEn'] ?? null,
            disclosedDate: $row['DiscDate'] ?? null,
            netSales: ($v = $this->numeric($row['Sales'] ?? null)) !== null ? (int) $v : null,
            operatingProfit: ($v = $this->numeric($row['OP'] ?? null)) !== null ? (int) $v : null,
            ordinaryProfit: ($v = $this->numeric($row['OdP'] ?? null)) !== null ? (int) $v : null,
            profit: ($v = $this->numeric($row['NP'] ?? null)) !== null ? (int) $v : null,
            eps: $this->numeric($row['EPS'] ?? null),
            bps: $this->numeric($row['BPS'] ?? null),
            // J-Quants returns ROE/EqAR as a 0-1 ratio (e.g. 0.136); we
            // store/display these as percentages, so convert here.
            roe: ($v = $this->numeric($row['ROE'] ?? null)) !== null ? round($v * 100, 4) : null,
            equityRatio: ($v = $this->numeric($row['EqAR'] ?? null)) !== null ? round($v * 100, 4) : null,
            totalAssets: ($v = $this->numeric($row['TA'] ?? null)) !== null ? (int) $v : null,
            netAssets: ($v = $this->numeric($row['Eq'] ?? null)) !== null ? (int) $v : null,
            dividendPerShare: $this->numeric($row['DivAnn'] ?? null),
            raw: $row,
        );
    }

    /**
     * J-Quants represents "not applicable" numeric fields as "-" or "".
     */
    private function numeric(mixed $value): ?float
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        return (float) $value;
    }

    private function authedGet(string $path, array $query): ?\Illuminate\Http\Client\Response
    {
        try {
            return Http::withHeaders(['x-api-key' => $this->apiKey])
                ->timeout(10)
                ->get($this->baseUrl.$path, $query);
        } catch (\Throwable $e) {
            Log::warning("JQuantsProvider: connection error on {$path}", ['message' => $e->getMessage()]);

            return null;
        }
    }
}
