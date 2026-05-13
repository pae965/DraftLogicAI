<?php

use App\Http\Controllers\Api\AIController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CitationController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\SectionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — RUS Research CMS
|--------------------------------------------------------------------------
| All routes require Sanctum authentication
*/

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/me', fn (\Illuminate\Http\Request $r) => response()->json(['data' => $r->user()]));

    // ===== Articles =====
    Route::apiResource('articles', ArticleController::class);
    Route::get('articles-templates', [ArticleController::class, 'availableTemplates']);

    // ===== Sections =====
    Route::get('articles/{article}/sections', [SectionController::class, 'index']);
    Route::patch('articles/{article}/sections/{section}', [SectionController::class, 'update']);
    Route::delete('articles/{article}/sections/{section}', [SectionController::class, 'destroy']);
    Route::post('articles/{article}/sections/insert', [SectionController::class, 'insert']);
    Route::post('articles/{article}/sections/reorder', [SectionController::class, 'reorder']);

    // ===== Citations =====
    Route::get('articles/{article}/citations', [CitationController::class, 'index']);
    Route::post('articles/{article}/citations/manual', [CitationController::class, 'storeManual']);
    Route::post('articles/{article}/citations/lookup', [CitationController::class, 'storeLookup']);
    Route::post('articles/{article}/citations/reformat', [CitationController::class, 'storeReformat']);
    Route::patch('articles/{article}/citations/{citation}', [CitationController::class, 'update']);
    Route::delete('articles/{article}/citations/{citation}', [CitationController::class, 'destroy']);

    // ===== Export =====
    Route::get('articles/{article}/export/word', [ExportController::class, 'word']);
    Route::get('articles/{article}/export/pdf', [ExportController::class, 'pdf']);
    Route::get('articles/{article}/export/validate', [ExportController::class, 'validate']);

    // ===== AI =====
    Route::get('ai/settings', [AIController::class, 'settings']);
    Route::post('ai/settings', [AIController::class, 'saveSetting']);
    Route::post('ai/generate-text', [AIController::class, 'generateText']);
    Route::post('articles/{article}/abstracts', [AIController::class, 'setAbstract']);
    Route::post('articles/{article}/abstracts/translate', [AIController::class, 'translateAbstract']);
    Route::post('articles/{article}/abstracts/approve', [AIController::class, 'approveAbstract']);
});
