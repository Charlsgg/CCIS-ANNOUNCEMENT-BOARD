<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validate the incoming JSON
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
            'position' => ['required', 'string'],
        ]);

        // 2. Map the data to your database column names
        $credentials = [
            'email'     => $request->email,
            'password'  => $request->password,
            'user_type' => $request->position, 
        ];

        $remember = $request->boolean('remember');

        // 3. Attempt to log the user in
        if (Auth::attempt($credentials, $remember)) {
            
            // Protect against session fixation
            $request->session()->regenerate();

            // 4. Dynamically determine the redirect route based on role
            // (Using PHP 8's match expression for clean syntax)
            $redirectUrl = match (Auth::user()->user_type) {
                'it_instructor' => '/it/dashboard',
                'cs_instructor' => '/cs/dashboard',
                'is_instructor' => '/is/dashboard',
                'lsg_officer'   => '/lsg/dashboard',
            };

            // Return success JSON with the correct redirect URL
            return response()->json([
                'message'  => 'Logged in successfully.',
                'redirect' => $redirectUrl
            ], 200);
        }

        // 5. Return 401 if login fails
        return response()->json([
            'message' => 'Invalid email, password, or position.'
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // If the frontend is making an API/Axios request, return JSON
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Logged out successfully.'], 200);
        }

        // Fallback for standard web requests
        return redirect('/');
    }
}