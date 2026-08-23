<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class IndexController extends Controller
{

    public function index()
    {
        $apiKey = config('services.twelvedata.key');
        $symbol = 'N225';

        $response = Http::get('https://api.twelvedata.com/price', [
            'symbol' => $symbol,
            'apikey' => $apiKey
        ]);

        $data = $response->json();

        $nikkei = $data['price'] ?? '取得失敗';

        return view('index', compact('nikkei'));
    }
}
