<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:newsletter_subscribers,email',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $token = Str::random(32);
        
        $subscriber = NewsletterSubscriber::create([
            'email' => $request->email,
            'name' => $request->name,
            'status' => 'pending',
            'token' => $token,
            'subscribed_at' => now(),
        ]);

        // Send confirmation email
        $this->sendConfirmationEmail($subscriber);

        return response()->json([
            'message' => 'Please check your email to confirm your subscription.',
        ], 201);
    }

    /**
     * Confirm newsletter subscription
     */
    public function confirm(string $token)
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if (!$subscriber) {
            return response()->json([
                'error' => 'Invalid confirmation token.',
            ], 404);
        }

        if ($subscriber->status === 'active') {
            return response()->json([
                'message' => 'Email already confirmed.',
            ]);
        }

        $subscriber->update([
            'status' => 'active',
            'confirmed_at' => now(),
        ]);

        return response()->json([
            'message' => 'Email confirmed! You are now subscribed to our newsletter.',
        ]);
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $subscriber = NewsletterSubscriber::where('email', $request->email)->first();

        if (!$subscriber) {
            return response()->json([
                'error' => 'Email not found in our system.',
            ], 404);
        }

        $subscriber->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        return response()->json([
            'message' => 'You have been unsubscribed from our newsletter.',
        ]);
    }

    /**
     * Get subscriber preferences
     */
    public function getPreferences(string $email)
    {
        $subscriber = NewsletterSubscriber::where('email', $email)->first();

        if (!$subscriber) {
            return response()->json([
                'error' => 'Email not found.',
            ], 404);
        }

        return response()->json([
            'preferences' => $subscriber->preferences ?? [
                'weekly_digest' => true,
                'new_properties' => true,
                'special_offers' => true,
                'tips_and_guides' => false,
            ],
        ]);
    }

    /**
     * Update subscriber preferences
     */
    public function updatePreferences(Request $request, string $email)
    {
        $subscriber = NewsletterSubscriber::where('email', $email)->first();

        if (!$subscriber) {
            return response()->json([
                'error' => 'Email not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'weekly_digest' => 'boolean',
            'new_properties' => 'boolean',
            'special_offers' => 'boolean',
            'tips_and_guides' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $subscriber->update([
            'preferences' => $request->all(),
        ]);

        return response()->json([
            'message' => 'Preferences updated successfully.',
        ]);
    }

    /**
     * Get active subscribers (admin only)
     */
    public function getSubscribers(Request $request)
    {
        $this->authorize('viewAny', NewsletterSubscriber::class);

        $subscribers = NewsletterSubscriber::where('status', 'active')
            ->when($request->search, function ($query, $search) {
                return $query->where('email', 'LIKE', "%{$search}%")
                    ->orWhere('name', 'LIKE', "%{$search}%");
            })
            ->paginate(50);

        return response()->json($subscribers);
    }

    /**
     * Send newsletter campaign (admin only)
     */
    public function sendCampaign(Request $request)
    {
        $this->authorize('create', NewsletterSubscriber::class);

        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'segment' => 'nullable|in:all,new_properties,special_offers,weekly_digest',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $subscribers = NewsletterSubscriber::where('status', 'active');

        // Filter by segment/preferences
        if ($request->segment && $request->segment !== 'all') {
            $subscribers->where("preferences->{$request->segment}", true);
        }

        $count = 0;
        foreach ($subscribers->get() as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(
                    new \App\Mail\NewsletterCampaign(
                        $request->subject,
                        $request->content,
                        $subscriber
                    )
                );
                $count++;
            } catch (\Exception $e) {
                \Log::error('Failed to send newsletter to ' . $subscriber->email, [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return response()->json([
            'message' => "Newsletter sent to {$count} subscribers.",
        ]);
    }

    /**
     * Get newsletter statistics (admin only)
     */
    public function getStats()
    {
        $this->authorize('viewAny', NewsletterSubscriber::class);

        $stats = [
            'total' => NewsletterSubscriber::count(),
            'active' => NewsletterSubscriber::where('status', 'active')->count(),
            'pending' => NewsletterSubscriber::where('status', 'pending')->count(),
            'unsubscribed' => NewsletterSubscriber::where('status', 'unsubscribed')->count(),
            'recent_signups' => NewsletterSubscriber::where('subscribed_at', '>=', now()->subDays(7))->count(),
            'this_month' => NewsletterSubscriber::whereMonth('subscribed_at', now()->month)->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Send confirmation email
     */
    private function sendConfirmationEmail(NewsletterSubscriber $subscriber)
    {
        $confirmUrl = url("/newsletter/confirm/{$subscriber->token}");
        
        Mail::to($subscriber->email)->send(
            new \App\Mail\NewsletterConfirmation($subscriber, $confirmUrl)
        );
    }
}
