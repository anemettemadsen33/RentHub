# RentHub Backend - Laravel 12 + Filament v4

## Overview

The RentHub backend is built with Laravel 12 and Filament v4, providing a robust REST API and powerful admin panel for managing a comprehensive property rental platform supporting both Long Term and Short Term rentals.

## Technology Stack

- **Framework**: Laravel 12
- **Admin Panel**: Filament v4
- **Authentication**: Laravel Sanctum + Socialite (Google, Facebook, GitHub)
- **Database**: MySQL 8 / PostgreSQL 16 / SQLite (development)
- **Cache & Queue**: Redis with Predis
- **Search**: Meilisearch / Algolia
- **Storage**: Laravel Filesystems (S3, local)
- **PDF Generation**: DomPDF
- **Excel Export**: Maatwebsite Excel
- **Translations**: Spatie Translatable
- **Permissions**: Spatie Laravel Permission

## Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- MySQL 8+ / PostgreSQL 16+ (or SQLite for development)
- Redis 6+ (optional but recommended)
- Meilisearch (optional, for advanced search)

## Installation

### 1. Install Dependencies

```bash
cd backend
composer install
```

### 2. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Configure your `.env` file with database, cache, and service credentials:

```env
# Application
APP_NAME=RentHub
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=renthub
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Meilisearch
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=

# AWS S3 (optional)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

# OAuth
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=

FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URI=
```

### 3. Database Setup

```bash
# Create database
php artisan db:create

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed
```

### 4. Create Admin User

```bash
php artisan make:filament-user
```

Follow the prompts to create your first admin user.

## Project Structure

```
backend/
├── app/
│   ├── Console/         # Artisan commands
│   ├── Exports/         # Excel export classes
│   ├── Filament/        # Filament admin panel
│   │   ├── Pages/       # Custom admin pages
│   │   ├── Resources/   # CRUD resources
│   │   └── Widgets/     # Dashboard widgets
│   ├── Http/
│   │   ├── Controllers/ # API controllers
│   │   ├── Middleware/  # Custom middleware
│   │   └── Requests/    # Form requests
│   ├── Jobs/            # Queue jobs
│   ├── Mail/            # Email templates
│   ├── Models/          # Eloquent models
│   ├── Notifications/   # Notification classes
│   ├── Observers/       # Model observers
│   ├── Policies/        # Authorization policies
│   ├── Providers/       # Service providers
│   └── Services/        # Business logic services
├── config/              # Configuration files
├── database/
│   ├── factories/       # Model factories
│   ├── migrations/      # Database migrations
│   └── seeders/         # Database seeders
├── routes/
│   ├── api.php          # API routes
│   ├── web.php          # Web routes
│   └── console.php      # Console commands
├── storage/
│   ├── app/             # Application files
│   ├── framework/       # Framework files
│   └── logs/            # Log files
└── tests/
    ├── Feature/         # Feature tests
    └── Unit/            # Unit tests
```

## Key Models

### User Management
- **User** - Users with roles (admin, owner, guest)
- **Role** - User roles
- **Permission** - User permissions
- **TwoFactorAuth** - 2FA settings
- **UserVerification** - Email/phone verification

### Property Management
- **Property** - Property listings
- **Amenity** - Property amenities
- **BlockedDate** - Unavailable dates
- **PropertyVerification** - Property verification status
- **PropertyComparison** - Property comparisons

### Booking System
- **Booking** - Reservations
- **BookingInsurance** - Booking insurance
- **LongTermRental** - Long-term rental contracts
- **RentPayment** - Rent payments

### Payment Processing
- **Payment** - Payment transactions
- **Payout** - Owner payouts
- **Invoice** - Invoice generation

### Communication
- **Message** - User messages
- **Conversation** - Message threads
- **MessageTemplate** - Auto-response templates
- **AutoResponse** - Automated messages

### Reviews & Ratings
- **Review** - Property reviews
- **ReviewResponse** - Owner responses
- **ReviewHelpfulVote** - Helpful votes

### Advanced Features
- **PricePrediction** - AI price predictions
- **PriceSuggestion** - Dynamic pricing
- **OccupancyPrediction** - ML occupancy forecasts
- **PropertyRecommendation** - AI recommendations

## API Routes

### Authentication
```
POST   /api/auth/register          # User registration
POST   /api/auth/login             # User login
POST   /api/auth/logout            # User logout
POST   /api/auth/refresh           # Refresh token
POST   /api/auth/forgot-password   # Password reset
POST   /api/auth/verify-email      # Email verification
```

### Properties
```
GET    /api/properties             # List properties
POST   /api/properties             # Create property
GET    /api/properties/{id}        # Get property
PUT    /api/properties/{id}        # Update property
DELETE /api/properties/{id}        # Delete property
GET    /api/properties/search      # Search properties
```

### Bookings
```
GET    /api/bookings               # List bookings
POST   /api/bookings               # Create booking
GET    /api/bookings/{id}          # Get booking
PUT    /api/bookings/{id}          # Update booking
DELETE /api/bookings/{id}          # Cancel booking
```

### Payments
```
GET    /api/payments               # List payments
POST   /api/payments               # Process payment
GET    /api/payments/{id}          # Get payment
POST   /api/payments/refund        # Refund payment
```

### Messages
```
GET    /api/messages               # List messages
POST   /api/messages               # Send message
GET    /api/messages/{id}          # Get message
GET    /api/conversations          # List conversations
```

### Reviews
```
GET    /api/reviews                # List reviews
POST   /api/reviews                # Create review
GET    /api/reviews/{id}           # Get review
PUT    /api/reviews/{id}           # Update review
DELETE /api/reviews/{id}           # Delete review
```

For complete API documentation, see the OpenAPI specification at `openapi.yaml`.

## Filament Admin Panel

Access the admin panel at `/admin` after creating an admin user.

### Dashboard
- Revenue charts
- Booking statistics
- Occupancy rates
- Recent activity

### Resources
- **Users** - User management
- **Properties** - Property CRUD
- **Bookings** - Reservation management
- **Payments** - Payment tracking
- **Reviews** - Review moderation
- **Messages** - Message monitoring

### Widgets
- Revenue Chart
- Occupancy Rate
- Recent Bookings
- Top Properties
- User Statistics

## Advanced Features

### Dynamic Pricing
Automatically adjust prices based on:
- Season
- Demand
- Duration
- Special events
- Competitor pricing

```php
use App\Services\PricingService;

$price = PricingService::calculatePrice($property, $checkIn, $checkOut);
```

### Search with Meilisearch

Properties are automatically indexed for fast, typo-tolerant search:

```bash
# Import all properties to Meilisearch
php artisan scout:import "App\Models\Property"
```

### Multi-Language Support

Models support multiple languages using Spatie Translatable:

```php
$property->setTranslation('title', 'en', 'Luxury Apartment');
$property->setTranslation('title', 'ro', 'Apartament de Lux');
$property->save();
```

### Real-time Features

WebSocket support for:
- Live chat
- Booking notifications
- Price updates

### Calendar Sync

Export bookings to iCal format:

```bash
GET /api/properties/{id}/calendar.ics
```

## Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Run With Coverage
```bash
php artisan test --coverage
```

## Code Quality

### Lint Code with Pint
```bash
./vendor/bin/pint
```

### Static Analysis
```bash
./vendor/bin/phpstan analyse
```

## Queue Workers

### Start Queue Worker
```bash
php artisan queue:work
```

### Process Specific Queue
```bash
php artisan queue:work --queue=emails,notifications
```

### Horizon (for Redis)
```bash
php artisan horizon
```

## Scheduled Tasks

Tasks are defined in `app/Console/Kernel.php`:

- Clean expired bookings (daily)
- Send booking reminders (hourly)
- Generate reports (daily)
- Update exchange rates (hourly)
- Process pending payouts (daily)

Run scheduler:
```bash
php artisan schedule:work
```

## Deployment

### Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components
```

### Run Migrations
```bash
php artisan migrate --force
```

### Clear Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Performance Optimization

### Database Indexing
All critical queries have proper indexes defined in migrations.

### Eager Loading
Use eager loading to prevent N+1 queries:

```php
Property::with(['amenities', 'owner', 'reviews'])->get();
```

### Caching
Implement caching for frequently accessed data:

```php
Cache::remember('popular-properties', 3600, function () {
    return Property::popular()->get();
});
```

## Security

### Rate Limiting
API endpoints are rate-limited:
- Anonymous: 60 requests/minute
- Authenticated: 120 requests/minute

### CORS Configuration
CORS is configured in `config/cors.php`

### Sanctum Tokens
API tokens expire after 7 days by default.

### SQL Injection Prevention
All queries use parameter binding.

### XSS Protection
Output is escaped automatically.

## Troubleshooting

### Clear All Caches
```bash
php artisan optimize:clear
```

### Regenerate Autoload
```bash
composer dump-autoload
```

### Fix Permissions
```bash
chmod -R 775 storage bootstrap/cache
```

### Database Connection Issues
Check database credentials in `.env` and ensure the database exists.

### Scout Issues
```bash
php artisan scout:flush "App\Models\Property"
php artisan scout:import "App\Models\Property"
```

## Support

For issues and questions:
- GitHub Issues: https://github.com/anemettemadsen33/RentHub/issues
- Documentation: See docs/ directory

## License

MIT License - see LICENSE file for details.
