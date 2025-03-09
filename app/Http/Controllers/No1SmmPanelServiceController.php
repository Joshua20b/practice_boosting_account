<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class No1SmmPanelServiceController extends Controller
{
    private string $api_url;
    private string $api_key;

    public function __construct()
    {
        // Default to no1smmpanel.com API URL; adjust if exact endpoint differs
        $this->api_url = config('services.smmpanel.url', 'https://no1smmpanel.com/api/v1');
        // Ensure $api_key is always a string, defaulting to empty string if not set
        $this->api_key = config('services.smmpanel.api_key', '') ?: '';
        Log::info('API Configuration - URL: ' . $this->api_url . ', Key: ' . $this->api_key);
    }

    /**
     * Make a POST request to the API with the given data.
     *
     * @param array $data
     * @return string|null
     */
    private function connect(array $data): ?string
    {
        try {
            Log::info('API Request Data: ' . json_encode($data, JSON_PRETTY_PRINT));
            $response = Http::asForm()
                ->timeout(30)
                ->post($this->api_url, $data);

            if (!$response->successful()) {
                throw new Exception('API request failed with status ' . $response->status() . ': ' . $response->body());
            }

            $body = $response->body();
            Log::info('API Response: ' . json_encode(json_decode($body, true), JSON_PRETTY_PRINT));
            return $body;
        } catch (Exception $e) {
            Log::error('SMM API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Add a new order to the API.
     *
     * @param array $data
     * @return object|null
     */
    public function add_order(array $data): ?object
    {
        $post = array_merge(['key' => $this->api_key, 'action' => 'add'], $data);
        $response = $this->connect($post);
        return $response ? json_decode($response) : null;
    }

    /**
     * Get the status of an existing order from the API.
     *
     * @param int $orderId
     * @return object|null
     */
    public function status(int $orderId): ?object
    {
        $response = $this->connect([
            'key' => $this->api_key,
            'action' => 'status',
            'order' => $orderId
        ]);
        return $response ? json_decode($response) : null;
    }

    /**
     * Get the status of multiple existing orders from the API.
     *
     * @param int|array $orderIds
     * @return object|null
     */
    public function multi_status($orderIds): ?object
    {
        $response = $this->connect([
            'key' => $this->api_key,
            'action' => 'status',
            'orders' => implode(',', (array)$orderIds)
        ]);
        return $response ? json_decode($response) : null;
    }

    /**
     * Get a list of services from the API.
     *
     * @return array
     */
    public function services(): array
    {
        $services = Cache::remember('smm_services', 3600, function () {
            $response = $this->connect([
                'key' => $this->api_key,
                'action' => 'services'
            ]);

            if (!$response) {
                Log::error('No response from No1SMMPanel.com API for services - check API key, URL, or server status ');
                return [];
            }

            $services = json_decode($response, true);
            Log::info('Raw Services Data from API: ' . json_encode($services, JSON_PRETTY_PRINT));

            if (!is_array($services) || empty($services)) {
                Log::warning('API returned invalid or empty services data');
                return [];
            }

            // Process services to ensure all have categories
            $categoryMap = [
                '1' => 'Instagram',
                '2' => 'YouTube',
                '3' => 'TikTok',
                // Expand based on no1smmpanel.com service IDs once known
            ];

            foreach ($services as &$service) {
                $service['service'] = $service['service'] ?? 'Unknown ID';
                $service['name'] = $service['name'] ?? 'Unnamed Service';
                $service['rate'] = $service['rate'] ?? '0.00';
                $service['min'] = $service['min'] ?? '1';
                $service['max'] = $service['max'] ?? '1000';

                if (!isset($service['category']) || empty($service['category'])) {
                    $serviceId = (string)$service['service'];
                    $service['category'] = $categoryMap[$serviceId] ?? 'Other';
                    Log::debug("Assigned category '{$service['category']}' to service ID '{$serviceId}'");
                }
            }

            Log::info('Processed Services with Categories: ' . json_encode($services, JSON_PRETTY_PRINT));
            return $services;
        });

        return is_array($services) ? $services : [];
    }

    /**
     * Get the current balance from the API.
     *
     * @return object|null
     */
    public function balance(): ?object
    {
        $response = $this->connect([
            'key' => $this->api_key,
            'action' => 'balance'
        ]);
        return $response ? json_decode($response) : null;
    }
}
