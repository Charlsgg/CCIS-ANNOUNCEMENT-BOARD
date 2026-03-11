<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Required for $this->authorize()

class AnnouncementController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        // Standardizing the response to always return JSON for your API calls
        return response()->json(
            Announcement::with('author:id,name') // Ensure your foreign key is correct (usually 'id')
                ->latest()
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string',
            'board_id' => 'required|integer',
        ]);

        // Auth is handled by 'auth' middleware in routes, but extra safety is fine
        $announcement = Announcement::create([
            'title'     => $validated['title'],
            'content'   => $validated['content'],
            'board_id'  => $validated['board_id'],
            'author_id' => Auth::id(), 
        ]);

        return response()->json($announcement->load('author:id,name'), 201);
    }

    public function update(Request $request, Announcement $announcement)
    {
        // AUTHORIZATION: Only the owner can update
        if ($announcement->author_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden: You do not own this post.'], 403);
        }

        $validated = $request->validate([
            'title'   => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        $announcement->update($validated);

        return response()->json($announcement);
    }

    public function destroy(Announcement $announcement)
    {
        // AUTHORIZATION: Only the owner can delete
        if ($announcement->author_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $announcement->delete();

        return response()->json(['message' => 'Deleted successfully']);
    }
}