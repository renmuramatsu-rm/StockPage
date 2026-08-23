<?php

namespace App\Console\Commands;

use App\Services\FinancialData\Contracts\FinancialStatementProviderInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncStocks extends Command
{
    protected $signature = 'stocks:sync {--date= : YYYYMMDD; defaults to ~90 days ago (rolled back to a weekday) to stay inside the J-Quants free-plan window} {--market=プライム,グロース : Comma-separated market segment names to import (as returned by J-Quants MktNm)}';

    protected $description = 'Import listed stocks for one or more market segments from the J-Quants listed issue master';

    /**
     * How many earlier weekdays to try if a date has no data — the
     * listed-issue master has no snapshot for weekends or JP market
     * holidays, and there is no holiday calendar baked in here.
     */
    private const MAX_DATE_FALLBACKS = 7;

    public function handle(FinancialStatementProviderInterface $provider): int
    {
        $explicitDate = $this->option('date');
        $markets = array_filter(array_map('trim', explode(',', $this->option('market'))));

        [$date, $issues] = $explicitDate
            ? [$explicitDate, $this->fetchForMarkets($provider, $explicitDate, $markets)]
            : $this->fetchWithFallback($provider, $markets);

        if ($issues->isEmpty()) {
            $marketList = implode('/', $markets);
            $hint = $explicitDate
                ? "Check that [{$date}] is a Japanese market trading day within your plan's allowed range."
                : "Tried {$date} and the ".self::MAX_DATE_FALLBACKS." weekdays before it — all came back empty. This usually means a connection/API problem rather than a holiday run; check the logs and your JQUANTS_API_KEY.";
            $this->error("No listed issues found for market(s) [{$marketList}] on date [{$date}]. {$hint}");

            return self::FAILURE;
        }

        $marketIds = [];
        $themeIds = [];
        $created = 0;
        $updated = 0;
        $themed = 0;
        $byMarket = [];

        foreach ($issues as $issue) {
            $marketId = $marketIds[$issue['market']] ??= $this->resolveMarketId($issue['market']);

            $exists = DB::table('stocks')->where('code', $issue['code'])->exists();

            DB::table('stocks')->updateOrInsert(
                ['code' => $issue['code']],
                ['stockName' => $issue['name'], 'market_id' => $marketId, 'scale_category' => $issue['scale_category'] ?? null]
            );

            $exists ? $updated++ : $created++;
            $byMarket[$issue['market']] = ($byMarket[$issue['market']] ?? 0) + 1;

            if (! empty($issue['sector17'])) {
                $themeId = $themeIds[$issue['sector17']] ??= $this->resolveThemeId($issue['sector17']);

                $alreadyTagged = DB::table('stock_theme')
                    ->where('stock_code', $issue['code'])
                    ->where('theme_id', $themeId)
                    ->exists();

                if (! $alreadyTagged) {
                    DB::table('stock_theme')->insert([
                        'stock_code' => $issue['code'],
                        'theme_id' => $themeId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $themed++;
                }
            }
        }

        $this->table(
            ['Market', 'Count'],
            collect($byMarket)->map(fn ($count, $market) => [$market, $count])->values()
        );

        $this->table(
            ['Date', 'Created', 'Updated', 'New theme tags (17業種)'],
            [[$date, $created, $updated, $themed]]
        );

        return self::SUCCESS;
    }

    /**
     * @param  string[]  $markets
     * @return array{0: string, 1: \Illuminate\Support\Collection}
     */
    private function fetchWithFallback(FinancialStatementProviderInterface $provider, array $markets): array
    {
        $day = now()->subDays(90);

        for ($attempt = 0; $attempt <= self::MAX_DATE_FALLBACKS; $attempt++) {
            while ($day->isWeekend()) {
                $day = $day->subDay();
            }

            $date = $day->format('Ymd');
            $issues = $this->fetchForMarkets($provider, $date, $markets);

            if ($issues->isNotEmpty()) {
                return [$date, $issues];
            }

            $this->warn("No data for {$date} (likely a market holiday) — trying an earlier date...");
            $day = $day->subDay();
        }

        return [$date, $issues];
    }

    /**
     * @param  string[]  $markets
     */
    private function fetchForMarkets(FinancialStatementProviderInterface $provider, string $date, array $markets)
    {
        return $provider->fetchListedIssues($date)
            ->filter(fn (array $row) => in_array($row['market'], $markets, true))
            ->values();
    }

    private function resolveMarketId(string $marketName): int
    {
        $id = DB::table('markets')->where('market', $marketName)->value('id');

        return $id ?? DB::table('markets')->insertGetId(['market' => $marketName]);
    }

    private function resolveThemeId(string $sectorName): int
    {
        $id = DB::table('themes')->where('name', $sectorName)->value('id');

        return $id ?? DB::table('themes')->insertGetId([
            'name' => $sectorName,
            'source' => 'jquants_17',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
