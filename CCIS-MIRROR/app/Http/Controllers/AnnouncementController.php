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
        // 1. Fetch announcements with their authors, profiles, and attachments
        $announcements = Announcement::with(['author.profile', 'attachments'])
            ->latest()
            ->get();

        // 2. Map the data to exactly match your Vue frontend's interface
        $formattedAnnouncements = $announcements->map(function ($post) {
            return [
                'id'            => $post->id,
                'title'         => $post->title,
                'content'       => $post->content,
                'topic'         => $post->topic,
                // Formats date nicely (e.g., "2 hours ago")
                'date'          => Carbon::parse($post->created_at)->diffForHumans(), 
                
                // Safely dig into the relationships to pull out the name and full Supabase URL
                'author_name'   => $post->author ? $post->author->name : 'Unknown User',
                'author_avatar' => ($post->author && $post->author->profile) 
                                    ? $post->author->profile->profile_picture 
                                    : null,
                
                'likes_count'   => $post->likes_count ?? 0, 
                
                // Map the attachments to keep the array clean
                'attachments'   => $post->attachments->map(function($file) {
                    return [
                        'id'        => $file->id,
                        'file_path' => $file->file_path, // This is already the full Supabase URL from your store() method
                        'file_type' => $file->file_type,
                    ];
                })->toArray(),
            ];
        });

        // 3. Return the clean, flat array to Vue
        return response()->json($formattedAnnouncements);
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
