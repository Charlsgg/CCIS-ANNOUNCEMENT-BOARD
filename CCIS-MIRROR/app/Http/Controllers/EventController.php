<?php

namespace App\Http\Controllers;

use App\Models\EventFilterView;
use App\Models\Event;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = EventFilterView::query();
        
        if ($request->filled('month')) {
            $query->where('event_month', $request->month);
        }
        if ($request->filled('year')) {
            $query->where('event_year', $request->year);
        }
        if ($request->filled('title')) {
            $query->where('title', 'LIKE', '%' . $request->title . '%');
        }

        $events = $query->get();
        return response()->json([
            'status' => 'success',
            'events' => $events
        ]);
    }

    /**
     * Fetch upcoming events for the side panel.
     * FIXED: Ensures created_at is returned to avoid "Recently" text in Vue.
     */
    public function upcoming(Request $request)
    {
        // Include events that haven't started yet OR haven't ended yet
        $events = EventFilterView::where(function ($query) {
                $query->where('start_time', '>=', now())
                      ->orWhere('end_time', '>=', now());
            })
            ->orderBy('start_time', 'asc') 
            ->take(6) 
            ->get()
            ->map(function($event) {
                // Explicitly ensuring the date is cast for the frontend
                return $event;
            });

        return response()->json([
            'status' => 'success',
            'events' => $events
        ]);
    }

    /**
     * Fetch events created by the currently authenticated user.
     */
    public function userEvents(Request $request)
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        // Fetch the user's events ordered by the most recent start time
        $events = EventFilterView::where('user_id', $userId)
            ->orderBy('start_time', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'events' => $events
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'event_month' => 'required|integer|min:1|max:12',
            'event_year' => 'required|integer',
            'Venue' => 'required|string|max:255',
            'content' => 'required|string',
            'time' => 'required|string',
            'end_time' => 'nullable|string',
            'day_range' => 'required|string', 
            'start_day' => 'required|integer|min:1|max:31', 
            'end_day' => 'nullable|integer|min:1|max:31', 
        ]);

        $timeParts = explode(':', $request->time);
        $hours = (int) $timeParts[0];
        $minutes = (int) $timeParts[1];

        $startTime = Carbon::create($request->event_year, $request->event_month, $request->start_day, $hours, $minutes, 0);
        
        $endTime = null;
        $actualEndDay = $request->end_day ?: ($request->filled('end_time') ? $request->start_day : null);

        if (!is_null($actualEndDay)) {
            $endHours = 23; $endMinutes = 59; $endSeconds = 59;

            if ($request->filled('end_time')) {
                $endTimeParts = explode(':', $request->end_time);
                $endHours = (int) $endTimeParts[0];
                $endMinutes = (int) $endTimeParts[1];
                $endSeconds = 0;
            }

            $endTime = Carbon::create($request->event_year, $request->event_month, $actualEndDay, $endHours, $endMinutes, $endSeconds);
        }

        $event = Event::create([
            'title' => $request->title,
            'content' => $request->input('content'),
            'venue' => $request->Venue, 
            'day_range' => $request->day_range, 
            'start_time' => $startTime,
            'end_time' => $endTime,
            'user_id' => Auth::id(),
            'board_id' => 1, 
        ]);

        return response()->json([
            'status' => 'success', 
            'message' => 'Event created successfully',
            'event' => $event
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['status' => 'error', 'message' => 'Event not found'], 404);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'event_month' => 'sometimes|required|integer|min:1|max:12',
            'event_year' => 'sometimes|required|integer',
            'Venue' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'time' => 'sometimes|required|string',
            'end_time' => 'nullable|string', 
            'start_day' => 'sometimes|required|integer|min:1|max:31',
            'end_day' => 'nullable|integer|min:1|max:31',
        ]);

        if ($request->has('title')) $event->title = $request->title;
        if ($request->has('content')) $event->content = $request->input('content');
        if ($request->has('Venue')) $event->venue = $request->Venue;
        if ($request->has('day_range')) $event->day_range = $request->day_range; 
        
        if ($request->has('start_day') && $request->has('event_month') && $request->has('event_year') && $request->has('time')) {
            $timeParts = explode(':', $request->time);
            $startTime = Carbon::create($request->event_year, $request->event_month, $request->start_day, (int)$timeParts[0], (int)$timeParts[1], 0);
            
            $endTime = null;
            $actualEndDay = $request->end_day ?: ($request->filled('end_time') ? $request->start_day : null);

            if (!is_null($actualEndDay)) {
                $endHours = 23; $endMinutes = 59;
                if ($request->filled('end_time')) {
                    $endTimeParts = explode(':', $request->end_time);
                    $endHours = (int) $endTimeParts[0];
                    $endMinutes = (int) $endTimeParts[1];
                }
                $endTime = Carbon::create($request->event_year, $request->event_month, $actualEndDay, $endHours, $endMinutes, 0);
            }

            $event->start_time = $startTime;
            $event->end_time = $endTime;
        }

        $event->save();

        return response()->json([
            'status' => 'success', 
            'message' => 'Event updated successfully',
            'event' => $event
        ]);
    }

    public function destroy($id)
    {
        $event = Event::find($id);
        if (!$event) {
            return response()->json(['status' => 'error', 'message' => 'Event not found'], 404);
        }
        $event->delete();
        return response()->json(['status' => 'success', 'message' => 'Event deleted successfully']);
    }
}