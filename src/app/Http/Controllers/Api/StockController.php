<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stock;
use App\Http\Requests\StockRequest;
use App\Http\Resources\StockResource;

class StockController extends Controller
{
    public function index()
    {
        $stockData = StockResource::collection(Stock::all())->resolve();
        return view('index', ['stocks' => $stockData]);
    }

    public function show(Stock $stock)
    {
        return new StockResource($stock);
    }

    public function store(StockRequest $request)
    {
        $data = $request->validated();
        $stock = Stock::create($data);
        return new StockResource($stock);
    }

    public function update(StockRequest $request, Stock $stock)
    {
        $data = $request->validated();
        $stock->update($data);
        return new StockResource($stock);
    }

    public function destroy(Stock $stock)
    {
        $stock->delete();
        return response("", 204);
    }
}
