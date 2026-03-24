<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\Announcement;

class CleanupOldRecords extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:cleanup-old-records';

    /**
     * The console command description.
     */
    protected $description = 'Permanently removes records that have been in the trash for more than 30 days (60 days total age).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Logic: These were soft-deleted when they hit 30 days old.
        // We wait another 30 days in the trash before wiping them (60 days total).
        $cutoff = now()->subDays(30);

        // Permanently delete soft-deleted events
        $eventCount = Event::onlyTrashed()
            ->where('deleted_at', '<', $cutoff)
            ->forceDelete();

        // Permanently delete soft-deleted announcements
        $announcementCount = Announcement::onlyTrashed()
            ->where('deleted_at', '<', $cutoff)
            ->forceDelete();

        $this->info("Successfully hard-deleted $eventCount events and $announcementCount announcements from the trash.");
    }
}