<?php

namespace App\Enums;

enum ConciergeServiceType: string
{
    case AIRPORT_PICKUP = 'airport_pickup';
    case GROCERY_DELIVERY = 'grocery_delivery';
    case LOCAL_EXPERIENCES = 'local_experiences';
    case PERSONAL_CHEF = 'personal_chef';
    case SPA_SERVICES = 'spa_services';

    public function label(): string
    {
        return match ($this) {
            self::AIRPORT_PICKUP => 'Airport Pickup',
            self::GROCERY_DELIVERY => 'Grocery Delivery',
            self::LOCAL_EXPERIENCES => 'Local Experiences',
            self::PERSONAL_CHEF => 'Personal Chef',
            self::SPA_SERVICES => 'Spa Services',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::AIRPORT_PICKUP => 'heroicon-o-truck',
            self::GROCERY_DELIVERY => 'heroicon-o-shopping-bag',
            self::LOCAL_EXPERIENCES => 'heroicon-o-map',
            self::PERSONAL_CHEF => 'heroicon-o-cake',
            self::SPA_SERVICES => 'heroicon-o-sparkles',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::AIRPORT_PICKUP => 'Professional airport transfer service',
            self::GROCERY_DELIVERY => 'Fresh groceries delivered to your property',
            self::LOCAL_EXPERIENCES => 'Guided tours and local activities',
            self::PERSONAL_CHEF => 'Private chef services for your stay',
            self::SPA_SERVICES => 'In-property spa and wellness treatments',
        };
    }
}
