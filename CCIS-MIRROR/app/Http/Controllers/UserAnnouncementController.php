<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UserAnnouncementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userAvatar = $user->profile->profile_picture ?? null;
        
        // Build the base Supabase public URL dynamically from .env
        $supabaseUrl = rtrim(env('SUPABASE_URL'), '/'); 
        $bucket = env('AWS_BUCKET'); 
        $baseStorageUrl = "{$supabaseUrl}/storage/v1/object/public/{$bucket}/";

        $rawData = DB::table('user_announcements_attachments_view')
            ->where('author_id', $user->user_id) 
            ->orderBy('announcement_date', 'desc')
            ->get();

        $groupedAnnouncements = $rawData->groupBy('announcement_id');

        $formattedAnnouncements = $groupedAnnouncements->map(function ($group) use ($user, $userAvatar, $baseStorageUrl) {
            $main = $group->first();

            // Format Avatar URL
            $formattedAvatar = null;
            if ($userAvatar) {
                $formattedAvatar = str_starts_with($userAvatar, 'http') 
                    ? $userAvatar 
                    : $baseStorageUrl . ltrim($userAvatar, '/');
            }

            // Format Attachments URLs
            $attachments = $group->filter(fn($item) => !is_null($item->attachment_id))
                ->map(fn($item) => [
                    'attachment_id' => $item->attachment_id,
                    'file_type'     => $item->file_type,
                    'file_path'     => str_starts_with($item->file_path, 'http') 
                                        ? $item->file_path 
                                        : $baseStorageUrl . ltrim($item->file_path, '/'),
                ])->values()->toArray();

            return [
                'id'            => $main->announcement_id,
                'title'         => $main->title,
                'content'       => $main->content,
                'topic'         => $main->topic,
                'date'          => Carbon::parse($main->announcement_date)->diffForHumans(), 
                'likes_count'   => (int) ($main->likes_count ?? 0), 
                'author_name'   => $user->name,
                'author_avatar' => $formattedAvatar, 
                'attachments'   => $attachments,
            ];
        })->values()->toArray();

        return response()->json([
            'announcements' => $formattedAnnouncements
        ]);
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $announcement = Announcement::findOrFail($id);

        // Verify the user owns this announcement
        if ($announcement->author_id !== $user->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title'      => 'required|string|max:255',
            'content'    => 'required|string',
            'topic'      => 'nullable|string|max:255',
            'newFiles.*' => 'nullable|file|max:10240', // Validates each file (e.g., max 10MB)
            'deletedIds' => 'nullable',
        ]);

        DB::beginTransaction();

        try {
            $announcement->update([
                'title'   => $validated['title'],
                'content' => $validated['content'],
                'topic'   => $validated['topic'] ?? null,
            ]);

            // 1. Process Deleted Attachments
            if ($request->has('deletedIds')) {
                $deletedIds = $request->input('deletedIds');
                
                if (is_string($deletedIds)) {
                    $deletedIds = json_decode($deletedIds, true);
                }

                if (is_array($deletedIds) && count($deletedIds) > 0) {
                    $attachmentsToDelete = $announcement->attachments()->whereIn('attachment_id', $deletedIds)->get();
                    
                    foreach ($attachmentsToDelete as $attachment) {
                        try {
                            $pathToDelete = $attachment->file_path;
                            
                            // If it's a full URL, extract the relative path for deletion
                            if (str_starts_with($pathToDelete, 'http')) {
                                $urlPath = parse_url($pathToDelete, PHP_URL_PATH);
                                $segments = explode('/public/' . env('AWS_BUCKET') . '/', $urlPath);
                                if (isset($segments[1])) {
                                    $pathToDelete = $segments[1];
                                }
                            }
                            
                            Storage::disk('s3')->delete($pathToDelete);
                        } catch (\Exception $e) {
                            Log::error("Failed to delete attachment from S3: " . $e->getMessage());
                        }
                        
                        $attachment->forceDelete();
                    }
                }
            }

            // 2. Process New Attachments (Uploading to S3)
            if ($request->hasFile('newFiles')) {
                foreach ($request->file('newFiles') as $file) {
                    // Store file in the 'announcements' directory within the 's3' disk
                    $path = $file->store('announcements', 's3');
                    
                    $announcement->attachments()->create([
                        'file_path' => $path, // Saving relative path, it will be formatted on read
                        'file_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            DB::commit();

            // Build URL helper for the response
            $supabaseUrl = rtrim(env('SUPABASE_URL'), '/'); 
            $bucket = env('AWS_BUCKET'); 
            $baseStorageUrl = "{$supabaseUrl}/storage/v1/object/public/{$bucket}/";

            // Fetch the updated data from the view
            $userAvatar = $user->profile->profile_picture ?? null;
            $formattedAvatar = $userAvatar ? (str_starts_with($userAvatar, 'http') ? $userAvatar : $baseStorageUrl . ltrim($userAvatar, '/')) : null;

            $rawData = DB::table('user_announcements_attachments_view')
                ->where('announcement_id', $announcement->id)
                ->get();

            $main = $rawData->first();
            
            if (!$main) {
                return response()->json(['message' => 'Announcement updated, but could not retrieve view data.'], 200);
            }

            $attachments = $rawData->filter(fn($item) => !is_null($item->attachment_id))
                ->map(fn($item) => [
                    'attachment_id' => $item->attachment_id,
                    'file_type'     => $item->file_type,
                    'file_path'     => str_starts_with($item->file_path, 'http') 
                                        ? $item->file_path 
                                        : $baseStorageUrl . ltrim($item->file_path, '/'),
                ])->values()->toArray();

            $formattedAnnouncement = [
                'id'            => $main->announcement_id,
                'title'         => $main->title,
                'content'       => $main->content,
                'topic'         => $main->topic,
                'date'          => Carbon::parse($main->announcement_date)->diffForHumans(), 
                'likes_count'   => (int) ($main->likes_count ?? 0), 
                'author_name'   => $user->name,
                'author_avatar' => $formattedAvatar, 
                'attachments'   => $attachments,
            ];

            return response()->json([
                'message' => 'Announcement updated successfully', 
                'announcement' => $formattedAnnouncement
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to update announcement: " . $e->getMessage());
            return response()->json([
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified announcement and its files.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $announcement = Announcement::findOrFail($id);

        if ($announcement->author_id !== $user->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($announcement->attachments()->exists()) {
            foreach ($announcement->attachments as $file) {
                try {
                    $pathToDelete = $file->file_path;
                    
                    if (str_starts_with($pathToDelete, 'http')) {
                        $urlPath = parse_url($pathToDelete, PHP_URL_PATH);
                        $segments = explode('/public/' . env('AWS_BUCKET') . '/', $urlPath);
                        if (isset($segments[1])) {
                            $pathToDelete = $segments[1];
                        }
                    }
                    
                    Storage::disk('s3')->delete($pathToDelete);
                } catch (\Exception $e) {
                    Log::error("Failed to delete attachment from S3 on destroy: " . $e->getMessage());
                }
            }
            $announcement->attachments()->forceDelete();
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully']);
    }
}