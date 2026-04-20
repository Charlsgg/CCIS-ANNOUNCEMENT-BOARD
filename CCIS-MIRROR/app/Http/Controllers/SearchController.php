<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class SearchController extends Controller
{
    public function globalSearch(Request $request): JsonResponse
    {
        $query = $request->input('q');
        if (!$query) return response()->json(['results' => []]);

        $terms = array_filter(explode(' ', $query));

        // Get and combine results using helper methods
        $results = collect()
            ->concat($this->searchAnnouncements($terms))
            ->concat($this->searchEvents($terms));

        return response()->json([
            'results' => $results->values()->all()
        ]);
    }

    /**
     * Search and format announcements from the database view.
     */
    private function searchAnnouncements(array $terms): Collection
    {
        return DB::table('all_announcements_view as av')
            ->leftJoin('user_profiles as up', 'av.author_id', '=', 'up.user_id')
            ->select('av.*', 'up.profile_picture as real_avatar')
            ->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->where('av.title', 'LIKE', "%{$term}%")
                      ->orWhere('av.content', 'LIKE', "%{$term}%");
                }
            })
            ->get()
            ->groupBy('announcement_id')
            ->take(5)
            ->map(function ($group) {
                $a = $group->first();
                $rawDate = Carbon::parse($a->announcement_date);

                return [
                    'id'            => 'ann_' . $a->announcement_id,
                    'type'          => 'Announcement',
                    'title'         => $a->title,
                    'description'   => Str::limit(strip_tags($a->content), 80),
                    'content'       => $a->content,
                    'author_name'   => $a->author_name,
                    'author_avatar' => $this->formatUrl($a->real_avatar),
                    'date'          => $rawDate->diffForHumans(),
                    'attachments'   => $group->whereNotNull('attachment_id')->map(fn($item) => [
                        'id'        => $item->attachment_id,
                        'file_path' => $this->formatUrl($item->file_path),
                        'file_type' => $item->file_type,
                    ])->values(),
                    'url'           => '/announcements-page?id=' . $a->announcement_id
                ];
            });
    }

    /**
     * Search and format events using the Event Model.
     */
    private function searchEvents(array $terms): Collection
    {
        return Event::query()
            ->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->where('title', 'LIKE', "%{$term}%")
                      ->orWhere('content', 'LIKE', "%{$term}%")
                      ->orWhere('venue', 'LIKE', "%{$term}%");
                }
            })
            ->limit(5)
            ->get()
            ->map(fn($e) => [
                'id'          => 'event_' . $e->event_id,
                'type'        => 'Event',
                'title'       => $e->title,
                'venue'       => $e->venue ?? 'TBA',
                'description' => $e->content,
                'start_time'  => $e->start_time,
                'end_time'    => $e->end_time,
                'url'         => '/events'
            ]);
    }

    /**
     * Helper to handle Storage URLs consistently.
     */
    private function formatUrl(?string $path): ?string
    {
        if (!$path) return null;
        return str_starts_with($path, 'http') ? $path : Storage::url($path);
    }
}