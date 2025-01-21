<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TranslationController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('register', [AuthController::class, 'createUser']);

Route::middleware(['auth:sanctum', 'apikey'])->group(function () {
    Route::post('translations', [TranslationController::class, 'store']); // Create
    Route::put('translations/{id}', [TranslationController::class, 'update']); // Update
    Route::get('translations/search', [TranslationController::class, 'search']); // Search
    Route::get('translations/export', [TranslationController::class, 'export']); // JSON export
    Route::get('translations/{id}', [TranslationController::class, 'show']); // View
});
