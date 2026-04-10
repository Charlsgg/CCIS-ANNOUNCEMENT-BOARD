<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
                        // Attempt to upload to Supabase. 
                        // Because 'throw' is true, this will CRASH and go to the catch block if it fails.
                        $path = $file->store('announcements', 's3');

                        // Save the attachment record to the DB
                        $announcement->attachments()->create([
                            'file_path' => $path,
                            'file_type' => $file->getClientMimeType(),
                        ]);

                    } catch (\Exception $s3Error) {
                        // 🚨 THIS catches the REAL Supabase error!
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Vercel S3 Upload Crashed!',
                            'error_detail' => $s3Error->getMessage(), // This will tell us the exact AWS issue
                            'file' => $s3Error->getFile(),
                            'line' => $s3Error->getLine()
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
            return response()->json([
                'message' => 'General Server Error',
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
            Storage::disk('s3')->delete($file->file_path);
            $file->delete(); 
        }

        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted successfully']);
    }
}