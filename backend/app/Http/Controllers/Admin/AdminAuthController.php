<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminAuthController extends Controller
{
    /**
     * Show admin login form
     */
    public function showLoginForm()
    {
        return view('admin.login');
    }

    /**
     * Handle admin login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if user is admin
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user || !$user->is_admin) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records or you do not have admin access.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            return redirect('/admin/login');
        }

        $stats = [
            'total_users' => User::count(),
            'total_properties' => \App\Models\Property::count(),
            'total_bookings' => \App\Models\Booking::count(),
            'total_revenue' => \App\Models\Payment::where('status', 'completed')->sum('amount'),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
