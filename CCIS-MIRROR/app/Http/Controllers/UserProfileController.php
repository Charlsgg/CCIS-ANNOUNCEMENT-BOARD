<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class UserProfileController extends Controller
{
    /**
     * Fetch the user profile data.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile');

        return response()->json([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'profile_picture' => $user->profile ? $user->profile->profile_picture : null,
            ]
        ]);
    }

    /**
     * Update the user password.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'The provided password does not match your current password.'
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Password updated successfully.']);
    }

    /**
     * Update profile info and upload avatar to Supabase 'avatars' bucket.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required', 
                'email', 
                'max:255', 
                Rule::unique('table_users', 'email')->ignore($user->user_id, 'user_id')
            ],
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            $profileData = [];

            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');

                if ($user->profile && $user->profile->profile_picture) {
                    $this->deleteOldAvatar($user->profile->profile_picture);
                }

                // Store using the 'avatars' disk we defined in filesystems.php
                $path = $file->store('user-avatars', 'avatars');

                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $disk = Storage::disk('avatars');
                $profileData['profile_picture'] = $disk->url($path);
            }

            $user->profile()->updateOrCreate(
                ['user_id' => $user->user_id],
                $profileData
            );

            DB::commit();

            return response()->json([
                'message' => 'Profile updated successfully.',
                'profile_picture' => $profileData['profile_picture'] ?? ($user->profile ? $user->profile->profile_picture : null)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Profile Update Error: " . $e->getMessage());
            return response()->json([
                'message' => 'Failed to update profile.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to clean up storage when an avatar is replaced.
     */
    protected function deleteOldAvatar(string $url): void
    {
        try {
            $path = parse_url($url, PHP_URL_PATH);
            // Splitting by the bucket name to get the relative path
            $segments = explode('/public/avatars/', $path);
            
            if (isset($segments[1])) {
                Storage::disk('avatars')->delete($segments[1]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to delete old avatar: " . $e->getMessage());
        }
    }
}