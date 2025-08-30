<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class IndexController extends Controller
{

    public function index()
    {
        $apiKey = '2659626995c143e5a133d601e23bbfeb';
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
