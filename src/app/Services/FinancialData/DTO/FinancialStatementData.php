<?php

namespace App\Services\FinancialData\DTO;

/**
 * Provider-agnostic representation of a single fiscal period's financial
 * statement. Providers (JQuantsProvider, a future EdinetProvider, ...) map
 * their own response shape into this DTO so the ingestion command and
 * FinancialStatement model never depend on any one provider's field names.
 */
class FinancialStatementData
{
    public function __construct(
        public readonly int $fiscalYear,
        public readonly string $periodType,
        public readonly ?string $fiscalPeriodEnd,
        public readonly ?string $disclosedDate,
        public readonly ?int $netSales,
        public readonly ?int $operatingProfit,
        public readonly ?int $ordinaryProfit,
        public readonly ?int $profit,
        public readonly ?float $eps,
        public readonly ?float $bps,
        public readonly ?float $roe,
        public readonly ?float $equityRatio,
        public readonly ?int $totalAssets,
        public readonly ?int $netAssets,
        public readonly ?float $dividendPerShare,
        public readonly array $raw,
    ) {
    }

    public function toModelAttributes(string $source): array
    {
        return [
            'fiscal_year' => $this->fiscalYear,
            'period_type' => $this->periodType,
            'fiscal_period_end' => $this->fiscalPeriodEnd,
            'disclosed_date' => $this->disclosedDate,
            'net_sales' => $this->netSales,
            'operating_profit' => $this->operatingProfit,
            'ordinary_profit' => $this->ordinaryProfit,
            'profit' => $this->profit,
            'eps' => $this->eps,
            'bps' => $this->bps,
            'roe' => $this->roe,
            'equity_ratio' => $this->equityRatio,
            'total_assets' => $this->totalAssets,
            'net_assets' => $this->netAssets,
            'dividend_per_share' => $this->dividendPerShare,
            'source' => $source,
            'raw_payload' => $this->raw,
        ];
    }
}
