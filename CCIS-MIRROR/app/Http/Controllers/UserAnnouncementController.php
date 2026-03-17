<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class UserAnnouncementController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $rawData = DB::table('user_announcements_attachments_view')
            ->where('author_id', $user->user_id) 
            ->orderBy('announcement_date', 'desc')
            ->get();
        $groupedAnnouncements = $rawData->groupBy('announcement_id');
        $formattedAnnouncements = $groupedAnnouncements->map(function ($group) use ($user) {
            $main = $group->first();

            $attachments = $group->filter(fn($item) => !is_null($item->attachment_id))
                ->map(fn($item) => [
                    'attachment_id' => $item->attachment_id,
                    'file_type' => $item->file_type,
                    'file_path' => $item->file_path,
                ])->values()->toArray();
            return [
                'id' => $main->announcement_id,
                'title' => $main->title,
                'content' => $main->content,
                'topic' => $main->topic,
                'date' => Carbon::parse($main->announcement_date)->diffForHumans(), 
                'likes_count' => 0, 
                'author_name' => $user->name,
                'author_avatar' => null, 
                'attachments' => $attachments,
            ];
        })->values()->toArray();

        return response()->json([
            'announcements' => $formattedAnnouncements
        ]);
    }
}