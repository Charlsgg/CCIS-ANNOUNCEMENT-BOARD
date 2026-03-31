<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserAnnouncementController;
use App\Http\Controllers\AnnouncementBoardController;
use App\Http\Controllers\NavbarController;

Route::get('/', function () {
    return redirect()->route('login');
});
Route::get('/login', function () {
    return view('login'); 
})->name('login');

Route::get('/signup', function () {
    return view('signup'); 
})->name('signup');

Route::post('/register', [AuthController::class, 'register']);

Route::get('/forgot-password', function () {
    return view('forgot-password'); 
})->name('password.request');

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::get('/reset-password/{token}', function (string $token) {
    return view('reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/announcements-board', function () {
    return view('announcement-board');
})->name('announcements.board');

Route::get('/announcements-events', function () {
    return view('announcements-events');
})->name('announcements.events');


Route::prefix('api')->group(function () {
    Route::get('/board-data', [AnnouncementBoardController::class, 'index']);
    Route::post('/announcements/{id}/like', [AnnouncementBoardController::class, 'like']); 
    Route::get('/events/upcoming', [EventController::class, 'upcoming']);
    
    Route::get('/events', [EventController::class, 'index']); 
});



Route::middleware('auth')->group(function () {

    // IT Instructor Routes
    Route::middleware('check_type:it_instructor')->prefix('it')->name('it.')->group(function () {
         Route::get('/home-page', function() { return view('home-page'); })->name('home.page');
        Route::get('/announcement-page', function () { return view('announcement-page'); })->name('announcement.page'); 
        Route::get('/events-page', function () { return view('events-page'); })->name('events.page');
        Route::get('/profile-page', function() { return view('profile-page'); })->name('profile.page');
        
        Route::resource('announcements', AnnouncementController::class)->except(['index']);
        Route::resource('events', EventController::class);
    });

    // CS Instructor Routes
    Route::middleware('check_type:cs_instructor')->prefix('cs')->name('cs.')->group(function () {
         Route::get('/home-page', function() { return view('home-page'); })->name('home.page');
        Route::get('/announcement-page', function () { return view('announcement-page'); })->name('announcement.page'); 
        Route::get('/events-page', function () { return view('events-page'); })->name('events.page');
        Route::get('/profile-page', function() { return view('profile-page'); })->name('profile.page');
        
        Route::resource('announcements', AnnouncementController::class)->except(['index']);
        Route::resource('events', EventController::class);
    });

    // IS Instructor Routes
    Route::middleware('check_type:is_instructor')->prefix('is')->name('is.')->group(function () {
         Route::get('/home-page', function() { return view('home-page'); })->name('home.page');
        Route::get('/announcement-page', function () { return view('announcement-page'); })->name('announcement.page'); 
        Route::get('/events-page', function () { return view('events-page'); })->name('events.page');
        Route::get('/profile-page', function() { return view('profile-page'); })->name('profile.page');
        
        Route::resource('announcements', AnnouncementController::class)->except(['index']);
        Route::resource('events', EventController::class);
    });

    // LSG Officer Routes
    Route::middleware('check_type:lsg_officer')->prefix('lsg')->name('lsg.')->group(function () {
        Route::get('/home-page', function() { return view('home-page'); })->name('home.page');
        Route::get('/announcement-page', function () { return view('announcement-page'); })->name('announcement.page'); 
        Route::get('/events-page', function () { return view('events-page'); })->name('events.page');
        Route::get('/profile-page', function() { return view('profile-page'); })->name('profile.page');

        Route::resource('announcements', AnnouncementController::class)->except(['index']);
        Route::resource('events', EventController::class);
    });

    Route::get('/events', function () {
        return view('events-calendar'); 
    })->name('events.index');
    
    
    // PROTECTED API ROUTES
    Route::prefix('api')->group(function () {
        Route::post('/my-announcements/{id}', [UserAnnouncementController::class, 'update']);
    
    Route::delete('/my-announcements/{id}', [UserAnnouncementController::class, 'destroy']);
    Route::get('/my-announcements', [UserAnnouncementController::class, 'index']);
        Route::get('/announcements', [AnnouncementController::class, 'index']);
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::put('/announcements/{id}', [AnnouncementController::class, 'update']);
        Route::delete('/announcements/{id}', [AnnouncementController::class, 'destroy']); 
        Route::post('/events', [EventController::class, 'store']);     
        Route::put('/events/{id}', [EventController::class, 'update']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']); 

        Route::get('/profile', [UserProfileController::class, 'show']);
        Route::post('/profile', [UserProfileController::class, 'update']);
        Route::put('/profile/password', [UserProfileController::class, 'updatePassword']);
        Route::get('/navbar/user', [NavbarController::class, 'getUserData']);
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

});