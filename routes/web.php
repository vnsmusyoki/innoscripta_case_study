<?php

use App\Http\Controllers\NewsApiController;
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
//
