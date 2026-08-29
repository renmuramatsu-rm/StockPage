<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Stock;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index()
    {
        $stocks = auth()->user()->favoriteStocks()->with(['market', 'themes', 'score'])->orderBy('code')->get();

        return response()->json(['stocks' => $stocks]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:10|exists:stocks,code',
        ]);

        $favorite = Favorite::firstOrCreate([
            'user_id' => auth()->id(),
            'code' => $data['code'],
        ]);

        return response()->json(['favorite' => $favorite], 201);
    }

    public function destroy(Stock $stock)
    {
        Favorite::where('user_id', auth()->id())->where('code', $stock->code)->delete();

        return response()->json(null, 204);
    }
}
