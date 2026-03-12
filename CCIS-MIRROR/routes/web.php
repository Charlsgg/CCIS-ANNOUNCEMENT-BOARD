<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;

// ---------------------------------------------------------
// Public / Guest Routes
// ---------------------------------------------------------
Route::get('/', function () {
    return view('welcome');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Renamed from 'announcements.index' to avoid a naming conflict
Route::get('/announcements-board', function () {
    return view('announcement-board');
})->name('announcements.board');

// ---------------------------------------------------------
// Authenticated Routes
// ---------------------------------------------------------
Route::middleware('auth')->group(function () {

    // --- Role: IT Instructor ---
    Route::middleware('check_type:it_instructor')->prefix('it')->name('it.')->group(function () {
        // Changed name from 'home' to 'dashboard' to match the URL and other roles
        Route::get('/dashboard', [AnnouncementController::class, 'index'])->name('dashboard');
        Route::resource('announcements', AnnouncementController::class)->except(['index']);
    });

    // --- Role: CS Instructor ---
    Route::middleware('check_type:cs_instructor')->prefix('cs')->name('cs.')->group(function () {
        Route::get('/dashboard', [AnnouncementController::class, 'index'])->name('dashboard');
        Route::resource('announcements', AnnouncementController::class)->except(['index']); // Fixed capitalization
        Route::resource('events', EventController::class); // Fixed capitalization
    });

    // --- Role: IS Instructor ---
    Route::middleware('check_type:is_instructor')->prefix('is')->name('is.')->group(function () {
        Route::get('/dashboard', [AnnouncementController::class, 'index'])->name('dashboard');
        Route::resource('announcements', AnnouncementController::class)->except(['index']); // Fixed capitalization
        Route::resource('events', EventController::class); // Fixed capitalization
    });

    // --- Role: LSG Officer ---
    Route::middleware('check_type:lsg_officer')->prefix('lsg')->name('lsg.')->group(function () {
        Route::get('/dashboard', [AnnouncementController::class, 'index'])->name('dashboard');
        Route::resource('events', EventController::class);
    });

    // --- Generic Authenticated Pages ---
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Renamed from 'announcements.index' to avoid a naming conflict
    Route::get('/announcements-page', function () {
        return view('announcements-page'); 
    })->name('announcements.page'); 

    Route::get('/events', function () {
        return view('events-calendar'); 
    })->name('events.index');

    // --- Internal API Endpoints (For AJAX/Axios) ---
    Route::prefix('api')->group(function () {
        Route::get('/announcements', [AnnouncementController::class, 'index']);
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::put('/announcements/{id}', [AnnouncementController::class, 'update']);
        Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']);

        Route::get('/events', [EventController::class, 'index']);   
        Route::post('/events', [EventController::class, 'store']);     
        Route::put('/events/{id}', [EventController::class, 'update']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']); 
    });

    // --- Logout ---
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

});