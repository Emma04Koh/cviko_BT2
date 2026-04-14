<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Controller;
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

    // komentáre k note
    Route::get('/notes/{note}/comments', [CommentController::class, 'indexForNote']);
    Route::post('/notes/{note}/comments', [CommentController::class, 'storeForNote']);

    // komentáre k tasku
    Route::get('/tasks/{task}/comments', [CommentController::class, 'indexForTask']);
    Route::post('/tasks/{task}/comments', [CommentController::class, 'storeForTask']);

    // update + delete (spoločné)
    Route::patch('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);

    // TASKS
    Route::apiResource('notes.tasks', TaskController::class)->scoped();
});

Route::middleware(['auth:sanctum', 'premium'])->group(function () {
    Route::get('/notes/{note}/attachments', [AttachmentController::class, 'index']);
    Route::post('/notes/{note}/attachments', [AttachmentController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/attachments/{attachment}/link', [AttachmentController::class, 'link']);
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);
});
