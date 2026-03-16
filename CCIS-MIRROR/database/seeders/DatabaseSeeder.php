<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Board;
use App\Models\Announcement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Users
        $users = [
            [
                'user_id' => 101,
                'name' => 'Juan Dela Cruz',
                'email' => 'admin1@gmail.com',
                'user_type' => 'it_instructor',
                'password' => bcrypt('password123'),
                'created_at' => Carbon::now(),
            ],
            [
                'user_id' => 102,
                'name' => 'Maria Clara',
                'email' => 'admin2@gmail.com',
                'user_type' => 'cs_instructor',
                'password' => bcrypt('password123'),
                'created_at' => Carbon::now(),
            ],
            [
                'user_id' => 103,
                'name' => 'Jose Rizal',
                'email' => 'admin3@gmail.com',
                'user_type' => 'lsg_officer',
                'password' => bcrypt('password123'),
                'created_at' => Carbon::now(),
            ],
            [
                'user_id' => 105,
                'name' => 'Imong mama',
                'email' => 'admin4@gmail.com',
                'user_type' => 'is_instructor',
                'password' => bcrypt('password123'),
                'created_at' => Carbon::now(),
            ],
            [
                'user_id' => 109,
                'name' => 'sad mama',
                'email' => 'admin5@gmail.com',
                'user_type' => 'is_instructor',
                'password' => bcrypt('password123'),
                'created_at' => Carbon::now(),
            ]

        ];

        foreach ($users as $user) {
            User::create($user);
        }

        // 2. Seed Boards
        $boards = [
            [
                'board_id' => 1, // Changed this back to 1 to match the announcements below
                'board_name' => 'IT Department Announcements',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'board_id' => 2, 
                'board_name' => 'CS Department Announcements',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'board_id' => 3, 
                'board_name' => 'CCIS Local Government',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        // I removed the duplicate foreach loop here!
        foreach ($boards as $board) {
            Board::create($board); 
        }

        // // 3. Seed Announcements
        // $announcements = [
        //     [
        //         'announcement_id' => 1001,
        //         'board_id' => 1, // IT Department
        //         'author_id' => 101, // Juan
        //         'content' => 'Welcome to the new semester! Please check your schedules.',
        //         'created_at' => Carbon::now(),
        //     ],
        //     [
        //         'announcement_id' => 1002,
        //         'board_id' => 3, // CCISLG
        //         'author_id' => 103, // Jose
        //         'content' => 'Upcoming General Assembly next Friday at the gym.',
        //         'created_at' => Carbon::now()->subDays(2),
        //     ]
        // ];

        // foreach ($announcements as $announcement) {
        //     Announcement::create($announcement);
        // }
    }
}