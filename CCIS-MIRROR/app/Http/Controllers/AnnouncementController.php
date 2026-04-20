<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with(['author.profile', 'attachments'])
            ->latest()
            ->get();

        // Dynamically append the correct full URL for attachments before returning
        $announcements->each(function ($announcement) {
            $announcement->attachments->transform(function ($attachment) {
                // Keep the relative path but add a new 'file_url' for the frontend
                $attachment->file_url = str_starts_with($attachment->file_path, 'http') 
                    ? $attachment->file_path 
                    : Storage::url($attachment->file_path);
                return $attachment;
            });
        });

        return response()->json($announcements);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'         => 'required|string|max:255',
                'content'       => 'required|string',
                'board_id'      => 'required|integer',
                'topic'         => 'nullable|string|max:255',
                'attachments.*' => 'file|max:10240',
            ]);

            DB::beginTransaction();

            $announcement = Announcement::create([
                'title'     => $validated['title'],
                'content'   => $validated['content'],
                'topic'     => $validated['topic'] ?? 'General',
                'board_id'  => $validated['board_id'],
                'author_id' => Auth::id(),
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    try {
                        // 1. Store the file using Laravel's default configured disk 
                        // (No hardcoded 's3', relies on your .env FILESYSTEM_DISK)
                        $path = $file->store('announcement-files');
                        
                        // 2. Save ONLY the relative path to the database
                        $announcement->attachments()->create([
                            'file_path' => $path, 
                            'file_type' => $file->getClientMimeType(),
                        ]);
                    } catch (\Exception $storageError) {
                        DB::rollBack();
                        Log::error("Storage Upload Failed: " . $storageError->getMessage());
                        return response()->json([
                            'message' => 'File Storage Upload Failed',
                            'error_detail' => $storageError->getMessage(),
                        ], 500);
                    }
                }
            }

            DB::commit();

            // Load relations and append the file_url just like the index method
            $announcement->load(['author.profile', 'attachments']);
            $announcement->attachments->transform(function ($attachment) {
                $attachment->file_url = str_starts_with($attachment->file_path, 'http') 
                    ? $attachment->file_path 
                    : Storage::url($attachment->file_path);
                return $attachment;
            });

            return response()->json($announcement, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("General Error: " . $e->getMessage());
            return response()->json([
                'message' => 'General Server Error',
                'error_detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Announcement $announcement)
    {
        if ($announcement->author_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $validated = $request->validate([
                'title'                 => 'required|string|max:255',
                'content'               => 'required|string',
                'topic'                 => 'nullable|string|max:255',
                'new_attachments.*'     => 'file|max:10240',
                'deleted_attachments.*' => 'integer',
            ]);

            DB::beginTransaction();

            $announcement->update([
                'title'   => $validated['title'],
                'content' => $validated['content'],
                'topic'   => $validated['topic'] ?? 'General',
            ]);

            // Handle Deleted Attachments
            if ($request->has('deleted_attachments')) {
                $filesToDelete = $announcement->attachments()
                    ->whereIn('attachment_id', $request->input('deleted_attachments'))
                    ->get();

                foreach ($filesToDelete as $file) {
                    try {
                        // Standard Laravel deletion. It handles the relative path directly.
                        if (!str_starts_with($file->file_path, 'http')) {
                            Storage::delete($file->file_path);
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed to delete announcement file on update: " . $e->getMessage());
                    }

                    $file->delete();
                }
            }

            // Handle New Attachments
            if ($request->hasFile('new_attachments')) {
                foreach ($request->file('new_attachments') as $file) {
                    try {
                        // Store using default disk
                        $path = $file->store('announcement-files');

                        // Save relative path
                        $announcement->attachments()->create([
                            'file_path' => $path,
                            'file_type' => $file->getClientMimeType(),
                        ]);
                    } catch (\Exception $storageError) {
                        DB::rollBack();
                        Log::error("Storage Upload Failed during Update: " . $storageError->getMessage());
                        return response()->json([
                            'message' => 'File Storage Upload Failed',
                            'error_detail' => $storageError->getMessage(),
                        ], 500);
                    }
                }
            }

            DB::commit();

            $announcement->load(['author.profile', 'attachments']);
            $announcement->attachments->transform(function ($attachment) {
                $attachment->file_url = str_starts_with($attachment->file_path, 'http') 
                    ? $attachment->file_path 
                    : Storage::url($attachment->file_path);
                return $attachment;
            });

            return response()->json($announcement);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("General Error during Update: " . $e->getMessage());
            return response()->json([
                'message' => 'General Server Error',
                'error_detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->author_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        foreach ($announcement->attachments as $file) {
            try {
                // Simplified deletion logic
                if (!str_starts_with($file->file_path, 'http')) {
                    Storage::delete($file->file_path);
                }
            } catch (\Exception $e) {
                Log::error("Failed to delete announcement file: " . $e->getMessage());
            }

            $file->delete();
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully']);
    }
}