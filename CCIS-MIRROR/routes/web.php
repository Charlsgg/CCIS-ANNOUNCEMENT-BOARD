<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;

// --- Public Routes ---
Route::get('/', function () {
    return view('welcome');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// --- Protected Routes (Require Login) ---
Route::middleware('auth')->group(function () {

    Route::middleware('check_type:it_instructor')->prefix('it-dept')->name('it.')->group(function () {
        Route::get('/home', [AnnouncementController::class, 'index'])->name('home');
        
        // Announcements (Owner-based)
        Route::resource('announcements', AnnouncementController::class)->except(['index']);
        // Events
        Route::resource('events', EventController::class);
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/announcements-board', function () {
        return view('announcement-board'); // This must match the filename exactly
    })->name('announcements.index');

    Route::get('/events-calendar', function () {
        return view('events-calendar'); 
    })->name('events.index');

    Route::prefix('api')->group(function () {

        Route::get('/announcements', [AnnouncementController::class, 'index']);
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::put('/announcements/{id}', [AnnouncementController::class, 'update']);
        Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);

        // Route::get('/events', [EventController::class, 'index']);   
        // Route::post('/events', [EventController::class, 'store']);     
        // Route::put('/events/{id}', [EventController::class, 'update']);
        // Route::delete('/events/{id}', [EventController::class, 'destroy']); 
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
}

);