<?php

namespace App\Http\Controllers;

use App\Models\NewsArticles;
use Illuminate\Http\Request;
use App\Services\NewsApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class NewsApiController extends Controller
{
    protected $newsApiService;

    public  function __construct(NewsApiService $newsApiService)
    {
        $this->newsApiService = $newsApiService;
    }

    public function index()
    {
        $headlines = $this->newsApiService->getTopHeadlines('us', 'technology');

        return view('news-api.index', compact('headlines'));
    }

    public function preloadArticles(Request $request)
    {

        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 30);
        $query = $request->get('searchQuery', '');

        $articlesQuery = NewsArticles::query();
        if ($request->has('searchQuery') && !empty($request->searchQuery)) {
            $searchKey = $request->searchQuery;
            $articlesQuery->where(function ($q) use ($searchKey) {
                $q->where('title', 'LIKE', "%{$searchKey}%")
                    ->orWhere('content', 'LIKE', "%{$searchKey}%")
                    ->orWhere('description', 'LIKE', "%{$searchKey}%");
            });
        }

        Log::info("updted suh params****************************************************************");

        $articles = $articlesQuery->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        return response()->json(['articles' => $articles]);
    }


    public function updateDatabaseRecords(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'fetch_records' => 'required|integer|max:100',
            'fetch_from' => 'nullable|date',
            'fetch_to' => 'nullable|date',
            'sort_by' => 'nullable|string',
        ], [
            'fetch_records.required' => 'Please provide the number of records to fetch',
            'fetch_records.max' => 'You can fetch a maximum of 100 records at a time',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'errors' => $validatedData->errors()
            ], 422);
        }

        try {
            $query = "technology";
            $from = $request->fetch_from;
            $to = $request->fetch_to;
            $sortBy = $request->sort_by;
            $numArticles = min($request->fetch_records, 100);


            $response = $this->newsApiService->getEverything($query, $from, $to, $sortBy, $numArticles);

            if (isset($response['error'])) {
                return response()->json(['error' => $response['error']], 400);
            }

            if (empty($response['articles'])) {
                return response()->json(['error' => 'No articles found.'], 404);
            }

            // Save articles to the database
            foreach ($response['articles'] as $article) {
                NewsArticles::create([
                    'news_category' => 'NewsAPI',
                    'source_name' => $article['source']['name'] ?? null,
                    'author' => $article['author'] ?? null,
                    'title' => $article['title'],
                    'description' => $article['description'],
                    'url' => $article['url'],
                    'url_to_image' => $article['urlToImage'] ?? null,
                    'content' => $article['content'],
                    'published_at' => Carbon::parse($article['publishedAt']),
                    'sort_by' => $sortBy ?? 'publishedAt'
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Articles successfully fetched and saved.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function cleanDatabase(Request $request)
    {
        
        try {
            NewsArticles::where('news_category', 'NewsAPI')->delete();

            $message = 'Successfully deleted news articles';

            if ($request->ajax()) {
                return response()->json(['response' => 'success', 'message' => $message], 200);
            } else {
                return redirect()->back()->with('success', $message);
            }
        } catch (\Exception $e) {
            Log::error("Failed to delete News Api articles", ['error' => $e->getMessage()]);

            $message = 'Failed to delete articles: ' . $e->getMessage();

            if ($request->ajax()) {
                return response()->json(['response' => 'error', 'message' => $message], 500);
            } else {
                return redirect()->back()->with('error', $message);
            }
        }
    }
}
