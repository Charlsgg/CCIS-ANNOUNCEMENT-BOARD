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

        // Official Announcement Templates
        $officialAnnouncements = [
            ['title' => 'Midterm Examination Schedule Released', 'content' => 'Please be advised that the official schedule for the Midterm Examinations is now posted. Students are required to secure their examination permits prior to their first exam. No permit, no exam policy will be strictly implemented.'],
            ['title' => 'Call for Capstone Project Proposals', 'content' => 'All graduating students must submit their initial capstone project proposals to their respective advisers by the end of the week. Late submissions will incur deductions.'],
            ['title' => 'CCIS General Assembly', 'content' => 'Attendance is mandatory for all CCIS students for the upcoming General Assembly. Matters regarding academic policies, new faculty introductions, and upcoming college weeks will be discussed.'],
            ['title' => 'Suspension of Afternoon Classes', 'content' => 'Due to the scheduled university-wide power maintenance, all afternoon classes (from 1:00 PM onwards) are officially suspended today. Online asynchronous tasks may be assigned by your instructors.'],
            ['title' => 'Application for Dean\'s List', 'content' => 'The Office of the College Dean is now accepting applications for the Dean\'s List for the previous semester. Please submit your printed grades and completed forms to the college secretary.'],
            ['title' => 'Cybersecurity Seminar Pre-Registration', 'content' => 'The IT Department is hosting a free seminar on modern Cybersecurity threats. Limited seats are available. Interested students must pre-register via the university portal.'],
            ['title' => 'LSG Election: Filing of Candidacy', 'content' => 'The Local Student Government Commission on Elections is officially opening the filing of Certificates of Candidacy for the upcoming academic year. Please refer to the attached guidelines for qualifications.'],
            ['title' => 'Library System Maintenance Downtime', 'content' => 'The digital library access and online academic journals will be temporarily unavailable this weekend for scheduled system upgrades. Please plan your research activities accordingly.'],
            ['title' => 'New Elective Course Offerings', 'content' => 'We are pleased to announce the addition of two new elective courses: Cloud Computing Architecture and Advanced AI Ethics. Enrollment for these subjects will open next week.'],
            ['title' => 'Code of Conduct Reminder', 'content' => 'A gentle reminder to all students to strictly observe the proper university dress code and display your IDs at all times while inside the campus premises.'],
        ];

        $topics = ['Academic', 'Extracurricular', 'Administrative', 'Events', 'General'];

        $startOfMonthStamp = now()->startOfMonth()->timestamp;
        $nowStamp = now()->timestamp;

        // 3. GENERATE 100 ANNOUNCEMENTS (Strictly this month, ONLY IN THE PAST)
        for ($i = 1; $i <= 15; $i++) {
            $id = 2000 + $i;
            
            // Pick a random timestamp between the start of the month and strictly right now
            $randomTimestamp = rand($startOfMonthStamp, $nowStamp);
            $createdAt = Carbon::createFromTimestamp($randomTimestamp);
            
            // Select random official data
            $sample = $officialAnnouncements[array_rand($officialAnnouncements)];
            $refNumber = str_pad($i, 3, '0', STR_PAD_LEFT);
            
            Announcement::create([
                'announcement_id' => $id,
                'board_id' => rand(1, 5),
                'author_id' => rand(101, 105),
                'title' => "[REF-CCIS-$refNumber] " . $sample['title'],
                'content' => $sample['content'],
                'topic' => $topics[array_rand($topics)],
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        // 4. GENERATE 100 EVENTS (Strictly this month, ONLY IN THE PAST)
        // Offset the max start time by 4 hours to ensure end_time also stays in the past
        $maxStartStamp = now()->subHours(4)->timestamp;
        
        // Edge case: If the month just started (less than 4 hours ago), fallback to start of month
        $maxStartStamp = max($startOfMonthStamp, $maxStartStamp);

        for ($i = 1; $i <= 3; $i++) {
            $id = 3000 + $i;

            $randomTimestamp = rand($startOfMonthStamp, $maxStartStamp);
            $startTime = Carbon::createFromTimestamp($randomTimestamp);
            
            // Set end time 2 to 4 hours later
            $endTime = (clone $startTime)->addHours(rand(2, 4));

            // Final safety check to cap it at now() just in case of edge bounds
            if ($endTime->isFuture()) {
                $endTime = now();
            }

            Event::create([
                'event_id' => $id,
                'user_id' => rand(101, 105),
                'board_id' => rand(1, 5),
                'title' => "Official Department Event $i",
                'content' => "This is a scheduled event for faculty and students. Attendance may be required depending on the respective program head.",
                'venue' => "Room ".rand(101, 404),
                'start_time' => $startTime,
                'end_time' => $endTime,
                'created_at' => $startTime,
                'updated_at' => $startTime,
            ]);
        }
    }
}