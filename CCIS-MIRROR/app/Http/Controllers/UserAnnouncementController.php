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
        $userAvatar = $user->profile->profile_picture ?? null;
        
        $rawData = DB::table('user_announcements_attachments_view')
            ->where('author_id', $user->user_id) 
            ->orderBy('announcement_date', 'desc')
            ->get();

        $groupedAnnouncements = $rawData->groupBy('announcement_id');

        $formattedAnnouncements = $groupedAnnouncements->map(function ($group) use ($user, $userAvatar) {
            $main = $group->first();

            $attachments = $group->filter(fn($item) => !is_null($item->attachment_id))
                ->map(fn($item) => [
                    'attachment_id' => $item->attachment_id,
                    'file_type'     => $item->file_type,
                    'file_path'     => $item->file_path,
                ])->values()->toArray();

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

        $announcement->update([
            'title'   => $validated['title'],
            'content' => $validated['content'],
            'topic'   => $validated['topic'] ?? null,
        ]);

        // 1. Process Deleted Attachments
        if ($request->has('deletedIds')) {
            $deletedIds = $request->input('deletedIds');
            
            // Depending on how FormData is appended, it might arrive as a JSON string
            if (is_string($deletedIds)) {
                $deletedIds = json_decode($deletedIds, true);
            }

            if (is_array($deletedIds) && count($deletedIds) > 0) {
                // Ensure 'id' matches the primary key of your attachments table. 
                // Change to 'attachment_id' if your model uses that.
                $attachmentsToDelete = $announcement->attachments()->whereIn('id', $deletedIds)->get();
                
                foreach ($attachmentsToDelete as $attachment) {
                    if (Storage::disk('public')->exists($attachment->file_path)) {
                        Storage::disk('public')->delete($attachment->file_path);
                    }
                    $attachment->forceDelete();
                }
            }
        }

        // 2. Process New Attachments
        if ($request->hasFile('newFiles')) {
            foreach ($request->file('newFiles') as $file) {
                // Store file in the 'announcements' directory within the 'public' disk
                $path = $file->store('announcements', 'public');
                
                $announcement->attachments()->create([
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        // Fetch the updated data from the view to maintain consistent frontend structure
        $userAvatar = $user->profile->profile_picture ?? null;
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
                'file_path'     => $item->file_path,
            ])->values()->toArray();

        $formattedAnnouncement = [
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

        return response()->json([
            'message' => 'Announcement updated successfully', 
            'announcement' => $formattedAnnouncement
        ]);
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
                if (Storage::disk('public')->exists($file->file_path)) {
                    Storage::disk('public')->delete($file->file_path);
                }
            }
            $announcement->attachments()->forceDelete();
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully']);
    }
}