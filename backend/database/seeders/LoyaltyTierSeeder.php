<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyBenefit;

class LoyaltyTierSeeder extends Seeder
{
    public function run(): void
    {
        // Silver Tier
        $silver = LoyaltyTier::create([
            'name' => 'Silver',
            'slug' => 'silver',
            'min_points' => 0,
            'max_points' => 999,
            'discount_percentage' => 5.00,
            'points_multiplier' => 1.00,
            'priority_booking' => false,
            'badge_color' => '#C0C0C0',
            'icon' => '🥈',
            'order' => 1,
            'is_active' => true,
            'benefits' => [
                'Welcome bonus: 100 points',
                'Earn 1 point per $1 spent',
                '5% discount on bookings',
                'Birthday bonus: 100 points',
            ],
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $silver->id,
            'benefit_type' => 'discount',
            'name' => '5% Discount',
            'description' => 'Get 5% off on all bookings',
            'value' => '5%',
            'icon' => '💰',
            'order' => 1,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $silver->id,
            'benefit_type' => 'early_access',
            'name' => 'Early Access',
            'description' => 'Early access to new properties',
            'value' => 'yes',
            'icon' => '⚡',
            'order' => 2,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $silver->id,
            'benefit_type' => 'other',
            'name' => 'Birthday Bonus',
            'description' => 'Get 100 bonus points on your birthday',
            'value' => '100 points',
            'icon' => '🎂',
            'order' => 3,
            'is_active' => true,
        ]);

        // Gold Tier
        $gold = LoyaltyTier::create([
            'name' => 'Gold',
            'slug' => 'gold',
            'min_points' => 1000,
            'max_points' => 4999,
            'discount_percentage' => 10.00,
            'points_multiplier' => 1.50,
            'priority_booking' => true,
            'badge_color' => '#FFD700',
            'icon' => '🥇',
            'order' => 2,
            'is_active' => true,
            'benefits' => [
                'Earn 1.5x points per $1 spent',
                '10% discount on bookings',
                'Priority booking',
                'Priority support',
                'Free cancellation up to 24h before',
                'Birthday bonus: 250 points',
            ],
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $gold->id,
            'benefit_type' => 'discount',
            'name' => '10% Discount',
            'description' => 'Get 10% off on all bookings',
            'value' => '10%',
            'icon' => '💰',
            'order' => 1,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $gold->id,
            'benefit_type' => 'priority_support',
            'name' => 'Priority Support',
            'description' => '24/7 priority customer support',
            'value' => '24/7',
            'icon' => '🎧',
            'order' => 2,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $gold->id,
            'benefit_type' => 'free_cancellation',
            'name' => 'Free Cancellation',
            'description' => 'Cancel free up to 24 hours before check-in',
            'value' => '24h',
            'icon' => '🔄',
            'order' => 3,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $gold->id,
            'benefit_type' => 'other',
            'name' => '1.5x Points',
            'description' => 'Earn 1.5 points for every $1 spent',
            'value' => '1.5x',
            'icon' => '⭐',
            'order' => 4,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $gold->id,
            'benefit_type' => 'other',
            'name' => 'Birthday Bonus',
            'description' => 'Get 250 bonus points on your birthday',
            'value' => '250 points',
            'icon' => '🎂',
            'order' => 5,
            'is_active' => true,
        ]);

        // Platinum Tier
        $platinum = LoyaltyTier::create([
            'name' => 'Platinum',
            'slug' => 'platinum',
            'min_points' => 5000,
            'max_points' => null, // Unlimited
            'discount_percentage' => 15.00,
            'points_multiplier' => 2.00,
            'priority_booking' => true,
            'badge_color' => '#E5E4E2',
            'icon' => '💎',
            'order' => 3,
            'is_active' => true,
            'benefits' => [
                'Earn 2x points per $1 spent',
                '15% discount on bookings',
                'Priority booking',
                'Personal concierge service',
                'Free cancellation up to 48h before',
                'Access to exclusive properties',
                'Airport pickup service',
                'Late checkout (subject to availability)',
                'Welcome gift on arrival',
                'Birthday bonus: 500 points',
            ],
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $platinum->id,
            'benefit_type' => 'discount',
            'name' => '15% Discount',
            'description' => 'Get 15% off on all bookings',
            'value' => '15%',
            'icon' => '💰',
            'order' => 1,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $platinum->id,
            'benefit_type' => 'personal_concierge',
            'name' => 'Personal Concierge',
            'description' => 'Dedicated concierge for all your needs',
            'value' => 'yes',
            'icon' => '👔',
            'order' => 2,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $platinum->id,
            'benefit_type' => 'exclusive_properties',
            'name' => 'Exclusive Properties',
            'description' => 'Access to VIP and exclusive properties',
            'value' => 'yes',
            'icon' => '🏰',
            'order' => 3,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $platinum->id,
            'benefit_type' => 'airport_pickup',
            'name' => 'Airport Pickup',
            'description' => 'Complimentary airport pickup service',
            'value' => 'free',
            'icon' => '✈️',
            'order' => 4,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $platinum->id,
            'benefit_type' => 'late_checkout',
            'name' => 'Late Checkout',
            'description' => 'Late checkout available (subject to availability)',
            'value' => '4pm',
            'icon' => '🕐',
            'order' => 5,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $platinum->id,
            'benefit_type' => 'welcome_gift',
            'name' => 'Welcome Gift',
            'description' => 'Receive a welcome gift upon arrival',
            'value' => 'yes',
            'icon' => '🎁',
            'order' => 6,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $platinum->id,
            'benefit_type' => 'free_cancellation',
            'name' => 'Free Cancellation',
            'description' => 'Cancel free up to 48 hours before check-in',
            'value' => '48h',
            'icon' => '🔄',
            'order' => 7,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $platinum->id,
            'benefit_type' => 'other',
            'name' => '2x Points',
            'description' => 'Earn 2 points for every $1 spent',
            'value' => '2x',
            'icon' => '⭐',
            'order' => 8,
            'is_active' => true,
        ]);

        LoyaltyBenefit::create([
            'tier_id' => $platinum->id,
            'benefit_type' => 'other',
            'name' => 'Birthday Bonus',
            'description' => 'Get 500 bonus points on your birthday',
            'value' => '500 points',
            'icon' => '🎂',
            'order' => 9,
            'is_active' => true,
        ]);
    }
}

