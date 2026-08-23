<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'color',
        'source',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stocks()
    {
        return $this->belongsToMany(Stock::class, 'stock_theme', 'theme_id', 'stock_code', 'id', 'code');
    }

    /**
     * True for auto-generated sector tags (e.g. from J-Quants), which
     * have no owner and are shared read-only across every user.
     */
    public function isSystem(): bool
    {
        return $this->user_id === null;
    }

    /**
     * System sector tags plus the given user's own custom themes.
     */
    public function scopeVisibleTo(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $query) use ($userId) {
            $query->whereNull('user_id')->orWhere('user_id', $userId);
        });
    }
}
