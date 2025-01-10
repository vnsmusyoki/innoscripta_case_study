<?php

namespace App\Http\Controllers;

use App\Models\NewsArticles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AllApisController extends Controller
{
    public function fetchAllArticles(Request $request)
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

        Log::info("Retrieving All Articles...");

        $articles = $articlesQuery->skip(($page - 1) * $pageSize)->take($pageSize)->get();

        return response()->json(['articles' => $articles]);
    }
}
