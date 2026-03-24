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

        // 3. Create Announcements
        
        // --- ACTIVE ---
        Announcement::create([
            'announcement_id' => 1001, 'board_id' => 1, 'author_id' => 101,
            'title' => 'Capstone Orientation', 'content' => 'Mandatory for 4th years.', 'topic' => 'Academic'
        ]);
        Announcement::create([
            'announcement_id' => 1004, 'board_id' => 4, 'author_id' => 104,
            'title' => 'No Classes Tomorrow', 'content' => 'Due to inclement weather.', 'topic' => 'Urgent'
        ]);

        // --- READY TO SOFT DELETE (30+ Days Old) ---
        $this->createOldAnnouncement(1002, 1, 101, 'Old Job Hiring', 'This is 35 days old.', 'Career', 35);
        $this->createOldAnnouncement(1005, 2, 103, 'Past Hackathon Results', 'Announcement from last month.', 'Contest', 45);

        // --- READY TO PRUNE (Soft Deleted 30+ Days ago) ---
        $this->createPrunableAnnouncement(1099, 1, 101, 'Spam Post', 'Deleted 40 days ago.', 'Spam', 70, 40);

        // 4. Create Events

        // --- ACTIVE / FUTURE ---
        Event::create([
            'event_id' => 501, 'user_id' => 101, 'board_id' => 1,
            'title' => 'Graduation Ball', 'content' => 'Big party.', 'venue' => 'Gym',
            'start_time' => now()->addDays(10), 'end_time' => now()->addDays(10)->addHours(4),
        ]);
        Event::create([
            'event_id' => 504, 'user_id' => 102, 'board_id' => 3,
            'title' => 'IS Quiz Bee', 'content' => 'Departmental level.', 'venue' => 'Lab 3',
            'start_time' => now()->addDays(2), 'end_time' => now()->addDays(2)->addHours(2),
        ]);

        // --- EXPIRED (End Time passed) ---
        Event::create([
            'event_id' => 502, 'user_id' => 101, 'board_id' => 1,
            'title' => 'Past Workshop', 'content' => 'Ended recently.', 'venue' => 'Lab 1',
            'start_time' => now()->subHours(5), 'end_time' => now()->subHours(2),
        ]);

        // --- READY TO SOFT DELETE (Created 30+ Days ago) ---
        $this->createOldEvent(505, 103, 2, 'Old Seminar', 'Created 32 days ago.', 'Audio-Visual', 32);

        // --- READY TO PRUNE (Soft Deleted 30+ Days ago) ---
        $this->createPrunableEvent(599, 101, 1, 'Ancient Meeting', 'Deleted long ago.', 'Room 101', 80, 35);
    }

    /**
     * Helper to create old active records (Stage 1 Test)
     */
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

    /**
     * Helper to create records ready for Pruning (Stage 2 Test)
     */
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