<?php

namespace App\Enums;

enum NavigationGroup: string
{
    case PAYMENTS = 'Payments';
    case PAYMENT_SETTINGS = 'Payment Settings';
    case PROPERTIES = 'Properties';
    case BOOKINGS = 'Bookings';
    case USERS = 'Users';
    case AI_SECURITY = 'AI & Security';
    case CONCIERGE = 'Concierge Services';
}
