<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use App\Repositories\CachedUserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class OptimizedAuthController extends Controller
{
    protected CachedUserRepository $userRepository;
    
    public function __construct(CachedUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
    
    /**
     * Register a new user with caching optimization
     */
    public function register(Request $request)
    {
        $startTime = microtime(true);
        Log::info('E2E register start', ['ts' => $startTime]);
        
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['nullable', 'in:owner,tenant'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role ?? 'tenant',
        ];

        // Use cached repository to create user
        $user = $this->userRepository->create($userData);

        // Send email verification (skip in testing env to avoid slowing E2E / requiring mailer)
        if (config('app.env') !== 'testing') {
            event(new Registered($user));
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;
        
        $endTime = microtime(true);
        Log::info('E2E register end', [
            'user_id' => $user->id, 
            'ts' => $endTime,
            'duration_ms' => round(($endTime - $startTime) * 1000, 2)
        ]);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'Registration successful! Please check your email to verify your account.',
            'optimized' => true,
        ], 201);
    }

    /**
     * Login user with caching optimization
     */
    public function login(Request $request)
    {
        $startTime = microtime(true);
        Log::info('E2E login start', ['ts' => $startTime]);
        
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Use cached repository to find user
        $user = $this->userRepository->findByEmail($request->email);
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Check if 2FA is enabled
        if ($user->two_factor_enabled) {
            // Generate and send 2FA code
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $user->update([
                'two_factor_code' => $code,
                'two_factor_code_expires_at' => now()->addMinutes(10),
            ]);

            // Send 2FA code via email
            Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code));

            // Clear cache after update
            $this->userRepository->clearUserCache($user);

            $endTime = microtime(true);
            Log::info('E2E login 2FA required', [
                'user_id' => $user->id,
                'ts' => $endTime,
                'duration_ms' => round(($endTime - $startTime) * 1000, 2)
            ]);

            return response()->json([
                'success' => true,
                'message' => '2FA code sent to your email',
                'requires_2fa' => true,
                'code' => config('app.env') === 'local' ? $code : null, // Development only
            ]);
        }

        // Create token and update cache
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // Update last_used_at for the token
        $user->currentAccessToken()->update(['last_used_at' => now()]);
        
        // Warm cache with updated user data
        $this->userRepository->warmUserCache($user);
        
        $endTime = microtime(true);
        Log::info('E2E login success', [
            'user_id' => $user->id,
            'ts' => $endTime,
            'duration_ms' => round(($endTime - $startTime) * 1000, 2)
        ]);

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token,
            'message' => 'Login successful',
            'optimized' => true,
        ]);
    }

    /**
     * Logout user with cache cleanup
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
            
            // Clear user cache on logout
            $this->userRepository->clearUserCache($user);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'optimized' => true,
        ]);
    }

    /**
     * Get authenticated user with cache
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        // Use cached version if available
        $cachedUser = $this->userRepository->findById($user->id);
        
        return response()->json([
            'success' => true,
            'data' => $cachedUser ?: $user,
            'cached' => $cachedUser !== null,
        ]);
    }

    /**
     * Verify email with cache update
     */
    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link',
            ], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified',
            ]);
        }

        $user->markEmailAsVerified();
        
        // Update cache after verification
        $this->userRepository->warmUserCache($user);

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully',
        ]);
    }

    /**
     * Resend email verification
     */
    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified',
            ], 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent',
        ]);
    }
}