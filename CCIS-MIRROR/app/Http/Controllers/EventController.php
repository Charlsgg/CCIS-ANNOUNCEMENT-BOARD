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

    public function upcoming(Request $request)
    {
        $events = EventFilterView::where('start_time', '>=', now())
            ->orderBy('start_time', 'asc') 
            ->take(6) 
            ->get();

        return response()->json([
            'status' => 'success',
            'events' => $events
        ]);
    }

   public function store(Request $request)
    {
        // 1. Validate the incoming data
        $request->validate([
            'title' => 'required|string|max:255',
            'event_month' => 'required|integer|min:1|max:12',
            'event_year' => 'required|integer',
            'Venue' => 'required|string|max:255',
            'content' => 'required|string',
            'day_range' => 'required|string'
        ]);

        // NEW LOGIC: Parse ranges like "3-10" or single days like "3"
        $dayRangeStr = trim($request->day_range);
        if (str_contains($dayRangeStr, '-')) {
            $parts = explode('-', $dayRangeStr);
            $startDay = (int) trim($parts[0]);
            $endDay = (int) trim($parts[1]);
        } else {
            $startDay = (int) $dayRangeStr;
            $endDay = $startDay;
        }

        // Fallbacks in case parsing fails
        $startDay = $startDay ?: 1;
        $endDay = $endDay ?: $startDay;

        $startTime = Carbon::create($request->event_year, $request->event_month, $startDay, 8, 0, 0);
        // Set the end time to 11:59:59 PM of the end day
        $endTime = Carbon::create($request->event_year, $request->event_month, $endDay, 23, 59, 59);

        // 2. Save securely!
        $event = Event::create([
            'title' => $request->title,
            'content' => $request->input('content'),
            'venue' => $request->Venue, 
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
        // 1. Find the exact event
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        // 2. Validate the incoming data
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'event_month' => 'sometimes|required|integer|min:1|max:12',
            'event_year' => 'sometimes|required|integer',
            'Venue' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'day_range' => 'sometimes|required|string'
        ]);
        if ($request->has('title')) $event->title = $request->title;
        if ($request->has('content')) {
            $event->content = $request->input('content');
        }
        if ($request->has('Venue')) $event->venue = $request->Venue;
        if ($request->has('day_range') && $request->has('event_month') && $request->has('event_year')) {
        
            $dayRangeStr = trim($request->day_range);
            if (str_contains($dayRangeStr, '-')) {
                $parts = explode('-', $dayRangeStr);
                $startDay = (int) trim($parts[0]);
                $endDay = (int) trim($parts[1]);
            } else {
                $startDay = (int) $dayRangeStr;
                $endDay = $startDay;
            }

            $startDay = $startDay ?: 1;
            $endDay = $endDay ?: $startDay;

            $startTime = Carbon::create($request->event_year, $request->event_month, $startDay, 8, 0, 0);
            $endTime = Carbon::create($request->event_year, $request->event_month, $endDay, 23, 59, 59);

            $event->start_time = $startTime;
            $event->end_time = $endTime;
        }

        // 4. Save to database
        $event->save();

        return response()->json([
            'status' => 'success', 
            'message' => 'Event updated successfully',
            'event' => $event
        ]);
    }

    public function destroy($id)
    {
        // 1. Find the exact event
        $event = Event::find($id);

        if (!$event) {
            return response()->json([
                'status' => 'error',
                'message' => 'Event not found'
            ], 404);
        }

        // 2. Delete it
        $event->delete();

        return response()->json([
            'status' => 'success', 
            'message' => 'Event deleted successfully'
        ]);
    }
}