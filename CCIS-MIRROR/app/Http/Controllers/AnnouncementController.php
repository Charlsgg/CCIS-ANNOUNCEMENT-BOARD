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
    public function index()
    {
        $user = Auth::user();

        // Use the avatars disk to get the full public URL for the author
        $userAvatar = $user->profile && $user->profile->profile_picture
            ? $user->profile->profile_picture
            : null;

        $rawData = DB::table('user_announcements_attachments_view')
            ->where('author_id', $user->user_id)
            ->orderBy('announcement_date', 'desc')
            ->get();

        $groupedAnnouncements = $rawData->groupBy('announcement_id');

        $formattedAnnouncements = $groupedAnnouncements->map(function ($group) use ($user, $userAvatar) {
            $main = $group->first();

            $attachments = $group->filter(fn($item) => !is_null($item->attachment_id))
                ->map(function ($item) {
                    // Ensure we are returning the full URL for attachments
                    // If your DB view already has the full URL, return it directly.
                    // Otherwise, wrap it: Storage::disk('s3')->url($item->file_path)
                    return [
                        'attachment_id' => $item->attachment_id,
                        'file_type'     => $item->file_type,
                        'file_path'     => $item->file_path,
                    ];
                })->values()->toArray();

            return [
                'id'            => $main->announcement_id,
                'title'         => $main->title,
                'content'       => $main->content,
                'topic'         => $main->topic,
                'date'          => Carbon::parse($main->announcement_date)->diffForHumans(),
                'likes_count'   => (int) ($main->likes_count ?? 0),
                'author_name'   => $user->name,
                'author_avatar' => $userAvatar,
                'attachments'   => $attachments,
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

        // 1. Process Deleted Attachments from Supabase
        if ($request->has('deletedIds')) {
            $deletedIds = $request->input('deletedIds');
            if (is_string($deletedIds)) $deletedIds = json_decode($deletedIds, true);

            if (is_array($deletedIds) && count($deletedIds) > 0) {
                $attachmentsToDelete = $announcement->attachments()->whereIn('id', $deletedIds)->get();

                foreach ($attachmentsToDelete as $attachment) {
                    // Extract path from URL to delete from S3
                    $urlPath = parse_url($attachment->file_path, PHP_URL_PATH);
                    $segments = explode('/public/' . env('AWS_BUCKET') . '/', $urlPath);
                    $relativePath = $segments[1] ?? $attachment->file_path;

                    Storage::disk('s3')->delete($relativePath);
                    $attachment->forceDelete();
                }
            }
        }

        // 2. Process New Attachments to Supabase
        if ($request->hasFile('newFiles')) {
            foreach ($request->file('newFiles') as $file) {
                $path = $file->store('announcement-files', 's3');
                // Inside your store or update method:

                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $disk = Storage::disk('s3');

                $fullUrl = $disk->url($path);

                $announcement->attachments()->create([
                    'file_path' => $fullUrl,
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        // Fetch Fresh Data for Frontend
        return $this->index();
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $announcement = Announcement::findOrFail($id);

        if ($announcement->author_id !== $user->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        foreach ($announcement->attachments as $file) {
            $urlPath = parse_url($file->file_path, PHP_URL_PATH);
            $segments = explode('/public/' . env('AWS_BUCKET') . '/', $urlPath);
            $relativePath = $segments[1] ?? $file->file_path;

            Storage::disk('s3')->delete($relativePath);
        }

        $announcement->attachments()->forceDelete();
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully']);
    }
}
