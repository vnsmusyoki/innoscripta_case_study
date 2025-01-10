<?php

use App\Http\Controllers\AllApisController;
use App\Http\Controllers\GuardianNewsApiController;
use App\Http\Controllers\NewsApiController;
use App\Http\Controllers\NewYorkTimesApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
// APIS FOR ALL THE FUNCTIONALITIES 
Route::get('/news-api', [NewsApiController::class, 'index']);
Route::get('/preload-articles', [NewsApiController::class, 'preloadArticles']);
Route::post('/update-database-records', [NewsApiController::class, 'updateDatabaseRecords']);
Route::get('/delete-database-records', [NewsApiController::class, 'cleanDatabase']);

Route::get('/new-york-api', [NewYorkTimesApiController::class, 'index']);
Route::get('/preload-new-york-api-articles', [NewYorkTimesApiController::class, 'preloadArticles']);
Route::get('/fetch-latest-new-york-api-articles', [NewYorkTimesApiController::class, 'fetchLatestArticles']);
Route::get('/delete-new-york-api-articles', [NewYorkTimesApiController::class, 'deleteNewYorkTimesArticles']);

Route::get('/guardian-news-api', [GuardianNewsApiController::class, 'index']);
Route::get('/guardian-news-api/fetch-data', [GuardianNewsApiController::class, 'getAllArticles']);
Route::post('/guardian-news-api/fetch-api-data', [GuardianNewsApiController::class, 'updateDatabaseRecords']);
Route::post('/guardian-news-api/delete-api-data', [GuardianNewsApiController::class, 'deleteGuardianApiRecords']);

Route::get('/fetch-all-articles', [AllApisController::class, 'fetchAllArticles']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
