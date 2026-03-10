<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

//Route::get('notes/stats/status', [NoteController::class, 'statsByStatus']);

//Route::patch('notes/actions/archive-old-drafts', [NoteController::class, 'archiveOldDrafts']);

//Route::get('users/{user}/notes', [NoteController::class, 'userNotesWithCategories']);

//Route::get('notes-actions/search', [NoteController::class, 'search']);
// vlastna metoda
//Route::get('notes/actions/filter', [NoteController::class, 'filterByCategory']);

//Route::apiResource('categories', CategoryController::class);

//Route::apiResource('notes', NoteController::class);

Route::prefix('notes')->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('notes', NoteController::class);

    Route::get('actions/filter', [NoteController::class, 'filterByCategory']);
});
