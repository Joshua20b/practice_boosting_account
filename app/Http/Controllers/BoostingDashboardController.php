<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BoostingDashboardController extends Controller
{

    protected $api;
    private $markupPercentage = 20;

    public function __construct(No1SmmPanelServiceController $api)
    {
        $this->api = $api;
    }

    public function index()
    {
        $balance = $this->api->balance();
        $services = $this->api->services();
        // $orders = auth()->user()->orders()->latest()->get();

        $categories = collect($services)->pluck('category')->unique()->values()->all();
        Log::info('Categories Loaded: ' . json_encode($categories, JSON_PRETTY_PRINT));

        return view('index', [
            'balance' => $balance?->balance ?? 0,
            'currency' => $balance?->currency ?? 'USD',
            'services' => $services,
            'categories' => $categories,
            // 'orders' => $orders,
            'userBalance' => 0,
            'markupPercentage' => $this->markupPercentage
        ]);
    }
}
