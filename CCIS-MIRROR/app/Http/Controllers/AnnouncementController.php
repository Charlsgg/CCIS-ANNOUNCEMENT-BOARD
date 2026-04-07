<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    /**
     * Fetch all announcements with their author, author's profile, and attachments.
     */
    public function index()
    {
        $announcements = Announcement::with(['author.profile', 'attachments'])
            ->latest()
            ->get();

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

            // Create the Announcement record
            $announcement = Announcement::create([
                'title'     => $validated['title'],
                'content'   => $validated['content'],
                'topic'     => $validated['topic'] ?? 'General',
                'board_id'  => $validated['board_id'],
                'author_id' => Auth::id(),
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('announcements', 's3');

                    $announcement->attachments()->create([
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            return response()->json(
                $announcement->load(['author.profile', 'attachments']),
                201
            );
            
        } catch (\Exception $e) {
            // This will force the exact error to appear in your browser's Network tab
            return response()->json([
                'message' => 'Upload failed',
                'error_detail' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function update(Request $request, Announcement $announcement)
    {
        if ($announcement->author_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'topic'   => 'nullable|string|max:255',
        ]);

        $announcement->update($validated);

        return response()->json($announcement->load(['author.profile', 'attachments']));
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->author_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        foreach ($announcement->attachments as $file) {
            // Delete directly using the stored relative path
            Storage::disk('s3')->delete($file->file_path);
            
            // Delete the attachment record from the database 
            // (Assuming you do not have cascading deletes set up on the DB schema)
            $file->delete(); 
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully']);
    }
}