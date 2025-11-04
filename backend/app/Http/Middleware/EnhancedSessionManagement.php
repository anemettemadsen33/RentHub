<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnhancedSessionManagement
{
    private array $config;
    
    public function __construct()
    {
        $this->config = config('security.session');
    }
    
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }
        
        // Check idle timeout
        if ($this->isSessionIdle($request)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('message', 'Session expired due to inactivity');
        }
        
        // Update last activity
        $request->session()->put('last_activity', now()->timestamp);
        
        // Device fingerprinting
        if ($this->config['device_fingerprinting']) {
            $this->validateDeviceFingerprint($request);
        }
        
        // Regenerate session ID periodically
        if ($this->shouldRegenerateSession($request)) {
            $request->session()->regenerate();
        }
        
        return $next($request);
    }
    
    private function isSessionIdle(Request $request): bool
    {
        $lastActivity = $request->session()->get('last_activity');
        
        if (!$lastActivity) {
            return false;
        }
        
        $idleTimeout = $this->config['idle_timeout'] * 60; // Convert to seconds
        
        return (now()->timestamp - $lastActivity) > $idleTimeout;
    }
    
    private function validateDeviceFingerprint(Request $request): void
    {
        $currentFingerprint = $this->generateFingerprint($request);
        $storedFingerprint = $request->session()->get('device_fingerprint');
        
        if (!$storedFingerprint) {
            $request->session()->put('device_fingerprint', $currentFingerprint);
            return;
        }
        
        if ($currentFingerprint !== $storedFingerprint) {
            \Log::warning('Device fingerprint mismatch', [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            Auth::logout();
            $request->session()->invalidate();
            abort(403, 'Session validation failed');
        }
    }
    
    private function generateFingerprint(Request $request): string
    {
        $components = [
            $request->userAgent(),
            $request->ip(),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
        ];
        
        return hash('sha256', implode('|', $components));
    }
    
    private function shouldRegenerateSession(Request $request): bool
    {
        $lastRegeneration = $request->session()->get('last_regeneration');
        
        if (!$lastRegeneration) {
            $request->session()->put('last_regeneration', now()->timestamp);
            return false;
        }
        
        // Regenerate every 30 minutes
        if ((now()->timestamp - $lastRegeneration) > 1800) {
            $request->session()->put('last_regeneration', now()->timestamp);
            return true;
        }
        
        return false;
    }
}
