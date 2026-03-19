<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
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
                'profile_picture' => $user->profile && $user->profile->profile_picture 
                    ? asset('storage/' . $user->profile->profile_picture) 
                    : null,
            ]
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
{
    $validated = $request->validate([
        'current_password' => ['required', 'string'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    $user = $request->user();

    // Verify the current password matches the database
    if (!Hash::check($validated['current_password'], $user->password)) {
        throw ValidationException::withMessages([
            'current_password' => 'The provided password does not match your current password.'
        ]);
    }

    // Update the password
    $user->update([
        'password' => Hash::make($validated['password']),
    ]);

    return response()->json([
        'message' => 'Password updated successfully.'
    ]);
}
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:table_users,email,' . $user->getKey() . ',user_id',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $profilePictureUrl = null;
        if ($request->hasFile('profile_picture')) {
            
            if ($user->profile && $user->profile->profile_picture) {
                Storage::disk('public')->delete($user->profile->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profiles', 'public');

            $user->profile()->updateOrCreate(
                ['user_id' => $user->getKey()],
                ['profile_picture' => $path]
            );

            $profilePictureUrl = asset('storage/' . $path);
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'profile_picture' => $profilePictureUrl
        ]);
    }
}