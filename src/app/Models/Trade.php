<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trade extends Model
{
    protected $fillable = [
        'id',
        'code',
        'buy_sell',
        'shares',
        'price',
        'trading_date'
    ];

    public $timestamps = false;

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'code');
    }
}
