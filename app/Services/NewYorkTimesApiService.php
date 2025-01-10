<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class NewYorkTimesApiService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {

        $timePeriods = [1, 7, 30];
        
        $randomPeriod = $timePeriods[array_rand($timePeriods)];

        $this->baseUrl = "https://api.nytimes.com/svc/mostpopular/v2/viewed/{$randomPeriod}.json";

        $this->apiKey = config('services.newyorktimes.key');
    }


    public function getArticles($query = '', $page = 0)
    {
        $params = [
            'api-key' => $this->apiKey,
        ];

        try {
            $response = Http::get($this->baseUrl, $params);

            if ($response->successful()) {
                return $response->json();
            }

            return ['error' => $response->status() . ': ' . $response->body()];
        } catch (\Exception $e) {
            return ['error' => 'Request failed: ' . $e->getMessage()];
        }
    }
}
