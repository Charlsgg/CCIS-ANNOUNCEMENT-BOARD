<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Board;
use App\Models\Event;
use App\Models\Announcement;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users
        $users = [
            ['user_id' => 101, 'name' => 'Juan Dela Cruz', 'email' => 'admin1@gmail.com', 'user_type' => 'it_instructor', 'password' => bcrypt('password')],
            ['user_id' => 102, 'name' => 'Maria Clara', 'email' => 'admin2@gmail.com', 'user_type' => 'is_instructor', 'password' => bcrypt('password')],
            ['user_id' => 103, 'name' => 'Jose Rizal', 'email' => 'admin3@gmail.com', 'user_type' => 'cs_instructor', 'password' => bcrypt('password')],
            ['user_id' => 104, 'name' => 'Andres Bonifacio', 'email' => 'admin4@gmail.com', 'user_type' => 'lsg_officer', 'password' => bcrypt('password')],
            ['user_id' => 105, 'name' => 'Apolinario Mabini', 'email' => 'admin5@gmail.com', 'user_type' => 'it_instructor', 'password' => bcrypt('password')],
        ];
        foreach ($users as $u) User::create($u);

        // 2. Create Boards
        $boards = [
            ['board_id' => 1, 'board_name' => 'IT Department'],
            ['board_id' => 2, 'board_name' => 'CS Department'],
            ['board_id' => 3, 'board_name' => 'IS Department'],
            ['board_id' => 4, 'board_name' => 'General CCIS'],
            ['board_id' => 5, 'board_name' => 'LSG Announcements'],
        ];
        foreach ($boards as $b) Board::create($b);

        // 3. GENERATE 100 ANNOUNCEMENTS
        for ($i = 1; $i <= 100; $i++) {
            $daysOld = rand(0, 70); // Random age up to 70 days
            $id = 2000 + $i;
            
            if ($daysOld > 60) {
                // Stage 2: Ready to Prune
                $this->createPrunableAnnouncement($id, rand(1, 5), rand(101, 105), "Bulk Post $i", "Content for $i", "Topic", $daysOld, rand(31, 40));
            } elseif ($daysOld > 30) {
                // Stage 1: Ready to Soft Delete
                $this->createOldAnnouncement($id, rand(1, 5), rand(101, 105), "Bulk Post $i", "Content for $i", "Topic", $daysOld);
            } else {
                // Active
                Announcement::create([
                    'announcement_id' => $id,
                    'board_id' => rand(1, 5),
                    'author_id' => rand(101, 105),
                    'title' => "Bulk Post $i",
                    'content' => "Fresh content for post $i",
                    'topic' => 'General'
                ]);
            }
        }

        // 4. GENERATE 100 EVENTS
        for ($i = 1; $i <= 100; $i++) {
            $daysOld = rand(0, 70);
            $id = 3000 + $i;

            if ($daysOld > 60) {
                // Stage 2: Ready to Prune
                $this->createPrunableEvent($id, rand(101, 105), rand(1, 5), "Bulk Event $i", "Desc $i", "Venue $i", $daysOld, rand(31, 40));
            } elseif ($daysOld > 30) {
                // Stage 1: Ready to Soft Delete
                $this->createOldEvent($id, rand(101, 105), rand(1, 5), "Bulk Event $i", "Desc $i", "Venue $i", $daysOld);
            } else {
                // Active / Future
                Event::create([
                    'event_id' => $id,
                    'user_id' => rand(101, 105),
                    'board_id' => rand(1, 5),
                    'title' => "Bulk Event $i",
                    'content' => "Active event description $i",
                    'venue' => "Room ".rand(101, 404),
                    'start_time' => now()->addDays(rand(1, 15)),
                    'end_time' => now()->addDays(16),
                ]);
            }
        }
    }

    // --- HELPER METHODS ---

    private function createOldAnnouncement($id, $board, $author, $title, $content, $topic, $daysOld) {
        $a = new Announcement([
            'announcement_id' => $id, 'board_id' => $board, 'author_id' => $author,
            'title' => $title, 'content' => $content, 'topic' => $topic
        ]);
        $a->created_at = now()->subDays($daysOld);
        $a->save();
    }

    private function createOldEvent($id, $user, $board, $title, $content, $venue, $daysOld) {
        $e = new Event([
            'event_id' => $id, 'user_id' => $user, 'board_id' => $board,
            'title' => $title, 'content' => $content, 'venue' => $venue,
            'start_time' => now()->subDays($daysOld), 'end_time' => now()->subDays($daysOld)->addHours(2)
        ]);
        $e->created_at = now()->subDays($daysOld);
        $e->save();
    }

    private function createPrunableAnnouncement($id, $board, $author, $title, $content, $topic, $createdDays, $deletedDays) {
        $a = new Announcement([
            'announcement_id' => $id, 'board_id' => $board, 'author_id' => $author,
            'title' => $title, 'content' => $content, 'topic' => $topic
        ]);
        $a->created_at = now()->subDays($createdDays);
        $a->deleted_at = now()->subDays($deletedDays);
        $a->save();
    }

    private function createPrunableEvent($id, $user, $board, $title, $content, $venue, $createdDays, $deletedDays) {
        $e = new Event([
            'event_id' => $id, 'user_id' => $user, 'board_id' => $board,
            'title' => $title, 'content' => $content, 'venue' => $venue,
            'start_time' => now()->subDays($createdDays), 'end_time' => now()->subDays($createdDays)->addHours(2)
        ]);
        $e->created_at = now()->subDays($createdDays);
        $e->deleted_at = now()->subDays($deletedDays);
        $e->save();
    }
}