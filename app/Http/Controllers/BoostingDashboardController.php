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

    /**
     * Show the boosting dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get the balance of the user
        $balance = $this->api->balance();

        // Get all the services
        $services = $this->api->services();

        // Get all the categories from the services
        $categories = collect($services)->pluck('category')->unique()->values()->all();

        // Log the categories for debugging
        Log::info('Categories Loaded: ' . json_encode($categories, JSON_PRETTY_PRINT));

        // Return the view with the data
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

