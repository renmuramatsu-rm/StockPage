<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'source',
    ];

    public function stocks()
    {
        return $this->belongsToMany(Stock::class, 'stock_theme', 'theme_id', 'stock_code', 'id', 'code');
    }
}
