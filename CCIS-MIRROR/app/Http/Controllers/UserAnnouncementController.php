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
     * Helper to format attachments with full Supabase URLs
     */
    private function formatAttachments($group)
    {
        return $group->filter(fn($item) => !is_null($item->attachment_id))
            ->map(function ($item) {
                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $disk = Storage::disk('s3');

                return [
                    'attachment_id' => $item->attachment_id,
                    'file_type'     => $item->file_type,
                    'file_path'     => $disk->url($item->file_path),
                ];
            })->values()->toArray();
    }
    public function index()
    {
        $user = Auth::user();
        $userAvatar = $user->profile->profile_picture ?? null;

        $rawData = DB::table('user_announcements_attachments_view')
            ->where('author_id', $user->user_id)
            ->orderBy('announcement_date', 'desc')
            ->get();

        $formattedAnnouncements = $rawData->groupBy('announcement_id')->map(function ($group) use ($user, $userAvatar) {
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
                'attachments'   => $this->formatAttachments($group),
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

        // 1. Delete Attachments from Supabase via S3 Driver
        if ($request->has('deletedIds')) {
            $deletedIds = $request->input('deletedIds');
            if (is_string($deletedIds)) $deletedIds = json_decode($deletedIds, true);

            if (is_array($deletedIds) && count($deletedIds) > 0) {
                // Adjust 'id' to 'attachment_id' if your table uses that name
                $attachmentsToDelete = $announcement->attachments()->whereIn('id', $deletedIds)->get();

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
                // This stores the file in the bucket defined in env('AWS_BUCKET')
                $path = $file->store('announcements', 's3');

                $announcement->attachments()->create([
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        // Fetch fresh data for the frontend response
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
                'attachments'   => $this->formatAttachments($rawData),
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
