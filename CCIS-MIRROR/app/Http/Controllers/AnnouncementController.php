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

                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $disk = Storage::disk('s3');
                $publicUrl = $disk->url($path);

                $announcement->attachments()->create([
                    'file_path' => $publicUrl,
                    'file_type' => $file->getClientMimeType(),
                ]);
            }
        }

        return response()->json(
            $announcement->load(['author.profile', 'attachments']),
            201
        );
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
            // 🚀 CHANGED HERE: Extract the path from the URL and delete it from S3
            $path = str_replace(env('AWS_ENDPOINT') . '/' . env('AWS_BUCKET') . '/', '', $file->file_path);
            Storage::disk('s3')->delete($path);
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully']);
    }
}
