<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SbiHolding extends Model
{
    protected $fillable = [
        'code',
        'shares',
        'average_acquisition_price',
        'acquisition_date',
        'account_type',
        'memo',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'average_acquisition_price' => 'decimal:2',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'code', 'code');
    }

    public function getAcquisitionCostAttribute()
    {
        return $this->shares * $this->average_acquisition_price;
    }
}
