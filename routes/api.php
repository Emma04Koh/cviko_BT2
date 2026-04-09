<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// zvyšné endpointy zostanú zatiaľ bez zmeny...
Route::apiResource('notes', NoteController::class);

// vy ich tam máte viac... nemažte si ich...
Route::middleware('auth:sanctum')->group(function () {
    // všetci prihlásení môžu čítať kategórie
    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

    // iba admin môže vytvárať, upravovať, mazať kategórie
    Route::middleware('admin')->group(function () {
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
    });
});


Route::middleware('auth:sanctum')->post('/logout-all', [AuthController::class, 'logoutAll']);

Route::middleware('auth:sanctum')->post('/change-password', [AuthController::class, 'changePassword']);

Route::middleware('auth:sanctum')->post('/profile', [AuthController::class, 'updateProfile']);
