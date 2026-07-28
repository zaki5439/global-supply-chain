<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\WatchlistComparisonController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout']); // Fallback for GET requests

// Protected User Routes (Requires Login)
Route::middleware('auth')->group(function () {
    Route::get('/', function () { return view('dashboard'); });
    Route::get('/dashboard', function () { return view('dashboard'); });
    Route::get('/country', function () { return view('country'); });
    Route::redirect('/compare', '/country');
    Route::get('/news', function () { return view('news'); });
    Route::get('/currency', function () { return view('currency'); });
    Route::get('/port', function () { return view('port'); });
    Route::get('/watchlist', function () { return view('watchlist'); });
    Route::get('/comparison', function () { return view('comparison'); });

    // Watchlist routes (auth-protected, uses session)
    Route::get('/watchlists', [WatchlistComparisonController::class, 'index']);
    Route::post('/watchlists', [WatchlistComparisonController::class, 'store']);
    Route::delete('/watchlists/{id}', [WatchlistComparisonController::class, 'destroy']);

    // Serve ports data JSON file
    Route::get('/ports-complete.json', function () {
        $jsonPath = resource_path('views/ports-complete.json');
        if (file_exists($jsonPath)) {
            return response()->file($jsonPath, [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'public, max-age=3600'
            ]);
        }
        return response()->json(['error' => 'Ports data file not found'], 404);
    });

    // Admin Protected Routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        
        // Admin Controller routes
        Route::get('/', [AdminController::class, 'index']);
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::post('/users/{id}/role', [AdminController::class, 'updateRole']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);

        Route::resource('ports', \App\Http\Controllers\Admin\PortController::class);
        Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);

        // Admin mapping for user features
        Route::get('/dashboard-view', function () { return view('dashboard', ['isAdminLayout' => true]); });
        Route::get('/country', function () { return view('country', ['isAdminLayout' => true]); });
        Route::get('/port', function () { return view('port', ['isAdminLayout' => true]); });
        Route::get('/news', function () { return view('news', ['isAdminLayout' => true]); });
        Route::get('/currency', function () { return view('currency', ['isAdminLayout' => true]); });
    });
});

// Testing / Debugging Routes (Unprotected)
Route::get('/test-countries', function () { return view('test-countries'); });
Route::get('/simple-test', function () { return view('simple-test'); });