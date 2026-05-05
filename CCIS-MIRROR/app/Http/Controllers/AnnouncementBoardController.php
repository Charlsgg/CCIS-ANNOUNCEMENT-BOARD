<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AnnouncementBoardController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
        return redirect('https://miracis.vercel.app/announcements-board'); 
        // Or you can return abort(403, 'Direct access not allowed.');
    }
        $filter = $request->query('topic');
        $userId = Auth::id();

        // 1. Fetch the Current Logged-in User's Avatar
        $currentUserAvatar = null;
        if ($userId) {
            $userProfile = DB::table('user_profiles')->where('user_id', $userId)->first();

            if ($userProfile && $userProfile->profile_picture) {
                // If it's an external URL (Google/Facebook auth), use it. Otherwise, use Laravel's Storage.
                $currentUserAvatar = str_starts_with($userProfile->profile_picture, 'http')
                    ? $userProfile->profile_picture
                    : Storage::url($userProfile->profile_picture);
            }
        }

        // Fetch Announcements
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
            ->map(function ($group) {
                $first = $group->first();
                $rawDate = Carbon::parse($first->announcement_date);

                return [
                    'id'            => $first->announcement_id,
                    'title'         => $first->title,
                    'content'       => $first->content,
                    'topic'         => $first->topic,
                    'author_name'   => $first->author_name,
                    'author_type'   => $first->author_type,
                    'author_avatar' => $first->real_avatar
                        ? (str_starts_with($first->real_avatar, 'http') ? $first->real_avatar : Storage::url($first->real_avatar))
                        : null,

                    'likes_count'   => (int) ($first->likes_count ?? 0),
                    'date'          => $rawDate->diffForHumans(),
                    'full_date'     => $rawDate->format('M d, Y h:i A'),
                    'created_at'    => $first->announcement_date,

                    'attachments' => $group->whereNotNull('attachment_id')
                        ->sortBy('attachment_id')
                        ->map(fn($item) => [
                            'id'        => $item->attachment_id,
                            'file_path' => str_starts_with($item->file_path, 'http') ? $item->file_path : Storage::url($item->file_path),
                            'file_type' => $item->file_type,
                        ])->values(),
                ];
            })->values();

        // Fetch Upcoming Events
        $upcomingEvents = DB::table('table_events')
            ->where('start_time', '>=', now()->toDateTimeString())
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

        // Calculate Stats
        $statsRaw = DB::table('all_announcements_view')
            ->select('author_type', DB::raw('count(DISTINCT announcement_id)::int as total'))
            ->groupBy('author_type')
            ->pluck('total', 'author_type');

        return response()->json([
            'current_user_avatar' => $currentUserAvatar,
            'announcements'       => $announcements,
            'upcoming_events'     => $upcomingEvents,
            'active_filter'       => $filter,
            'stats' => [
                'cs'  => $statsRaw->get('cs_instructor', 0),
                'it'  => $statsRaw->get('it_instructor', 0),
                'is'  => $statsRaw->get('is_instructor', 0),
                'lsg' => $statsRaw->get('lsg_officer', 0),
                'all' => $statsRaw->sum(),
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
