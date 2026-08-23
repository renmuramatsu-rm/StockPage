<?php

namespace App\Services\FinancialData;

use App\Models\FinancialStatement;
use App\Models\Stock;
use App\Services\FinancialData\Contracts\FinancialStatementProviderInterface;

/**
 * Shared upsert logic for pulling one stock's financial statement history
 * from the provider and storing it. Used by both the financials:sync
 * Artisan command (bulk/background) and the stock detail page (on-demand,
 * only when that stock has no or stale data).
 */
class FinancialStatementSyncer
{
    /**
     * How long a stock's synced financial data (and derived score) stays
     * considered fresh — used both by the on-demand sync-on-page-view path
     * and by financials:sync's default "skip if already fresh" behavior.
     */
    public const STALE_AFTER_HOURS = 24;

    public function __construct(private readonly FinancialStatementProviderInterface $provider)
    {
    }

    public function needsSync(Stock $stock): bool
    {
        $lastSyncedAt = $stock->financialStatements()->max('updated_at');

        return $lastSyncedAt === null
            || now()->diffInHours($lastSyncedAt) >= self::STALE_AFTER_HOURS;
    }

    /**
     * @return int number of statement rows upserted
     */
    public function sync(Stock $stock): int
    {
        $statements = $this->provider->fetchStatements((string) $stock->code);

        foreach ($statements as $dto) {
            FinancialStatement::updateOrCreate(
                [
                    'code' => $stock->code,
                    'fiscal_year' => $dto->fiscalYear,
                    'period_type' => $dto->periodType,
                ],
                $dto->toModelAttributes('jquants')
            );
        }

        return $statements->count();
    }
}
