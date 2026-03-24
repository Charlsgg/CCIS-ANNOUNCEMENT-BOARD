<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Event;
use App\Models\Announcement;

/**
 * Stage 1: The 30-Day Auto-Soft-Delete
 * Logic: Move to trash exactly 30 days after creation.
 */
Schedule::call(function () {
    $cutoff = now()->subDays(30);

    // Soft delete Announcements created 30+ days ago
    Announcement::where('created_at', '<', $cutoff)
        ->whereNull('deleted_at')
        ->delete();

    // Soft delete Events created 30+ days ago
    Event::where('created_at', '<', $cutoff)
        ->whereNull('deleted_at')
        ->delete();
})
->everySecond()
->name('soft-delete-by-age');

/**
 * Stage 2: The Model Pruner
 * Logic: Permanently delete records that hit the 60-day mark.
 */
Schedule::command('model:prune')->everySecond();