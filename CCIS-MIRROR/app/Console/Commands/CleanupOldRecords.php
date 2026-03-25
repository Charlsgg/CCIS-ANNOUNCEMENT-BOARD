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


    public function handle()
    {
        $cutoff = now()->subDays(30);

        $eventCount = Event::onlyTrashed()
            ->where('deleted_at', '<', $cutoff)
            ->forceDelete();

        $announcementCount = Announcement::onlyTrashed()
            ->where('deleted_at', '<', $cutoff)
            ->forceDelete();

        $this->info("Successfully hard-deleted $eventCount events and $announcementCount announcements from the trash.");
    }
}