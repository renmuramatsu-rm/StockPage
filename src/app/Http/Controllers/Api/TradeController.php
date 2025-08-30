<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TradeRequest;
use App\Http\Resources\TradeResource;
use App\Models\Trade;

class TradeController extends Controller
{
    public function index()
    {
        return TradeResource::collection(Trade::all());
    }

    public function show(Trade $trade)
    {
        return new TradeResource($trade);
    }

    public function store(TradeRequest $request)
    {
        $data = $request->validated();
        $trade = Trade::create($data);
        return new TradeResource($trade);
    }

    public function update(TradeRequest $request, Trade $trade)
    {
        $data = $request->validated();
        $trade->update($data);
        return new TradeResource($trade);
    }

    public function destroy(Trade $trade)
    {
        $trade->delete();
        return response('', 204);
    }
}
