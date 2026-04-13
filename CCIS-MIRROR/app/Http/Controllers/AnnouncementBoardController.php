<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnnouncementBoardController extends Controller
{   
    public function index(Request $request)
    {
        $filter = $request->query('topic'); 
        $userId = Auth::id(); 

        // 1. Build the base Supabase public URL dynamically from your .env
        $supabaseUrl = rtrim(env('SUPABASE_URL'), '/'); // e.g., https://xyz.supabase.co
        $bucket = env('AWS_BUCKET'); // e.g., your bucket name, usually 'public'
        
        // This is the exact format Supabase uses for public bucket URLs
        $baseStorageUrl = "{$supabaseUrl}/storage/v1/object/public/{$bucket}/";

        // 2. Fetch Announcements
        $query = DB::table('all_announcements_view as av')
            ->leftJoin('user_profiles as up', 'av.author_id', '=', 'up.user_id')
            ->select(
                'av.*', 
                'up.profile_picture as real_avatar'
            );

        if ($filter) {
            $query->where('av.author_type', $filter);
        }

        $announcements = $query->orderBy('av.announcement_date', 'desc')
            ->get()
            ->groupBy('announcement_id')
            ->map(function ($group) use ($baseStorageUrl) {
                $first = $group->first();
                $rawDate = Carbon::parse($first->announcement_date);

                return [
                    'id'            => $first->announcement_id,
                    'title'         => $first->title,
                    'content'       => $first->content,
                    'topic'         => $first->topic,
                    'author_name'   => $first->author_name,
                    'author_type'   => $first->author_type,
                    
                    // Attach the full Supabase URL to the avatar
                    'author_avatar' => $first->real_avatar ? $baseStorageUrl . ltrim($first->real_avatar, '/') : null,
                    
                    'likes_count'   => (int) ($first->likes_count ?? 0),
                    'date'          => $rawDate->diffForHumans(),
                    'full_date'     => $rawDate->format('M d, Y h:i A'),
                    'created_at'    => $first->announcement_date, 
                    
                    'attachments'   => $group->whereNotNull('attachment_id')->map(fn($item) => [
                        'id'        => $item->attachment_id,
                        
                        // Attach the full Supabase URL to the uploaded file
                        'file_path' => $baseStorageUrl . ltrim($item->file_path, '/'),
                        
                        'file_type' => $item->file_type,
                    ])->values(),
                ];
            })->values();

        // 3. Fetch Upcoming Events
        $upcomingEvents = DB::table('table_events')
            ->where('start_time', '>=', now())
            ->orderBy('start_time', 'asc')
            ->take(3)
            ->get()
            ->map(function ($event) {
                $dt = Carbon::parse($event->start_time);
                return [
                    'event_id'   => $event->event_id,
                    'title'      => $event->title,
                    'content'    => $event->content,
                    'venue'      => $event->venue,
                    'start_time' => $event->start_time,
                    'created_at' => $event->created_at ? Carbon::parse($event->created_at)->diffForHumans() : 'Just now', 
                    'month'      => $dt->format('M'),
                    'day'        => $dt->format('d'),
                    'time'       => $dt->format('g:i A'),
                ];
            });

        // 4. Calculate Stats
        $statsRaw = DB::table('all_announcements_view')
            ->select('author_type', DB::raw('count(DISTINCT announcement_id) as total'))
            ->groupBy('author_type')
            ->pluck('total', 'author_type');

        return response()->json([
            'announcements'   => $announcements,
            'upcoming_events' => $upcomingEvents,
            'active_filter'   => $filter,
            'stats' => [
                'cs'  => (int) $statsRaw->get('cs_instructor', 0),
                'it'  => (int) $statsRaw->get('it_instructor', 0),
                'is'  => (int) $statsRaw->get('is_instructor', 0),
                'lsg' => (int) $statsRaw->get('lsg_officer', 0),
                'all' => (int) $statsRaw->sum(),
            ]
        ]);
    }

    public function like(Request $request, $id)
    {
        $announcement = DB::table('table_announcement')
            ->where('announcement_id', $id)
            ->first();

        if (!$announcement) {
            return response()->json(['message' => 'Announcement not found'], 404);
        }

        DB::table('table_announcement')
            ->where('announcement_id', $id)
            ->increment('likes_count');

        $newCount = DB::table('table_announcement')
            ->where('announcement_id', $id)
            ->value('likes_count');

        return response()->json([
            'status' => 'success',
            'likes_count' => (int) $newCount
        ]);
    }
}