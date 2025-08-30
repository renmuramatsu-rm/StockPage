<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TradeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->stock,
            'buy_sell' => $this->buy_sell,
            'shares' => $this->shares,
            'price' => $this->price,
            'trading_date' => $this->trading_date,
        ];
    }
}
