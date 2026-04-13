<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UserAnnouncementController extends Controller
{
    /**
     * Helper to format attachments with full Supabase URLs.
     * Uses manual construction to avoid S3-driver URL inconsistencies.
     */
    private function formatAttachments($group, $baseStorageUrl)
    {
        return $group->whereNotNull('attachment_id')
            ->map(fn($item) => [
                'attachment_id' => $item->attachment_id,
                'file_type'     => $item->file_type,
                // If the path in DB is already a URL, use it. 
                // Otherwise, join the base URL with the cleaned file path.
                'file_path'     => str_starts_with($item->file_path, 'http') 
                    ? $item->file_path 
                    : $baseStorageUrl . ltrim($item->file_path, '/'),
            ])->values()->toArray();
    }

    public function index()
    {
        $user = Auth::user();
        
        // --- Setup Supabase Base URL ---
        $supabaseUrl = rtrim(env('SUPABASE_URL'), '/'); 
        $bucket = env('AWS_BUCKET'); // Usually 'announcements'
        $baseStorageUrl = "{$supabaseUrl}/storage/v1/object/public/{$bucket}/";

        // Fetch User Avatar
        $userProfile = DB::table('user_profiles')->where('user_id', $user->user_id)->first();
        $userAvatar = null;
        if ($userProfile && $userProfile->profile_picture) {
            $userAvatar = str_starts_with($userProfile->profile_picture, 'http') 
                ? $userProfile->profile_picture 
                : $baseStorageUrl . ltrim($userProfile->profile_picture, '/');
        }

        // Fetch Data from the View
        $rawData = DB::table('user_announcements_attachments_view')
            ->where('author_id', $user->user_id)
            ->orderBy('announcement_date', 'desc')
            ->get();

        $formattedAnnouncements = $rawData->groupBy('announcement_id')->map(function ($group) use ($user, $userAvatar, $baseStorageUrl) {
            $main = $group->first();

            return [
                'id'            => $main->announcement_id,
                'title'         => $main->title,
                'content'       => $main->content,
                'topic'         => $main->topic,
                'date'          => Carbon::parse($main->announcement_date)->diffForHumans(),
                'likes_count'   => (int) ($main->likes_count ?? 0),
                'author_name'   => $user->name,
                'author_avatar' => $userAvatar,
                'attachments'   => $this->formatAttachments($group, $baseStorageUrl),
            ];
        })->values()->toArray();

        return response()->json([
            'announcements' => $formattedAnnouncements
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $announcement = Announcement::findOrFail($id);

        if ($announcement->author_id !== $user->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'content'    => 'required|string',
            'topic'      => 'nullable|string|max:255',
            'newFiles.*' => 'nullable|file|max:10240',
            'deletedIds' => 'nullable',
        ]);

        $announcement->update([
            'title'   => $validated['title'],
            'content' => $validated['content'],
            'topic'   => $validated['topic'] ?? null,
        ]);

        // 1. Process Deleted Attachments
        if ($request->has('deletedIds')) {
            $deletedIds = $request->input('deletedIds');
            if (is_string($deletedIds)) $deletedIds = json_decode($deletedIds, true);

            if (is_array($deletedIds) && count($deletedIds) > 0) {
                // Ensure this matches your Primary Key (attachment_id)
                $attachmentsToDelete = $announcement->attachments()
                    ->whereIn('attachment_id', $deletedIds)
                    ->get();

                foreach ($attachmentsToDelete as $attachment) {
                    if (Storage::disk('s3')->exists($attachment->file_path)) {
                        Storage::disk('s3')->delete($attachment->file_path);
                    }
                    $attachment->forceDelete();
                }
            }
        }

        // 2. Upload New Attachments
        if ($request->hasFile('newFiles')) {
            foreach ($request->file('newFiles') as $file) {
                $path = $file->store('announcements', 's3');

                $announcement->attachments()->create([
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        // 3. Re-fetch fresh data for the response
        $supabaseUrl = rtrim(env('SUPABASE_URL'), '/'); 
        $bucket = env('AWS_BUCKET'); 
        $baseStorageUrl = "{$supabaseUrl}/storage/v1/object/public/{$bucket}/";

        $rawData = DB::table('user_announcements_attachments_view')
            ->where('announcement_id', $announcement->id)
            ->get();

        $main = $rawData->first();

        return response()->json([
            'message' => 'Announcement updated successfully',
            'announcement' => [
                'id'            => $main->announcement_id,
                'title'         => $main->title,
                'content'       => $main->content,
                'topic'         => $main->topic,
                'date'          => Carbon::parse($main->announcement_date)->diffForHumans(),
                'likes_count'   => (int) ($main->likes_count ?? 0),
                'author_name'   => $user->name,
                'author_avatar' => $user->profile->profile_picture ?? null,
                'attachments'   => $this->formatAttachments($rawData, $baseStorageUrl),
            ]
        ]);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $announcement = Announcement::findOrFail($id);

        if ($announcement->author_id !== $user->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        foreach ($announcement->attachments as $file) {
            if (Storage::disk('s3')->exists($file->file_path)) {
                Storage::disk('s3')->delete($file->file_path);
            }
        }

        $announcement->attachments()->forceDelete();
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully']);
    }
}