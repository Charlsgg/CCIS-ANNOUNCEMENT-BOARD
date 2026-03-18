<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Board;
use App\Models\Event;
use App\Models\Announcement;
use App\Models\Notification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
//hjgjhghj
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['user_id' => 101, 'name' => 'Juan Dela Cruz', 'email' => 'admin1@gmail.com', 'user_type' => 'it_instructor', 'password' => bcrypt('password')],
            ['user_id' => 102, 'name' => 'Maria Clara', 'email' => 'admin2@gmail.com', 'user_type' => 'is_instructor', 'password' => bcrypt('password')],
            ['user_id' => 103, 'name' => 'Jose Rizal', 'email' => 'admin3@gmail.com', 'user_type' => 'cs_instructor', 'password' => bcrypt('password')],
            ['user_id' => 104, 'name' => 'Andres Bonifacio', 'email' => 'admin4@gmail.com', 'user_type' => 'lsg_officer', 'password' => bcrypt('password')],
            ['user_id' => 105, 'name' => 'Apolinario Mabini', 'email' => 'admin5@gmail.com', 'user_type' => 'it_instructor', 'password' => bcrypt('password')],
        ];
        foreach ($users as $u) User::create($u);

        $boards = [
            ['board_id' => 1, 'board_name' => 'IT Department'],
            ['board_id' => 2, 'board_name' => 'CS Department'],
            ['board_id' => 3, 'board_name' => 'LSG Announcements'],
            ['board_id' => 4, 'board_name' => 'General CCIS'],
        ];
        foreach ($boards as $b) Board::create($b);


        $announcements = [
            ['announcement_id' => 1001, 'board_id' => 1, 'author_id' => 101, 'title' => 'Capstone Orientation', 'content' => 'Orientation for 4th years.', 'topic' => 'Academic', 'likes_count' => 15],
            ['announcement_id' => 1002, 'board_id' => 3, 'author_id' => 103, 'title' => 'Foundation Day', 'content' => 'Join the festivities!', 'topic' => 'Events', 'likes_count' => 50],
            ['announcement_id' => 1003, 'board_id' => 2, 'author_id' => 102, 'title' => 'Coding Competition', 'content' => 'Register now for Hackathon.', 'topic' => 'Contest', 'likes_count' => 8],
        ];
        foreach ($announcements as $a) Announcement::create($a);
$events = [
    [
        'event_id' => 501, 
        'user_id' => 101, 
        'board_id' => 1,
        'title' => 'IT Faculty Meeting', 
        'content' => 'Room 302', 
        'venue' => 'Room 302',
        'start_time' => Carbon::now()->addHours(2), 
        'end_time' => Carbon::now()->addHours(3),
    ],
    [
        'event_id' => 502, 
        'user_id' => 103, 
        'board_id' => 3,
        'title' => 'Student GA', 
        'content' => 'Gymnasium', 
        'venue' => 'Assembly',
        'start_time' => Carbon::now()->addHours(18), 
        'end_time' => Carbon::now()->addHours(20),
    ],
    [
        'event_id' => 503, 
        'user_id' => 102, 
        'board_id' => 2,
        'title' => 'Thesis Defense', 
        'content' => 'Lab 1', 
        'venue' => 'Lab 1',
        'start_time' => Carbon::now()->addDays(5), 
        'end_time' => Carbon::now()->addDays(5)->addHours(2),
    ],
];

foreach ($events as $e) {
    Event::create($e);
}

        // 5. Seed Many Manual Notifications
        $notifications = [
            // Likes for Juan (101)
            ['reciepient_id' => 101, 'announcement_id' => 1001, 'event_id' => null, 'message' => 'Maria Clara liked your Capstone announcement', 'is_read' => false],
            ['reciepient_id' => 101, 'announcement_id' => null, 'event_id' => 501, 'message' => 'Apolinario Mabini liked your Meeting event', 'is_read' => true],
            
            ['reciepient_id' => 103, 'announcement_id' => 1002, 'event_id' => null, 'message' => 'Andres Bonifacio liked the Foundation Day post', 'is_read' => false],
            ['reciepient_id' => 102, 'announcement_id' => null, 'event_id' => null, 'message' => 'Your profile was updated successfully', 'is_read' => true],
        ];
        foreach ($notifications as $n) Notification::create($n);
    }
}