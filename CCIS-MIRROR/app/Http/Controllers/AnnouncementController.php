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

        return response()->json($announcements);
    }

    public function store(Request $request)
    {
        // REMOVED: Artisan::call('config:clear') - Never run this in a controller!

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
                        // 1. Store the file in the 's3' disk (announcements bucket)
                        // We store it in a subfolder called 'announcement-files'
                        $path = $file->store('announcement-files', 's3');
                        
                        // 2. Get the FULL PUBLIC URL from Supabase
                        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                        $disk = Storage::disk('s3');
                        $fullUrl = $disk->url($path);

                        // 3. Save the FULL URL to the database
                        $announcement->attachments()->create([
                            'file_path' => $fullUrl, // Saving full URL here
                            'file_type' => $file->getClientMimeType(),
                        ]);
                    } catch (\Exception $s3Error) {
                        DB::rollBack();
                        Log::error("S3 Upload Failed: " . $s3Error->getMessage());
                        return response()->json([
                            'message' => 'Supabase Storage Upload Failed',
                            'error_detail' => $s3Error->getMessage(),
                        ], 500);
                    }
                }
            }

            DB::commit();

            return response()->json(
                $announcement->load(['author.profile', 'attachments']),
                201
            );
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
            // 1. Validate incoming data (including new and deleted attachments)
            $validated = $request->validate([
                'title'                 => 'required|string|max:255',
                'content'               => 'required|string',
                'topic'                 => 'nullable|string|max:255',
                'new_attachments.*'     => 'file|max:10240',
                'deleted_attachments.*' => 'integer',
            ]);

            DB::beginTransaction();

            // 2. Update basic text fields
            $announcement->update([
                'title'   => $validated['title'],
                'content' => $validated['content'],
                'topic'   => $validated['topic'] ?? 'General',
            ]);

            // 3. Handle Deleted Attachments
            if ($request->has('deleted_attachments')) {
                // Find the attachments that belong to this announcement and match the deleted IDs
                // Note: Change 'attachment_id' to 'id' if your database primary key for attachments is just 'id'
                $filesToDelete = $announcement->attachments()
                    ->whereIn('attachment_id', $request->input('deleted_attachments'))
                    ->get();

                foreach ($filesToDelete as $file) {
                    try {
                        // Extract relative path to delete from S3
                        $urlPath = parse_url($file->file_path, PHP_URL_PATH);
                        $segments = explode('/public/' . env('AWS_BUCKET') . '/', $urlPath);

                        if (isset($segments[1])) {
                            Storage::disk('s3')->delete($segments[1]);
                        }
                    } catch (\Exception $e) {
                        Log::error("Failed to delete announcement file on update: " . $e->getMessage());
                    }

                    // Remove from database
                    $file->delete();
                }
            }

            // 4. Handle New Attachments
            if ($request->hasFile('new_attachments')) {
                foreach ($request->file('new_attachments') as $file) {
                    try {
                        // Store file and get URL
                        $path = $file->store('announcement-files', 's3');

                        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                        $disk = Storage::disk('s3');
                        $fullUrl = $disk->url($path);

                        // Save to database
                        $announcement->attachments()->create([
                            'file_path' => $fullUrl,
                            'file_type' => $file->getClientMimeType(),
                        ]);
                    } catch (\Exception $s3Error) {
                        DB::rollBack();
                        Log::error("S3 Upload Failed during Update: " . $s3Error->getMessage());
                        return response()->json([
                            'message' => 'Supabase Storage Upload Failed',
                            'error_detail' => $s3Error->getMessage(),
                        ], 500);
                    }
                }
            }

            DB::commit();

            return response()->json($announcement->load(['author.profile', 'attachments']));
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
                // Extract relative path from the stored full URL to delete it
                $urlPath = parse_url($file->file_path, PHP_URL_PATH);
                $segments = explode('/public/' . env('AWS_BUCKET') . '/', $urlPath);

                if (isset($segments[1])) {
                    Storage::disk('s3')->delete($segments[1]);
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
