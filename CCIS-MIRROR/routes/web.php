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

Route::middleware(['auth', 'check_type:it_instructor'])->prefix('it')->name('it.')->group(function () {
    Route::get('/home', [AnnouncementController::class, 'index'])->name('home');
    Route::resource('announcements', AnnouncementController::class)->except(['index']);
});
Route::middleware(['auth', 'check_type:cs_instructor'])->prefix('cs')->name('cs.')->group(function () {
    Route::get('/home', [AnnouncementController::class, 'index'])->name('home');
    route::resource('announcements', AnnouncementController::class)->except(['index']);
    route::resource('events', EventController::class);
});
Route::middleware(['auth', 'check_type:is_instructor'])->prefix('is')->name('is.')->group(function () {
    Route::get('/home', [AnnouncementController::class, 'index'])->name('home');
    route::resource('announcements', AnnouncementController::class)->except(['index']);
    route::resource('events', EventController::class);
});
Route::middleware(['auth', 'check_type:lsg_officer'])->prefix('lsg')->name('lsg.')->group(function () {
    Route::get('/dashboard', [AnnouncementController::class, 'index'])->name('home');
    Route::resource('events', EventController::class);
});


Route::middleware('auth')->group(function () {

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
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
}

);