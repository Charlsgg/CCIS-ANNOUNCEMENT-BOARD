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

        $rawData = DB::table('user_announcements_attachments_view')
            ->where('author_id', $user->user_id) 
            ->orderBy('announcement_date', 'desc')
            ->get();

        $groupedAnnouncements = $rawData->groupBy('announcement_id');

        $formattedAnnouncements = $groupedAnnouncements->map(function ($group) use ($user, $userAvatar) {
            $main = $group->first();

            // Format Avatar URL using standard Laravel Storage
            $formattedAvatar = null;
            if ($userAvatar) {
                $formattedAvatar = str_starts_with($userAvatar, 'http') 
                    ? $userAvatar 
                    : Storage::url($userAvatar);
            }

            // Format Attachments URLs using standard Laravel Storage
            $attachments = $group->filter(fn($item) => !is_null($item->attachment_id))
                ->map(fn($item) => [
                    'attachment_id' => $item->attachment_id,
                    'file_type'     => $item->file_type,
                    'file_path'     => str_starts_with($item->file_path, 'http') 
                                        ? $item->file_path 
                                        : Storage::url($item->file_path),
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
                            
                            // Let Laravel handle the deletion natively
                            if (!str_starts_with($pathToDelete, 'http')) {
                                Storage::delete($pathToDelete);
                            }
                        } catch (\Exception $e) {
                            Log::error("Failed to delete attachment: " . $e->getMessage());
                        }
                        
                        $attachment->forceDelete();
                    }
                }
            }

            // 2. Process New Attachments using Default Storage
            if ($request->hasFile('newFiles')) {
                foreach ($request->file('newFiles') as $file) {
                    // Store file in the 'announcements' directory using the default disk
                    $path = $file->store('announcements');
                    
                    $announcement->attachments()->create([
                        'file_path' => $path, // Saving relative path
                        'file_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            DB::commit();

            // Fetch the updated data from the view
            $userAvatar = $user->profile->profile_picture ?? null;
            $formattedAvatar = $userAvatar 
                ? (str_starts_with($userAvatar, 'http') ? $userAvatar : Storage::url($userAvatar)) 
                : null;

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
                                        : Storage::url($item->file_path),
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
                    
                    // Let Laravel handle the deletion natively
                    if (!str_starts_with($pathToDelete, 'http')) {
                        Storage::delete($pathToDelete);
                    }
                } catch (\Exception $e) {
                    Log::error("Failed to delete attachment on destroy: " . $e->getMessage());
                }
            }
            $announcement->attachments()->forceDelete();
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully']);
    }
}