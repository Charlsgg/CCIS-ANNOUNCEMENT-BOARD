<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Event;

class SearchController extends Controller
{
    public function globalSearch(Request $request)
    {
        $query = $request->input('q');
        if (!$query) return response()->json(['results' => []]);

        $terms = array_filter(explode(' ', $query));
        
        // 2. Search Announcements (using your DB View)
        $announcements = DB::table('user_announcements_attachments_view')
            ->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->where('title', 'LIKE', "%{$term}%")->orWhere('content', 'LIKE', "%{$term}%");
                }
            })
            ->limit(5)->get()->map(fn($a) => [
                'id' => 'ann_' . ($a->announcement_id ?? 0),
                'type' => 'Announcement',
                'title' => $a->title,
                'description' => Str::limit(strip_tags($a->content ?? ''), 80),
                'url' => '/announcements-page'
            ]);

        // 3. Search Events (using the Event model you shared)
        // Note: No 'try' block here. If this fails, Laravel will show you exactly why.
        $events = Event::query()
            ->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->where('title', 'LIKE', "%{$term}%")
                      ->orWhere('content', 'LIKE', "%{$term}%")
                      ->orWhere('venue', 'LIKE', "%{$term}%");
                }
            })
            ->limit(5)->get()->map(fn($e) => [
                'id' => 'event_' . $e->event_id,
                'type' => 'Event',
                'title' => $e->title,
                'description' => $e->venue ? "Venue: {$e->venue}" : Str::limit(strip_tags($e->content ?? ''), 80),
                'url' => '/events'
            ]);

        // Combine them all manually
        $allResults = collect($announcements)->concat($events);

        return response()->json([
            'results' => $allResults->values()->all()
        ]);
    }
}