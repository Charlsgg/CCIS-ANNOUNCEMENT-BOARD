<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;

// ---------------------------------------------------------
// Public / Guest Routes
// ---------------------------------------------------------
// Change the '/' to '/login'
Route::get('/login', function () {
    return view('welcome'); // This loads your Vue form
})->name('login');

// Keep your POST route exactly the same
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Renamed from 'announcements.index' to avoid a naming conflict
Route::get('/announcements-board', function () {
    return view('announcement-board');
})->name('announcements.board');

Route::middleware('auth')->group(function () {

    Route::middleware('check_type:it_instructor')->prefix('it')->name('it.')->group(function () {
        Route::get('/dashboard', function() {
            return view('dashboard'); 
        })->name('dashboard');
        Route::get('/home', function() {
            return view('home'); 
        })->name('home');
        Route::get('/announcement-page', function() {
            return view('announcement-page'); 
        })->name('announcement.page');
        Route::get('/events', function() {
            return view('events-calendar'); 
        })->name('events.index');
        
        Route::resource('announcements', AnnouncementController::class)->except(['index']);
        Route::resource('events', EventController::class);
    });

    Route::middleware('check_type:cs_instructor')->prefix('cs')->name('cs.')->group(function () {
        Route::get('/dashboard', function() {
            return view('dashboard'); 
        })->name('dashboard');
         Route::get('/home', function() {
            return view('home'); 
        })->name('home');
        Route::get('/announcement-page', function() {
            return view('announcement-page'); 
        })->name('announcement.page');
        Route::get('/events', function() {
            return view('events-calendar'); 
        })->name('events.index');
        
        Route::resource('announcements', AnnouncementController::class)->except(['index']);
        Route::resource('events', EventController::class);
    });

    Route::middleware('check_type:is_instructor')->prefix('is')->name('is.')->group(function () {
        Route::get('/dashboard', function() {
            return view('dashboard'); 
        })->name('dashboard');
         Route::get('/home', function() {
            return view('home'); 
        })->name('home');
        Route::get('/announcement-page', function() {
            return view('announcement-page'); 
        })->name('announcement.page');
        Route::get('/events', function() {
            return view('events-calendar'); 
        })->name('events.index');
        
        Route::resource('announcements', AnnouncementController::class)->except(['index']);
        Route::resource('events', EventController::class);
    });

    Route::middleware('check_type:lsg_officer')->prefix('lsg')->name('lsg.')->group(function () {

        Route::get('/dashboard', function() {
            return view('dashboard'); 
        })->name('dashboard');
         Route::get('/home', function() {
            return view('home'); 
        })->name('home');
        Route::get('/announcement-page', function() {
            return view('announcement-page'); 
        })->name('announcement.page');
        Route::get('/events', function() {
            return view('events-calendar'); 
        })->name('events.index');
        
        Route::resource('announcements', AnnouncementController::class)->except(['index']);
        Route::resource('events', EventController::class);
    });


  
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/announcements-page', function () {
        return view('announcements-page'); 
    })->name('announcements.page'); 

    Route::get('/events', function () {
        return view('events-calendar'); 
    })->name('events.index');

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

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

});