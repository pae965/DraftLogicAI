<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — RUS Research CMS
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    \App\Http\Middleware\SetLocale::class,
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Article editor (Phase 3)
    Route::get('/articles/{article}/edit', function (\App\Models\Article $article) {
        abort_if(! auth()->user()->can('update', $article), 403);
        return view('articles.editor', compact('article'));
    })->name('articles.edit');
});
