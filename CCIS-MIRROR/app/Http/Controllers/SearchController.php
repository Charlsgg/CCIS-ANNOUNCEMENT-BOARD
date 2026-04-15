<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str; // Required for Str::limit
use App\Models\User;
use App\Models\Announcement; // Added Announcement model
use App\Models\Event;        // Added Event model

class SearchController extends Controller
{
    public function globalSearch(Request $request)
    {
        $query = $request->input('q');

        // If no query is provided, return an empty array
        if (!$query) {
            return response()->json(['results' => []]);
        }

        // Create an empty collection to hold everything
        $results = collect();

        // ---------------------------------------------------------
        // 1. Search Users
        // ---------------------------------------------------------
        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => 'user_' . $user->id,
                    'type' => 'User',
                    'title' => $user->name,
                    'description' => $user->email,
                    // If you have a specific user profile page, put it here:
                    'url' => '#' 
                ];
            });
        $results = $results->concat($users);

        // ---------------------------------------------------------
        // 2. Search Announcements
        // ---------------------------------------------------------
        if (class_exists(Announcement::class)) {
            $announcements = Announcement::where('title', 'LIKE', "%{$query}%")
                ->orWhere('content', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get()
                ->map(function ($announcement) {
                    return [
                        'id' => 'announcement_' . $announcement->id,
                        'type' => 'Announcement',
                        'title' => $announcement->title,
                        // strip_tags removes HTML if your content is rich text
                        'description' => Str::limit(strip_tags($announcement->content), 80),
                        'url' => '/announcements-board' // From your web.php
                    ];
                });
            $results = $results->concat($announcements);
        }

        // ---------------------------------------------------------
        // 3. Search Events
        // ---------------------------------------------------------
        if (class_exists(Event::class)) {
            $events = Event::where('title', 'LIKE', "%{$query}%")
                // Change 'description' if your Event model uses a different column name
                ->orWhere('description', 'LIKE', "%{$query}%") 
                ->limit(5)
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => 'event_' . $event->id,
                        'type' => 'Event',
                        'title' => $event->title,
                        'description' => Str::limit(strip_tags($event->description), 80),
                        'url' => '/events' // From your web.php
                    ];
                });
            $results = $results->concat($events);
        }

        // Return the combined, shuffled list of results
        return response()->json([
            // ->values()->all() resets the array keys so it formats perfectly as JSON
            'results' => $results->values()->all() 
        ]);
    }
}