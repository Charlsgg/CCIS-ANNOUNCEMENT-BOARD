<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EventController extends Controller
{
    /**
     * Display a listing of the events.
     */
    public function index()
    {
        $events = Event::with(['author', 'board'])->paginate(15);
        return response()->json($events);
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_type' => 'required|string|max:255',
            'author_id'  => 'required|exists:users,user_id',
            'board_id'   => 'required|exists:table_boards,board_id',
            'title'      => 'required|string|max:255',
            'content'    => 'nullable|string',
            'start_time' => 'required|date',
            'end_time'   => 'required|date|after:start_time',
        ]);

        $event = Event::create($validated);

        return response()->json($event, Response::HTTP_CREATED);
    }

    /**
     * Display the specified event.
     */
    public function show($id)
    {
        $event = Event::with(['author', 'board'])->findOrFail($id);
        return response()->json($event);
    }

    /**
     * Update the specified event in storage.
     */
    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'event_type' => 'sometimes|string|max:255',
            'title'      => 'sometimes|string|max:255',
            'content'    => 'nullable|string',
            'start_time' => 'sometimes|date',
            'end_time'   => 'sometimes|date|after:start_time',
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    /**
     * Remove the specified event from storage (Soft Delete).
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json(['message' => 'Event deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}