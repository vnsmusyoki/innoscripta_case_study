<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardianNewsService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.guardianapi.key');
        $this->baseUrl = 'https://content.guardianapis.com';
    }

    public function getArticles($query = '', $page = 1, $pageSize = 50)
    {
        try {
            $response = Http::get("{$this->baseUrl}/search", [
                'api-key' => $this->apiKey,
                'q' => $query,
                'page' => $page,
                'page-size' => $pageSize,
                'order-by' => 'newest',
                'show-fields' => 'headline,thumbnail,body,byline',
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('Guardian API error', ['response' => $response->body()]);
                return ['error' => 'Failed to fetch articles.'];
            }
        } catch (\Exception $e) {
            Log::error('Guardian API request failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function oldgetArticles($query = '', $page = 1, $pageSize = 3)
    {
        try {
            $response = Http::get("{$this->baseUrl}/search", [
                'api-key' => $this->apiKey,
                'q' => $query,
                'page' => $page,
                'page-size' => $pageSize,
                'show-fields' => 'headline,thumbnail,body',
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('Guardian API error', ['response' => $response->body()]);
                return ['error' => 'Failed to fetch articles.'];
            }
        } catch (\Exception $e) {
            Log::error('Guardian API request failed', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }
}
