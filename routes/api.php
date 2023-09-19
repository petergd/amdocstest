<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\ProductController;

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

Route::get('/tags', [TagController::class, 'index']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/category/{cname}', [ProductController::class, 'get']);

Route::post('/products/add', [ProductController::class, 'add']);
Route::post('/products/update', [ProductController::class, 'update']);
