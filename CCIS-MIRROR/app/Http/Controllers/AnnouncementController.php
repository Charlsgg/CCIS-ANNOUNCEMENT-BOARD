<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{

    public function index()
    {

        return Announcement::with('author:user_id,name')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function store(Request $request)
    {

        $request->validate([
            'title'    => 'required|string|max:255',
            'content'  => 'required|string',
            'board_id' => 'required|integer',
        ]);

        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $announcement = Announcement::create([
                'title'      => $request->title,
                'content' => $request->input('content'),
                'board_id'   => $request->board_id,
                'author_id'  => Auth::id(), 
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return response()->json($announcement->load('author:user_id,name'), 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to save announcement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}