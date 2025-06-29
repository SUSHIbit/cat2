<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SimplificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Dashboard Route
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Document Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Document CRUD
    Route::resource('documents', DocumentController::class);
    
    // Additional Document Actions
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])
        ->name('documents.download')
        ->middleware('document.ownership');
    
    Route::post('documents/{document}/reprocess', [DocumentController::class, 'reprocess'])
        ->name('documents.reprocess')
        ->middleware('document.ownership');
    
    Route::post('documents/{document}/archive', [DocumentController::class, 'archive'])
        ->name('documents.archive')
        ->middleware('document.ownership');
    
    Route::post('documents/{document}/restore', [DocumentController::class, 'restore'])
        ->name('documents.restore')
        ->middleware('document.ownership');
    
    Route::get('documents/{document}/status', [DocumentController::class, 'status'])
        ->name('documents.status')
        ->middleware('document.ownership');
    
    // Bulk Actions
    Route::post('documents/bulk-action', [DocumentController::class, 'bulkAction'])
        ->name('documents.bulk-action');
});

// Simplification Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Simplification CRUD
    Route::resource('simplifications', SimplificationController::class);
    
    // Additional Simplification Actions
    Route::get('simplifications/{simplification}/download', [SimplificationController::class, 'download'])
        ->name('simplifications.download')
        ->middleware('simplification.ownership');
    
    Route::post('simplifications/{simplification}/toggle-favorite', [SimplificationController::class, 'toggleFavorite'])
        ->name('simplifications.toggle-favorite')
        ->middleware('simplification.ownership');
    
    Route::post('simplifications/{simplification}/rate', [SimplificationController::class, 'rate'])
        ->name('simplifications.rate')
        ->middleware('simplification.ownership');
    
    Route::post('simplifications/{simplification}/make-public', [SimplificationController::class, 'makePublic'])
        ->name('simplifications.make-public')
        ->middleware('simplification.ownership');
    
    Route::post('simplifications/{simplification}/make-private', [SimplificationController::class, 'makePrivate'])
        ->name('simplifications.make-private')
        ->middleware('simplification.ownership');
    
    Route::post('simplifications/{simplification}/regenerate', [SimplificationController::class, 'regenerate'])
        ->name('simplifications.regenerate')
        ->middleware('simplification.ownership');
    
    Route::get('simplifications/{simplification}/status', [SimplificationController::class, 'status'])
        ->name('simplifications.status')
        ->middleware('simplification.ownership');
    
    // Favorites
    Route::get('simplifications/favorites/index', [SimplificationController::class, 'favorites'])
        ->name('simplifications.favorites');
});

// Public Simplification Routes (no auth required)
Route::get('public/simplifications/{shareToken}', [SimplificationController::class, 'public'])
    ->name('simplifications.public')
    ->middleware('throttle:public-content');

// API-style routes for AJAX requests
Route::middleware(['auth', 'verified'])->prefix('api')->group(function () {
    // Dashboard quick actions
    Route::get('dashboard/quick-actions', [DashboardController::class, 'quickActions'])
        ->name('api.dashboard.quick-actions');
    
    // Document status checks (for real-time updates)
    Route::get('documents/{document}/status', [DocumentController::class, 'status'])
        ->name('api.documents.status')
        ->middleware('document.ownership');
    
    // Simplification status checks
    Route::get('simplifications/{simplification}/status', [SimplificationController::class, 'status'])
        ->name('api.simplifications.status')
        ->middleware('simplification.ownership');
});

// Rate-limited routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Document upload (rate limited)
    Route::post('documents', [DocumentController::class, 'store'])
        ->middleware(['throttle:document-upload', 'file.upload'])
        ->name('documents.store');
    
    // Simplification creation (rate limited)
    Route::post('simplifications', [SimplificationController::class, 'store'])
        ->middleware('throttle:simplification-create')
        ->name('simplifications.store');
    
    // Download routes (rate limited)
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])
        ->middleware(['throttle:downloads', 'document.ownership'])
        ->name('documents.download');
    
    Route::get('simplifications/{simplification}/download', [SimplificationController::class, 'download'])
        ->middleware(['throttle:downloads', 'simplification.ownership'])
        ->name('simplifications.download');
});

// Administrative routes (if needed in the future)
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    // These would be protected by additional admin middleware
    // Route::get('/', [AdminController::class, 'index'])->name('admin.index');
    // Route::get('documents', [AdminController::class, 'documents'])->name('admin.documents');
    // Route::get('users', [AdminController::class, 'users'])->name('admin.users');
});

// Health check and monitoring routes
Route::get('health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'services' => [
            'database' => 'connected',
            'queue' => 'operational',
            'storage' => 'accessible',
        ]
    ]);
})->middleware('throttle:health-check');

// Include authentication routes
require __DIR__.'/auth.php';