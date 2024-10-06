<?php

use App\Http\Controllers\SewaController;
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

Route::prefix('sewa')->group(function () {
    Route::get('/', [SewaController::class, 'index']);
    Route::post('/create', [SewaController::class, 'store']);
    Route::post('/edit/{id}', [SewaController::class, 'update']);
    Route::get('/delete/{id}', [SewaController::class, 'destroy']);
});
