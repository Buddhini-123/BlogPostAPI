<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PostController;

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

Route::post('login', [LoginController::class, 'login'])->name('login');
Route::post('register', [RegisterController::class, 'register']);

Route::middleware('auth:sanctum')->group(function() {
    /*
    Blog Post CRUD
    */
    Route::get('posts', [PostController::class, 'index']);
    Route::post('posts', [PostController::class, 'store']); 
    Route::get('posts/{id}', [PostController::class, 'edit']); 
    Route::put('posts/edit/{id}', [PostController::class, 'update']); 
});


