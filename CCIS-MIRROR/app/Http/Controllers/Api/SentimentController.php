<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sentiment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SentimentController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate the incoming request
        $request->validate([
            'sentiment' => 'required|string|in:Stressed,Tired,Okay,Productive,Great'
        ]);

        $ip = $request->ip();
        $cacheKey = 'vibe_cooldown_' . $ip;

        // 2. Enforce the 10-second cooldown
        if (Cache::has($cacheKey)) {
            return response()->json([
                'error' => 'Cooldown active. Please wait 10 seconds.'
            ], 429); // 429 Too Many Requests
        }

        // 3. Save the vibe permanently to the database
        Sentiment::create([
            'sentiment' => $request->sentiment,
            'ip_address' => $ip
        ]);

        // 4. Lock the user out for 10 seconds
        Cache::put($cacheKey, true, 10);

        // 5. Calculate today's most common campus vibe
        $mostCommon = Sentiment::whereDate('created_at', Carbon::today())
            ->select('sentiment', DB::raw('count(*) as total'))
            ->groupBy('sentiment')
            ->orderByDesc('total')
            ->first();

        return response()->json([
            'message' => 'Vibe recorded successfully!',
            'most_common_vibe' => $mostCommon ? $mostCommon->sentiment : $request->sentiment
        ], 200);
    }
}