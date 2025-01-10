<?php

use App\Http\Controllers\NewsApiController;
use App\Http\Controllers\NewYorkTimesApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/news-api', [NewsApiController::class, 'index'])->name('newsApi');
Route::get('/preload-articles', [NewsApiController::class, 'preloadArticles'])->name('newsAPI.preloadArticles');
Route::post('/update-database-records', [NewsApiController::class, 'updateDatabaseRecords'])->name('newsApi.updateDatabaseRecords');
Route::get('/delete-database-records', [NewsApiController::class, 'cleanDatabase'])->name('newsApi.cleanDatabase');

Route::get('/new-york-api', [NewYorkTimesApiController::class, 'index'])->name('newYorkApi');
Route::get('/preload-new-york-api-articles', [NewYorkTimesApiController::class, 'preloadArticles'])->name('newYorkApi.preloadArticles');
Route::get('/fetch-latest-new-york-api-articles', [NewYorkTimesApiController::class, 'fetchLatestArticles'])->name('newYorkApi.fetchLatestArticles');
Route::get('/delete-new-york-api-articles', [NewYorkTimesApiController::class, 'deleteNewYorkTimesArticles'])->name('newsApi.deleteNeyYorkTimesArticles');
//
