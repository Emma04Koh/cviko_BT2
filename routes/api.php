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


Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/me/profile-photo', [AuthController::class, 'storeProfilePhoto']);

    // notes
    Route::get('/notes', [NoteController::class, 'index']);      // nástenka - všetky poznámky
    Route::get('/my-notes', [NoteController::class, 'myNotes']); // len moje poznámky (aj drafty)
    Route::post('/notes', [NoteController::class, 'store']);
    Route::get('/notes/{note}', [NoteController::class, 'show']);
    Route::patch('/notes/{note}', [NoteController::class, 'update']);
    Route::delete('/notes/{note}', [NoteController::class, 'destroy']);

    // vy máte možno iné routy, nekopírujte naslepo...
    Route::patch('/notes/{note}/publish', [NoteController::class, 'publish']);
    Route::patch('/notes/{note}/archive', [NoteController::class, 'archive']);
    Route::patch('/notes/{note}/pin', [NoteController::class, 'pin']);
    Route::patch('/notes/{note}/unpin', [NoteController::class, 'unpin']);

    // tasks
    Route::apiResource('notes.tasks', TaskController::class)->scoped();
});
