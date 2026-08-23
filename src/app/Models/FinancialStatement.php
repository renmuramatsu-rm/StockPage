<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialStatement extends Model
{
    protected $fillable = [
        'code',
        'fiscal_year',
        'period_type',
        'fiscal_period_end',
        'disclosed_date',
        'net_sales',
        'operating_profit',
        'ordinary_profit',
        'profit',
        'eps',
        'bps',
        'roe',
        'equity_ratio',
        'total_assets',
        'net_assets',
        'dividend_per_share',
        'source',
        'raw_payload',
    ];

    protected $casts = [
        'fiscal_period_end' => 'date',
        'disclosed_date' => 'date',
        'raw_payload' => 'array',
        'eps' => 'decimal:2',
        'bps' => 'decimal:2',
        'roe' => 'decimal:4',
        'equity_ratio' => 'decimal:4',
        'dividend_per_share' => 'decimal:2',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'code', 'code');
    }

    public function scopeForStock($query, $code)
    {
        return $query->where('code', $code);
    }

    public function scopeFiscalYearOnly($query)
    {
        return $query->where('period_type', 'FY');
    }

    public function scopeOrderedByPeriod($query)
    {
        return $query->orderBy('fiscal_year');
    }
}
