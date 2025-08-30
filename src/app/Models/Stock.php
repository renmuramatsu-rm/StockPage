<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $primaryKey = 'code';
    protected $fillable = [
        'code',
        'stockName',
        'market_id'
    ];
    public $timestamps = false;

    public function market()
    {
        return $this->belongsTo(Market::class, 'market_id');
    }
}
