<?php

use App\Http\Controllers\URLController;
use Illuminate\Support\Facades\Route;

// URL Shortener API - Version 1 (Public endpoints, no authentication required)
Route::prefix('v1')->group(function () {
    Route::prefix('urls')->group(function () {
        //CSV Upload
        Route::post('/upload', [URLController::class, 'store']);              // POST /api/v1/urls/upload - Upload CSV file

        // URL Operations
        Route::get('/', [URLController::class, 'index']);                     // GET /api/v1/urls - List URLs

        // Analytics Operations (move these BEFORE the dynamic {shortUrl} route)
        Route::get('/analytics', [URLController::class, 'analytics']);        // GET /api/v1/urls/analytics - Get all analytics in batches
        Route::get('/{shortUrl}/analytics', [URLController::class, 'urlAnalytics']); // GET /api/v1/urls/my.short.url.com/analytics - Get analytics for a specific short URL

        // Dynamic route MUST come last
        Route::get('/{shortUrl}', [URLController::class, 'show']);           // GET /api/v1/urls/abc123 - Get url data on a single shortened URL
    });
});
