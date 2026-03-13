<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
            'position' => ['required', 'string'],
        ]);

        $credentials = [
            'email'     => $request->email,
            'password'  => $request->password,
            'user_type' => $request->position, 
        ];

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Added a default fallback to prevent Match Errors
            $redirectUrl = match (Auth::user()->user_type) {
                'it_instructor' => '/it/home-page',
                'cs_instructor' => '/cs/home-page',
                'is_instructor' => '/is/home-page',
                'lsg_officer'   => '/lsg/home-page',
                default         => '/announcements-board', 
            };

            return response()->json([
                'message'  => 'Logged in successfully.',
                'redirect' => $redirectUrl
            ], 200);
        }
    
        // Return 422 for consistency with how Vue handles validation/auth errors
        return response()->json([
            'message' => 'The provided credentials do not match our records.',
            'errors' => [
                'email' => ['Invalid email, password, or position.']
            ]
        ], 422);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:table_users'], // Ensure table name is correct
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'position' => ['required', 'string', 'in:it_instructor,is_instructor,cs_instructor,lsg_officer'],
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password), 
            'user_type' => $request->position, 
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $redirectUrl = match ($user->user_type) {
            'it_instructor' => '/it/home-page',
            'cs_instructor' => '/cs/home-page',
            'is_instructor' => '/is/home-page',
            'lsg_officer'   => '/lsg/home-page',
            default         => '/announcements-board',
        };

        return response()->json([
            'message'  => 'Account created successfully.',
            'redirect' => $redirectUrl
        ], 201);
    }
    public function resetPassword(Request $request)
    {
        // 1. Validate the input
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:table_users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        // 2. Attempt to reset the password
        // This will verify the token against the password_reset_tokens table
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();
            }
        );

        // 3. Return JSON response for Vue
        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status) // "Your password has been reset."
            ], 200);
        }

        // If something went wrong (e.g. token expired)
        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:table_users,email'], // Check if email exists first
        ], [
            'email.exists' => 'We can\'t find a user with that email address.'
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'status' => __($status) 
            ], 200);
        }

        // Handle cases where the broker fails even if validation passed
        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Logged out successfully.'], 200);
        }

        return redirect('/login');
    }
}