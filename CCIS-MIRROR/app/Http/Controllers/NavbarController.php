<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Don't forget to import this!

class NavbarController extends Controller
{
    public function getUserData(Request $request)
    {
        $user = $request->user()->load('profile');

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Get the raw path from the database
        $rawPath = $user->profile?->profile_picture;
         $avatarUrl = null;
        if ($user->profile && $user->profile->profile_picture) {
            $avatarUrl = str_starts_with($user->profile->profile_picture, 'http')
                ? $user->profile->profile_picture
                : Storage::url($user->profile->profile_picture);
        }

        return response()->json([
            'name' => $user->name,
            'profile_picture' => $avatarUrl, 
        ]);
    }
}