
<?php

use App\Http\Controllers\URLController;
use Illuminate\Support\Facades\Route;

// Main route - Upload form on welcome screen
Route::get('/', [URLController::class, 'create'])->name('urls.upload');
Route::post('/', [URLController::class, 'store'])->name('urls.store');

// View URLs route - Display URLs and their shortened versions
Route::get('/view-urls', [URLController::class, 'index'])->name('urls.view');

// Analytics route - View all shortened URLs with analytics data
Route::get('/analytics', [URLController::class, 'analytics'])->name('urls.analytics');

// Short URL redirect route - This handles the actual shortened URL clicks
Route::get('/{shortUrl}', [URLController::class, 'redirect'])->name('urls.redirect');
