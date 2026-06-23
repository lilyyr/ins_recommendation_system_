<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLogin()
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            return $this->redirectBasedOnRole($user);
        }

        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt login
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            // Check if user is active
            if (!$user->isActive()) {
                Auth::logout();
                return back()->with('error', 'Your account has been deactivated.');
            }

            // Redirect based on role
            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Redirect user based on their role
     */
    protected function redirectBasedOnRole(User $user)
    {
        return match($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'agent' => redirect()->route('consultations.index'),
            'client' => redirect()->route('client.consultations'),
            default => redirect()->route('home'),
        };
    }
}
