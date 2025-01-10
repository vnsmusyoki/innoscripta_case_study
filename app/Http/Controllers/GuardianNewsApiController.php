<?php

namespace App\Http\Controllers;

use App\Models\NewsArticles;
use App\Services\GuardianNewsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class GuardianNewsApiController extends Controller
{
    protected $guardianNewsService;
    public function __construct(GuardianNewsService $guardianNewsService)
    {
        $this->guardianNewsService = $guardianNewsService;
    }
    public function index(Request $request)
    {
        return view('guardian-api.index');
    }

  
    public function getAllArticles(Request $request)
    {
        $page = $request->get('page', 1);
        $pageSize = $request->get('pageSize', 30);
        $query = $request->get('searchQuery', '');

        $articlesQuery = NewsArticles::where('news_category', 'GuardianAPiNews');
        if ($request->has('searchQuery') && !empty($request->searchQuery)) {
            $searchKey = $request->searchQuery;
            $articlesQuery->where(function ($q) use ($searchKey) {
                $q->where('title', 'LIKE', "%{$searchKey}%")
                    ->orWhere('content', 'LIKE', "%{$searchKey}%")
                    ->orWhere('description', 'LIKE', "%{$searchKey}%");
            });
        }

        Log::info("Retrieving GuardianAPiNews Articles...");

        $articles = $articlesQuery->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        return response()->json(['articles' => $articles]);
    }

    public function updateDatabaseRecords(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'fetch_records' => 'required|integer|max:500',
            'randomize' => 'nullable',
            'sort_by' => 'nullable',
        ], [
            'fetch_records.required' => 'Please provide the number of records to fetch',
            'fetch_records.max' => 'You can fetch a maximum of 500 records at a time',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'errors' => $validatedData->errors()
            ], 422);
        }

        try {

            $query = $request->input('query', '');
            $orderBy = $request->input('order_by', 'newest');
            $randomize = $request->input('randomize', false);
            $pageSize = $request->input('fetch_records', 30);

            $articles = $this->guardianNewsService->getArticles($query, 1, $pageSize);


            if (isset($articles['error'])) {
                return response()->json(['error' => $articles['error']], 400);
            }

            if (empty($articles['response']['results'])) {
                return response()->json(['error' => 'No articles found.'], 404);
            }
            Log::info("this is here", ['articles' => $articles['response']['results']]);


            foreach ($articles['response']['results'] as $article) {
                NewsArticles::create([
                    'news_category' => 'GuardianAPiNews',
                    'source_name' => $article['sectionId'] ?? null,
                    'author' => $article['sectionName'] ?? null,
                    'title' => $article['webTitle'],
                    'description' => $article['fields']['headline'],
                    'url' => $article['webUrl'],
                    'url_to_image' => $article['fields']['thumbnail'] ?? null,
                    'content' => $article['fields']['body'],
                    'published_at' => Carbon::parse($article['webPublicationDate']),
                    'sort_by' => $article['sectionName']
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Articles successfully fetched and saved.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function deleteGuardianApiRecords(Request $request)
    {
        try {
            NewsArticles::where('news_category', 'GuardianAPiNews')->delete();

            $message = 'Successfully deleted news articles';

            if ($request->ajax()) {
                return response()->json(['response' => 'success', 'message' => $message], 200);
            } else {
                return redirect()->back()->with('success', $message);
            }
        } catch (\Exception $e) {
            Log::error("Failed to delete Guardian Api  articles", ['error' => $e->getMessage()]);

            $message = 'Failed to delete articles: ' . $e->getMessage();

            if ($request->ajax()) {
                return response()->json(['response' => 'error', 'message' => $message], 500);
            } else {
                return redirect()->back()->with('error', $message);
            }
        }
    }
}
