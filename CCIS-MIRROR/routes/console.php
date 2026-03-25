<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Event;
use App\Models\Announcement;


Schedule::call(function () {
    $cutoff = now()->subDays(30);
    
    Announcement::where('created_at', '<', $cutoff)
        ->whereNull('deleted_at')
        ->delete();

    Event::where('created_at', '<', $cutoff)
        ->whereNull('deleted_at')
        ->delete();
})
->everySecond()
->name('soft-delete-by-age');

Schedule::command('model:prune')->everySecond();