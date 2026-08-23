<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $primaryKey = 'code';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'code',
        'stockName',
        'market_id',
        'scale_category',
    ];
    public $timestamps = false;

    public function market()
    {
        return $this->belongsTo(Market::class, 'market_id');
    }

    public function financialStatements()
    {
        return $this->hasMany(FinancialStatement::class, 'code', 'code');
    }

    public function themes()
    {
        return $this->belongsToMany(Theme::class, 'stock_theme', 'stock_code', 'theme_id', 'code', 'id');
    }

    public function sbiHolding()
    {
        return $this->hasOne(SbiHolding::class, 'code', 'code');
    }

    public function score()
    {
        return $this->hasOne(StockScore::class, 'code', 'code');
    }
}
