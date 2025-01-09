<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class NewsApiService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://newsapi.org/v2/';
        $this->apiKey = config('services.newsapi.key');
    }

    public function getTopHeadlines($countrySelected, $category = null)
    {
        $url = $this->baseUrl . 'top-headlines';

        if (empty($countrySelected)) {
            $country = 'us';
        } else {
            $country = $countrySelected;
        }

        $params = [
            'country' => $country,
            'apiKey' => $this->apiKey,
        ];

        if ($category) {
            $params['category'] = $category;
        }


        try {
            $response = Http::timeout(10)->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("News API returned error: " . $response->body());
            return ['error' => 'News API error.'];
        } catch (RequestException $e) {
            Log::error("News API request failed: " . $e->getMessage());
            return ['error' => 'Unable to fetch news at this time.'];
        } catch (\Exception $e) {
            Log::error("Unexpected error: " . $e->getMessage());
            return ['error' => 'An unexpected error occurred.'];
        }
    }

    /**
     *
     * @param
     */



    public function getEverything($query, $from = null, $to = null, $sortBy = null, $pageSize = 100, $page=1)
    {
        $url = "{$this->baseUrl}/everything";
        if($sortBy == null){
            $sortBy='publishedAt';
        }
        $params = [
            'q' => $query,
            'from' => $from,
            'to' => $to,
            'sortBy' => $sortBy,
            'pageSize' => $pageSize,
            'page'=>$page,
            'apiKey' => $this->apiKey,
        ];

        return $this->makeRequest($url, $params);
    }

    private function makeRequest($url, $params)
    {
        try {
            $response = Http::get($url, $params);

            if ($response->successful()) {
                return $response->json();
            } else {
                return ['error' => $response->status() . ': ' . $response->body()];
            }
        } catch (\Exception $e) {
            return ['error' => 'Request failed: ' . $e->getMessage()];
        }
    }
}
