<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Referral Program Configuration
    |--------------------------------------------------------------------------
    |
    | Configure referral rewards and settings for your referral program.
    |
    */

    // Referrer Rewards (earned when referred user completes first booking)
    'referrer_points' => env('REFERRAL_REFERRER_POINTS', 500),
    'referrer_amount' => env('REFERRAL_REFERRER_AMOUNT', 0),

    // Referred User Rewards (received immediately on registration)
    'referred_points' => env('REFERRAL_REFERRED_POINTS', 100),
    'referred_amount' => env('REFERRAL_REFERRED_AMOUNT', 10.00),

    // Referral Code Settings
    'code_length' => 8,
    'code_prefix' => env('REFERRAL_CODE_PREFIX', ''),
    'code_expiry_days' => env('REFERRAL_CODE_EXPIRY_DAYS', 30),

    // Requirements
    'min_booking_amount' => env('REFERRAL_MIN_BOOKING_AMOUNT', 50),
    'require_booking_completion' => env('REFERRAL_REQUIRE_BOOKING', true),

    // Limits
    'max_referrals_per_user' => env('REFERRAL_MAX_PER_USER', null), // null = unlimited
    'max_redemptions_per_code' => env('REFERRAL_MAX_REDEMPTIONS', null), // null = unlimited

    // Features
    'enable_referral_discount' => env('REFERRAL_ENABLE_DISCOUNT', true),
    'enable_referral_points' => env('REFERRAL_ENABLE_POINTS', true),
    'enable_leaderboard' => env('REFERRAL_ENABLE_LEADERBOARD', true),
];
