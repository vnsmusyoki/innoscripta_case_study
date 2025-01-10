<?php

namespace App\Http\Controllers;

use App\Models\NewsArticles;
use App\Services\NewYorkTimesApiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NewYorkTimesApiController extends Controller
{

    protected $nytService;

    public function __construct(NewYorkTimesApiService $nytService)
    {
        $this->nytService = $nytService;
    }

    public function index(Request $request)
    {
        return view('new-york-times.index');
    }

    public function fetchLatestArticles(Request $request)
    {
        $query = $request->get('searchQuery', '');
        $page = $request->get('page', 0);
        try {

            $response = $this->nytService->getArticles($query, $page);
            foreach ($response['results'] as $article) {
                $imageUrl = null;

                if (!empty($article['media']) && isset($article['media'][0]['media-metadata'][0]['url'])) {
                    $imageUrl = $article['media'][0]['media-metadata'][0]['url'];
                }
                $contentToInsert = $article['abstract'] . '' . $article['adx_keywords'];
                NewsArticles::create([
                    'news_category' => 'NewYorkTimes',
                    'source_name' => $article['source'] ?? 'New York Times',
                    'author' => $article['byline'] ?? null,
                    'title' => $article['title'],
                    'description' => $article['abstract'] ?? null,
                    'url' => $article['url'],
                    'url_to_image' => $imageUrl,
                    'content' => $contentToInsert ?? null,
                    'published_at' => Carbon::parse($article['updated']),
                    'sort_by' => 'published_date',
                ]);
            }
            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Articles successfully fetched and saved.']);
            } else {
                return redirect()->back()->with('success', 'Articles successfully fetched and saved.');
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function preloadArticles(Request $request)
    {

        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 30);
        $query = $request->get('searchQuery', '');

        $articlesQuery = NewsArticles::where('news_category', 'NewYorkTimes');
        if ($request->has('searchQuery') && !empty($request->searchQuery)) {
            $searchKey = $request->searchQuery;
            $articlesQuery->where(function ($q) use ($searchKey) {
                $q->where('title', 'LIKE', "%{$searchKey}%")
                    ->orWhere('content', 'LIKE', "%{$searchKey}%")
                    ->orWhere('description', 'LIKE', "%{$searchKey}%");
            });
        }

        Log::info("updted with New York Times Api params****************************");

        $articles = $articlesQuery->skip(($page - 1) * $pageSize)->take($pageSize)->get();
        if ($request->ajax()) {
            return response()->json(['articles' => $articles]);
        } else {
            return redirect()->back()->with('articles', $articles);
        }

    }

    public function deleteNewYorkTimesArticles(Request $request)
    {
        try {
            NewsArticles::where('news_category', 'NewYorkTimes')->delete();

            $message = 'Successfully deleted news articles';

            if ($request->ajax()) {
                return response()->json(['response' => 'success', 'message' => $message], 200);
            } else {
                return redirect()->back()->with('success', $message);
            }
        } catch (\Exception $e) {
            Log::error("Failed to delete New York Times articles", ['error' => $e->getMessage()]);

            $message = 'Failed to delete articles: ' . $e->getMessage();

            if ($request->ajax()) {
                return response()->json(['response' => 'error', 'message' => $message], 500);
            } else {
                return redirect()->back()->with('error', $message);
            }
        }
    }
}
