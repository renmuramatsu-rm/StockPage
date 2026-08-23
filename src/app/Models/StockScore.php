<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockScore extends Model
{
    protected $fillable = [
        'code',
        'overall_score',
        'badge',
        'badge_color',
        'growth_score',
        'growth_label',
        'valuation_score',
        'valuation_label',
        'quality_score',
        'quality_label',
        'current_price',
        'price_date',
        'price_change',
        'price_change_percent',
        'per',
        'pbr',
        'computed_at',
    ];

    protected $casts = [
        'current_price' => 'decimal:2',
        'price_date' => 'date',
        'price_change' => 'decimal:2',
        'price_change_percent' => 'decimal:2',
        'per' => 'decimal:2',
        'pbr' => 'decimal:2',
        'computed_at' => 'datetime',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'code', 'code');
    }
}
