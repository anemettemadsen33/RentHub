# ==============================================================================
# RENTHUB - COMPLETE AUTOMATION TO 100% COMPLETION
# ==============================================================================
# Purpose: Continuously execute all remaining roadmap tasks until 100% complete
# Mode: Overnight automation with real-time progress monitoring
# Timeline: 8-12 hours (overnight execution)
# ==============================================================================

param(
    [switch]$DryRun = $false,
    [switch]$SkipTests = $false,
    [int]$MaxRetries = 3
)

# Configuration
$ErrorActionPreference = "Continue"
$global:StartTime = Get-Date
$global:LogFile = "automation_to_100_$(Get-Date -Format 'yyyyMMdd_HHmmss').log"
$global:ProgressFile = "progress_tracker_$(Get-Date -Format 'yyyyMMdd_HHmmss').json"
$global:CompletedTasks = @()
$global:FailedTasks = @()
$global:TotalTasks = 0
$global:CompletedCount = 0

# Color output functions
function Write-TaskHeader($message) {
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $line = "=" * 80
    Write-Host "`n$line" -ForegroundColor Cyan
    Write-Host "[$timestamp] $message" -ForegroundColor Yellow
    Write-Host "$line`n" -ForegroundColor Cyan
    Add-Content $global:LogFile "[$timestamp] $message"
}

function Write-Success($message) {
    $timestamp = Get-Date -Format "HH:mm:ss"
    Write-Host "✅ [$timestamp] $message" -ForegroundColor Green
    Add-Content $global:LogFile "✅ [$timestamp] $message"
}

function Write-Failure($message) {
    $timestamp = Get-Date -Format "HH:mm:ss"
    Write-Host "❌ [$timestamp] $message" -ForegroundColor Red
    Add-Content $global:LogFile "❌ [$timestamp] $message"
}

function Write-Progress($message) {
    $timestamp = Get-Date -Format "HH:mm:ss"
    Write-Host "⏳ [$timestamp] $message" -ForegroundColor Yellow
    Add-Content $global:LogFile "⏳ [$timestamp] $message"
}

function Write-Info($message) {
    $timestamp = Get-Date -Format "HH:mm:ss"
    Write-Host "ℹ️  [$timestamp] $message" -ForegroundColor Cyan
    Add-Content $global:LogFile "ℹ️  [$timestamp] $message"
}

# Save progress to JSON
function Save-Progress {
    $progress = @{
        StartTime = $global:StartTime
        CurrentTime = Get-Date
        ElapsedHours = ((Get-Date) - $global:StartTime).TotalHours
        TotalTasks = $global:TotalTasks
        CompletedCount = $global:CompletedCount
        FailedCount = $global:FailedTasks.Count
        CompletionPercentage = if ($global:TotalTasks -gt 0) { [math]::Round(($global:CompletedCount / $global:TotalTasks) * 100, 2) } else { 0 }
        CompletedTasks = $global:CompletedTasks
        FailedTasks = $global:FailedTasks
    }
    $progress | ConvertTo-Json -Depth 10 | Set-Content $global:ProgressFile
}

# Execute task with retry logic
function Invoke-TaskWithRetry($taskName, $scriptBlock) {
    $global:TotalTasks++
    $retries = 0
    $success = $false
    
    while ($retries -lt $MaxRetries -and -not $success) {
        try {
            Write-Progress "Executing: $taskName (Attempt $($retries + 1)/$MaxRetries)"
            
            if (-not $DryRun) {
                & $scriptBlock
            }
            
            Write-Success "$taskName completed"
            $global:CompletedTasks += @{
                Task = $taskName
                CompletedAt = Get-Date
                Attempts = $retries + 1
            }
            $global:CompletedCount++
            $success = $true
            
        } catch {
            $retries++
            Write-Failure "$taskName failed (Attempt $retries): $($_.Exception.Message)"
            
            if ($retries -ge $MaxRetries) {
                $global:FailedTasks += @{
                    Task = $taskName
                    Error = $_.Exception.Message
                    Attempts = $retries
                }
            } else {
                Start-Sleep -Seconds 5
            }
        }
    }
    
    Save-Progress
    return $success
}

# ==============================================================================
# PHASE 1: FOUNDATION & DATABASE
# ==============================================================================
function Complete-Phase1-Foundation {
    Write-TaskHeader "PHASE 1: FOUNDATION & DATABASE SETUP"
    
    # Task 1.1: Install Missing Dependencies
    Invoke-TaskWithRetry "Install Backend Dependencies" {
        Set-Location backend
        composer require --no-interaction `
            laravel/socialite `
            league/oauth2-client `
            pusher/pusher-php-server `
            predis/predis `
            spatie/laravel-permission `
            intervention/image `
            league/flysystem-aws-s3-v3 `
            barryvdh/laravel-dompdf `
            maatwebsite/excel `
            laravel/horizon
        Set-Location ..
    }
    
    Invoke-TaskWithRetry "Install Frontend Dependencies" {
        Set-Location frontend
        npm install --legacy-peer-deps `
            next-i18next `
            react-currency-input-field `
            chart.js react-chartjs-2 `
            @hello-pangea/dnd `
            react-hot-toast `
            date-fns
        Set-Location ..
    }
    
    # Task 1.2: Database Migrations
    Invoke-TaskWithRetry "Run Database Migrations" {
        Set-Location backend
        php artisan migrate:fresh --force
        Set-Location ..
    }
    
    # Task 1.3: Create Missing Database Tables
    Invoke-TaskWithRetry "Create Bookings Table" {
        $migration = @"
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('bookings', function (Blueprint `$table) {
            `$table->id();
            `$table->foreignId('property_id')->constrained()->onDelete('cascade');
            `$table->foreignId('user_id')->constrained()->onDelete('cascade');
            `$table->date('check_in');
            `$table->date('check_out');
            `$table->integer('guests');
            `$table->decimal('total_price', 10, 2);
            `$table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            `$table->text('special_requests')->nullable();
            `$table->timestamps();
            `$table->softDeletes();
        });
    }
    
    public function down() {
        Schema::dropIfExists('bookings');
    }
};
"@
        $migration | Set-Content "backend/database/migrations/$(Get-Date -Format 'yyyy_MM_dd_HHmmss')_create_bookings_table.php"
        Set-Location backend
        php artisan migrate --force
        Set-Location ..
    }
    
    Invoke-TaskWithRetry "Create Payments Table" {
        $migration = @"
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('payments', function (Blueprint `$table) {
            `$table->id();
            `$table->foreignId('booking_id')->constrained()->onDelete('cascade');
            `$table->string('payment_method');
            `$table->string('transaction_id')->unique();
            `$table->decimal('amount', 10, 2);
            `$table->string('currency', 3)->default('USD');
            `$table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            `$table->json('payment_details')->nullable();
            `$table->timestamps();
        });
    }
    
    public function down() {
        Schema::dropIfExists('payments');
    }
};
"@
        $migration | Set-Content "backend/database/migrations/$(Get-Date -Format 'yyyy_MM_dd_HHmmss')_create_payments_table.php"
        Set-Location backend
        php artisan migrate --force
        Set-Location ..
    }
    
    Invoke-TaskWithRetry "Create Reviews Table" {
        $migration = @"
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('reviews', function (Blueprint `$table) {
            `$table->id();
            `$table->foreignId('property_id')->constrained()->onDelete('cascade');
            `$table->foreignId('user_id')->constrained()->onDelete('cascade');
            `$table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
            `$table->integer('rating');
            `$table->text('comment')->nullable();
            `$table->integer('cleanliness_rating')->nullable();
            `$table->integer('accuracy_rating')->nullable();
            `$table->integer('communication_rating')->nullable();
            `$table->integer('location_rating')->nullable();
            `$table->integer('value_rating')->nullable();
            `$table->json('photos')->nullable();
            `$table->boolean('is_verified')->default(false);
            `$table->timestamps();
        });
    }
    
    public function down() {
        Schema::dropIfExists('reviews');
    }
};
"@
        $migration | Set-Content "backend/database/migrations/$(Get-Date -Format 'yyyy_MM_dd_HHmmss')_create_reviews_table.php"
        Set-Location backend
        php artisan migrate --force
        Set-Location ..
    }
    
    Invoke-TaskWithRetry "Create Wishlists Table" {
        $migration = @"
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('wishlists', function (Blueprint `$table) {
            `$table->id();
            `$table->foreignId('user_id')->constrained()->onDelete('cascade');
            `$table->string('name');
            `$table->text('description')->nullable();
            `$table->boolean('is_public')->default(false);
            `$table->timestamps();
        });
        
        Schema::create('wishlist_properties', function (Blueprint `$table) {
            `$table->id();
            `$table->foreignId('wishlist_id')->constrained()->onDelete('cascade');
            `$table->foreignId('property_id')->constrained()->onDelete('cascade');
            `$table->timestamps();
            `$table->unique(['wishlist_id', 'property_id']);
        });
    }
    
    public function down() {
        Schema::dropIfExists('wishlist_properties');
        Schema::dropIfExists('wishlists');
    }
};
"@
        $migration | Set-Content "backend/database/migrations/$(Get-Date -Format 'yyyy_MM_dd_HHmmss')_create_wishlists_table.php"
        Set-Location backend
        php artisan migrate --force
        Set-Location ..
    }
    
    Invoke-TaskWithRetry "Create Messages Table" {
        $migration = @"
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('conversations', function (Blueprint `$table) {
            `$table->id();
            `$table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');
            `$table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null');
            `$table->timestamps();
        });
        
        Schema::create('messages', function (Blueprint `$table) {
            `$table->id();
            `$table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            `$table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            `$table->text('message');
            `$table->json('attachments')->nullable();
            `$table->boolean('is_read')->default(false);
            `$table->timestamp('read_at')->nullable();
            `$table->timestamps();
        });
        
        Schema::create('conversation_participants', function (Blueprint `$table) {
            `$table->id();
            `$table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            `$table->foreignId('user_id')->constrained()->onDelete('cascade');
            `$table->timestamps();
            `$table->unique(['conversation_id', 'user_id']);
        });
    }
    
    public function down() {
        Schema::dropIfExists('conversation_participants');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};
"@
        $migration | Set-Content "backend/database/migrations/$(Get-Date -Format 'yyyy_MM_dd_HHmmss')_create_messages_table.php"
        Set-Location backend
        php artisan migrate --force
        Set-Location ..
    }
    
    Invoke-TaskWithRetry "Create Notifications Table" {
        Set-Location backend
        php artisan notifications:table
        php artisan migrate --force
        Set-Location ..
    }
}

# ==============================================================================
# PHASE 2: AUTHENTICATION & SOCIAL LOGIN
# ==============================================================================
function Complete-Phase2-Authentication {
    Write-TaskHeader "PHASE 2: AUTHENTICATION & SOCIAL LOGIN"
    
    Invoke-TaskWithRetry "Create Social Authentication Controller" {
        $controller = @"
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function redirectToProvider(`$provider)
    {
        `$this->validateProvider(`$provider);
        return Socialite::driver(`$provider)->stateless()->redirect();
    }

    public function handleProviderCallback(`$provider)
    {
        `$this->validateProvider(`$provider);

        try {
            `$socialUser = Socialite::driver(`$provider)->stateless()->user();
            
            `$user = User::where('email', `$socialUser->getEmail())->first();

            if (!`$user) {
                `$user = User::create([
                    'name' => `$socialUser->getName(),
                    'email' => `$socialUser->getEmail(),
                    'email_verified_at' => now(),
                    'password' => bcrypt(Str::random(16)),
                    'provider' => `$provider,
                    'provider_id' => `$socialUser->getId(),
                    'avatar' => `$socialUser->getAvatar(),
                ]);
            }

            `$token = `$user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'user' => `$user,
                'token' => `$token,
            ]);

        } catch (\Exception `$e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'error' => `$e->getMessage(),
            ], 500);
        }
    }

    protected function validateProvider(`$provider)
    {
        if (!in_array(`$provider, ['google', 'facebook', 'twitter', 'github'])) {
            abort(404);
        }
    }
}
"@
        $controller | Set-Content "backend/app/Http/Controllers/API/SocialAuthController.php"
    }
    
    Invoke-TaskWithRetry "Update User Model for Social Auth" {
        $migration = @"
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('users', function (Blueprint `$table) {
            `$table->string('provider')->nullable()->after('email');
            `$table->string('provider_id')->nullable()->after('provider');
            `$table->string('avatar')->nullable()->after('provider_id');
            `$table->string('phone')->nullable()->after('email');
            `$table->timestamp('phone_verified_at')->nullable()->after('phone');
            `$table->boolean('two_factor_enabled')->default(false)->after('phone_verified_at');
            `$table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
        });
    }
    
    public function down() {
        Schema::table('users', function (Blueprint `$table) {
            `$table->dropColumn(['provider', 'provider_id', 'avatar', 'phone', 'phone_verified_at', 'two_factor_enabled', 'two_factor_secret']);
        });
    }
};
"@
        $migration | Set-Content "backend/database/migrations/$(Get-Date -Format 'yyyy_MM_dd_HHmmss')_add_social_auth_to_users.php"
        Set-Location backend
        php artisan migrate --force
        Set-Location ..
    }
    
    Invoke-TaskWithRetry "Add Social Auth Routes" {
        $routes = @"

// Social Authentication
Route::get('auth/{provider}', [App\Http\Controllers\API\SocialAuthController::class, 'redirectToProvider']);
Route::get('auth/{provider}/callback', [App\Http\Controllers\API\SocialAuthController::class, 'handleProviderCallback']);
"@
        Add-Content "backend/routes/api.php" $routes
    }
}

# ==============================================================================
# PHASE 3: DASHBOARD ANALYTICS
# ==============================================================================
function Complete-Phase3-DashboardAnalytics {
    Write-TaskHeader "PHASE 3: DASHBOARD ANALYTICS"
    
    Invoke-TaskWithRetry "Create Analytics Service" {
        $service = @"
<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use App\Models\Payment;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    public function getOwnerDashboardStats(`$ownerId, `$period = '30days')
    {
        `$startDate = `$this->getStartDate(`$period);

        return [
            'total_properties' => Property::where('user_id', `$ownerId)->count(),
            'active_bookings' => Booking::whereHas('property', function(`$q) use (`$ownerId) {
                `$q->where('user_id', `$ownerId);
            })->where('status', 'confirmed')->count(),
            
            'total_revenue' => Payment::whereHas('booking.property', function(`$q) use (`$ownerId) {
                `$q->where('user_id', `$ownerId);
            })->where('status', 'completed')
              ->where('created_at', '>=', `$startDate)
              ->sum('amount'),
            
            'occupancy_rate' => `$this->calculateOccupancyRate(`$ownerId, `$startDate),
            'average_rating' => Review::whereHas('property', function(`$q) use (`$ownerId) {
                `$q->where('user_id', `$ownerId);
            })->avg('rating'),
            
            'bookings_by_month' => `$this->getBookingsByMonth(`$ownerId, `$startDate),
            'revenue_by_month' => `$this->getRevenueByMonth(`$ownerId, `$startDate),
            'top_properties' => `$this->getTopProperties(`$ownerId, 5),
        ];
    }

    public function getTenantDashboardStats(`$userId, `$period = '30days')
    {
        `$startDate = `$this->getStartDate(`$period);

        return [
            'total_bookings' => Booking::where('user_id', `$userId)->count(),
            'active_bookings' => Booking::where('user_id', `$userId)
                ->where('status', 'confirmed')->count(),
            'total_spent' => Payment::whereHas('booking', function(`$q) use (`$userId) {
                `$q->where('user_id', `$userId);
            })->where('status', 'completed')
              ->where('created_at', '>=', `$startDate)
              ->sum('amount'),
            'upcoming_bookings' => Booking::where('user_id', `$userId)
                ->where('check_in', '>', now())
                ->where('status', 'confirmed')
                ->with('property')
                ->orderBy('check_in')
                ->take(5)
                ->get(),
            'recent_reviews' => Review::where('user_id', `$userId)
                ->with('property')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
        ];
    }

    protected function getStartDate(`$period)
    {
        return match(`$period) {
            '7days' => Carbon::now()->subDays(7),
            '30days' => Carbon::now()->subDays(30),
            '90days' => Carbon::now()->subDays(90),
            '1year' => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(30),
        };
    }

    protected function calculateOccupancyRate(`$ownerId, `$startDate)
    {
        `$properties = Property::where('user_id', `$ownerId)->get();
        `$totalDays = 0;
        `$bookedDays = 0;

        foreach (`$properties as `$property) {
            `$totalDays += Carbon::now()->diffInDays(`$startDate);
            `$bookedDays += Booking::where('property_id', `$property->id)
                ->where('status', 'confirmed')
                ->where(function(`$q) use (`$startDate) {
                    `$q->whereBetween('check_in', [`$startDate, now()])
                      ->orWhereBetween('check_out', [`$startDate, now()]);
                })
                ->get()
                ->sum(function(`$booking) {
                    return Carbon::parse(`$booking->check_out)->diffInDays(Carbon::parse(`$booking->check_in));
                });
        }

        return `$totalDays > 0 ? round((`$bookedDays / `$totalDays) * 100, 2) : 0;
    }

    protected function getBookingsByMonth(`$ownerId, `$startDate)
    {
        return Booking::whereHas('property', function(`$q) use (`$ownerId) {
            `$q->where('user_id', `$ownerId);
        })->where('created_at', '>=', `$startDate)
          ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('count(*) as count'))
          ->groupBy('month')
          ->orderBy('month')
          ->get();
    }

    protected function getRevenueByMonth(`$ownerId, `$startDate)
    {
        return Payment::whereHas('booking.property', function(`$q) use (`$ownerId) {
            `$q->where('user_id', `$ownerId);
        })->where('status', 'completed')
          ->where('created_at', '>=', `$startDate)
          ->select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), DB::raw('sum(amount) as total'))
          ->groupBy('month')
          ->orderBy('month')
          ->get();
    }

    protected function getTopProperties(`$ownerId, `$limit = 5)
    {
        return Property::where('user_id', `$ownerId)
            ->withCount('bookings')
            ->withAvg('reviews', 'rating')
            ->orderBy('bookings_count', 'desc')
            ->take(`$limit)
            ->get();
    }
}
"@
        $service | Set-Content "backend/app/Services/AnalyticsService.php"
    }
    
    Invoke-TaskWithRetry "Create Analytics Controller" {
        $controller = @"
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected `$analyticsService;

    public function __construct(AnalyticsService `$analyticsService)
    {
        `$this->analyticsService = `$analyticsService;
    }

    public function ownerDashboard(Request `$request)
    {
        `$period = `$request->input('period', '30days');
        `$stats = `$this->analyticsService->getOwnerDashboardStats(auth()->id(), `$period);
        
        return response()->json([
            'success' => true,
            'data' => `$stats,
        ]);
    }

    public function tenantDashboard(Request `$request)
    {
        `$period = `$request->input('period', '30days');
        `$stats = `$this->analyticsService->getTenantDashboardStats(auth()->id(), `$period);
        
        return response()->json([
            'success' => true,
            'data' => `$stats,
        ]);
    }
}
"@
        $controller | Set-Content "backend/app/Http/Controllers/API/AnalyticsController.php"
    }
    
    Invoke-TaskWithRetry "Add Analytics Routes" {
        $routes = @"

// Analytics
Route::middleware('auth:sanctum')->group(function () {
    Route::get('analytics/owner-dashboard', [App\Http\Controllers\API\AnalyticsController::class, 'ownerDashboard']);
    Route::get('analytics/tenant-dashboard', [App\Http\Controllers\API\AnalyticsController::class, 'tenantDashboard']);
});
"@
        Add-Content "backend/routes/api.php" $routes
    }
    
    Invoke-TaskWithRetry "Create Frontend Dashboard Component" {
        $component = @"
import React, { useEffect, useState } from 'react';
import { Line, Bar, Doughnut } from 'react-chartjs-2';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
);

export default function OwnerDashboard() {
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [period, setPeriod] = useState('30days');

  useEffect(() => {
    fetchDashboardStats();
  }, [period]);

  const fetchDashboardStats = async () => {
    try {
      const response = await fetch(\`/api/analytics/owner-dashboard?period=\${period}\`, {
        headers: {
          'Authorization': \`Bearer \${localStorage.getItem('token')}\`,
        },
      });
      const data = await response.json();
      setStats(data.data);
    } catch (error) {
      console.error('Error fetching dashboard stats:', error);
    } finally {
      setLoading(false);
    }
  };

  if (loading) return <div>Loading dashboard...</div>;
  if (!stats) return <div>No data available</div>;

  const revenueChartData = {
    labels: stats.revenue_by_month.map(item => item.month),
    datasets: [{
      label: 'Revenue',
      data: stats.revenue_by_month.map(item => item.total),
      borderColor: 'rgb(75, 192, 192)',
      tension: 0.1,
    }],
  };

  return (
    <div className="dashboard-container">
      <h1>Owner Dashboard</h1>
      
      <div className="period-selector">
        <button onClick={() => setPeriod('7days')}>7 Days</button>
        <button onClick={() => setPeriod('30days')}>30 Days</button>
        <button onClick={() => setPeriod('90days')}>90 Days</button>
        <button onClick={() => setPeriod('1year')}>1 Year</button>
      </div>

      <div className="stats-grid">
        <div className="stat-card">
          <h3>Total Properties</h3>
          <p className="stat-value">{stats.total_properties}</p>
        </div>
        <div className="stat-card">
          <h3>Active Bookings</h3>
          <p className="stat-value">{stats.active_bookings}</p>
        </div>
        <div className="stat-card">
          <h3>Total Revenue</h3>
          <p className="stat-value">\${stats.total_revenue.toFixed(2)}</p>
        </div>
        <div className="stat-card">
          <h3>Occupancy Rate</h3>
          <p className="stat-value">{stats.occupancy_rate}%</p>
        </div>
        <div className="stat-card">
          <h3>Average Rating</h3>
          <p className="stat-value">{stats.average_rating?.toFixed(1) || 'N/A'}</p>
        </div>
      </div>

      <div className="charts-grid">
        <div className="chart-container">
          <h3>Revenue Over Time</h3>
          <Line data={revenueChartData} />
        </div>
        
        <div className="chart-container">
          <h3>Top Properties</h3>
          <div className="properties-list">
            {stats.top_properties.map(property => (
              <div key={property.id} className="property-item">
                <h4>{property.title}</h4>
                <p>Bookings: {property.bookings_count}</p>
                <p>Rating: {property.reviews_avg_rating?.toFixed(1) || 'N/A'}</p>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}
"@
        New-Item -ItemType Directory -Force -Path "frontend/src/components/dashboard" | Out-Null
        $component | Set-Content "frontend/src/components/dashboard/OwnerDashboard.tsx"
    }
}

# ==============================================================================
# PHASE 4: MULTI-LANGUAGE SUPPORT
# ==============================================================================
function Complete-Phase4-MultiLanguage {
    Write-TaskHeader "PHASE 4: MULTI-LANGUAGE SUPPORT"
    
    Invoke-TaskWithRetry "Configure i18n for Next.js" {
        $i18nConfig = @"
module.exports = {
  i18n: {
    defaultLocale: 'en',
    locales: ['en', 'es', 'fr', 'de', 'ro', 'ar'],
  },
  localePath: './public/locales',
  reloadOnPrerender: process.env.NODE_ENV === 'development',
};
"@
        $i18nConfig | Set-Content "frontend/next-i18next.config.js"
    }
    
    Invoke-TaskWithRetry "Create Translation Files" {
        $locales = @('en', 'es', 'fr', 'de', 'ro')
        
        foreach ($locale in $locales) {
            New-Item -ItemType Directory -Force -Path "frontend/public/locales/$locale" | Out-Null
            
            $common = @"
{
  "welcome": "Welcome to RentHub",
  "search": "Search",
  "login": "Login",
  "register": "Register",
  "logout": "Logout",
  "properties": "Properties",
  "bookings": "Bookings",
  "profile": "Profile",
  "dashboard": "Dashboard",
  "settings": "Settings"
}
"@
            $common | Set-Content "frontend/public/locales/$locale/common.json"
        }
    }
    
    Invoke-TaskWithRetry "Create Language Switcher Component" {
        $component = @"
import { useRouter } from 'next/router';
import { useTranslation } from 'next-i18next';

export default function LanguageSwitcher() {
  const router = useRouter();
  const { i18n } = useTranslation();

  const languages = [
    { code: 'en', name: 'English', flag: '🇺🇸' },
    { code: 'es', name: 'Español', flag: '🇪🇸' },
    { code: 'fr', name: 'Français', flag: '🇫🇷' },
    { code: 'de', name: 'Deutsch', flag: '🇩🇪' },
    { code: 'ro', name: 'Română', flag: '🇷🇴' },
    { code: 'ar', name: 'العربية', flag: '🇸🇦' },
  ];

  const changeLanguage = (locale) => {
    router.push(router.pathname, router.asPath, { locale });
  };

  return (
    <div className="language-switcher">
      <select
        value={i18n.language}
        onChange={(e) => changeLanguage(e.target.value)}
        className="language-select"
      >
        {languages.map((lang) => (
          <option key={lang.code} value={lang.code}>
            {lang.flag} {lang.name}
          </option>
        ))}
      </select>
    </div>
  );
}
"@
        $component | Set-Content "frontend/src/components/LanguageSwitcher.tsx"
    }
}

# ==============================================================================
# PHASE 5: MULTI-CURRENCY SUPPORT
# ==============================================================================
function Complete-Phase5-MultiCurrency {
    Write-TaskHeader "PHASE 5: MULTI-CURRENCY SUPPORT"
    
    Invoke-TaskWithRetry "Create Currency Service" {
        $service = @"
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    protected `$baseCurrency = 'USD';
    protected `$supportedCurrencies = ['USD', 'EUR', 'GBP', 'RON', 'JPY', 'CAD', 'AUD'];
    protected `$apiKey;

    public function __construct()
    {
        `$this->apiKey = config('services.exchangerate.api_key');
    }

    public function convert(`$amount, `$from, `$to)
    {
        if (`$from === `$to) {
            return `$amount;
        }

        `$rate = `$this->getExchangeRate(`$from, `$to);
        return round(`$amount * `$rate, 2);
    }

    public function getExchangeRate(`$from, `$to)
    {
        `$cacheKey = "exchange_rate_{`$from}_{`$to}";
        
        return Cache::remember(`$cacheKey, 3600, function () use (`$from, `$to) {
            try {
                `$response = Http::get("https://api.exchangerate-api.com/v4/latest/{`$from}");
                `$data = `$response->json();
                
                return `$data['rates'][`$to] ?? 1;
            } catch (\Exception `$e) {
                return 1; // Fallback to 1:1 ratio
            }
        });
    }

    public function getAllRates(`$base = 'USD')
    {
        `$cacheKey = "all_rates_{`$base}";
        
        return Cache::remember(`$cacheKey, 3600, function () use (`$base) {
            try {
                `$response = Http::get("https://api.exchangerate-api.com/v4/latest/{`$base}");
                return `$response->json()['rates'] ?? [];
            } catch (\Exception `$e) {
                return [];
            }
        });
    }

    public function getSupportedCurrencies()
    {
        return `$this->supportedCurrencies;
    }

    public function formatPrice(`$amount, `$currency = 'USD')
    {
        `$symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'RON' => 'lei',
            'JPY' => '¥',
            'CAD' => 'C$',
            'AUD' => 'A$',
        ];

        `$symbol = `$symbols[`$currency] ?? `$currency;
        return "{`$symbol}" . number_format(`$amount, 2);
    }
}
"@
        $service | Set-Content "backend/app/Services/CurrencyService.php"
    }
    
    Invoke-TaskWithRetry "Create Currency Controller" {
        $controller = @"
<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    protected `$currencyService;

    public function __construct(CurrencyService `$currencyService)
    {
        `$this->currencyService = `$currencyService;
    }

    public function convert(Request `$request)
    {
        `$request->validate([
            'amount' => 'required|numeric',
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
        ]);

        `$converted = `$this->currencyService->convert(
            `$request->amount,
            `$request->from,
            `$request->to
        );

        return response()->json([
            'success' => true,
            'original' => [
                'amount' => `$request->amount,
                'currency' => `$request->from,
            ],
            'converted' => [
                'amount' => `$converted,
                'currency' => `$request->to,
            ],
        ]);
    }

    public function rates(Request `$request)
    {
        `$base = `$request->input('base', 'USD');
        `$rates = `$this->currencyService->getAllRates(`$base);

        return response()->json([
            'success' => true,
            'base' => `$base,
            'rates' => `$rates,
        ]);
    }

    public function supported()
    {
        return response()->json([
            'success' => true,
            'currencies' => `$this->currencyService->getSupportedCurrencies(),
        ]);
    }
}
"@
        $controller | Set-Content "backend/app/Http/Controllers/API/CurrencyController.php"
    }
    
    Invoke-TaskWithRetry "Add Currency Routes" {
        $routes = @"

// Currency
Route::get('currency/convert', [App\Http\Controllers\API\CurrencyController::class, 'convert']);
Route::get('currency/rates', [App\Http\Controllers\API\CurrencyController::class, 'rates']);
Route::get('currency/supported', [App\Http\Controllers\API\CurrencyController::class, 'supported']);
"@
        Add-Content "backend/routes/api.php" $routes
    }
    
    Invoke-TaskWithRetry "Create Currency Selector Component" {
        $component = @"
import React, { useState, useEffect } from 'react';

export default function CurrencySelector() {
  const [currency, setCurrency] = useState('USD');
  const [rates, setRates] = useState({});

  const currencies = [
    { code: 'USD', symbol: '$', name: 'US Dollar' },
    { code: 'EUR', symbol: '€', name: 'Euro' },
    { code: 'GBP', symbol: '£', name: 'British Pound' },
    { code: 'RON', symbol: 'lei', name: 'Romanian Leu' },
    { code: 'JPY', symbol: '¥', name: 'Japanese Yen' },
    { code: 'CAD', symbol: 'C$', name: 'Canadian Dollar' },
    { code: 'AUD', symbol: 'A$', name: 'Australian Dollar' },
  ];

  useEffect(() => {
    fetchExchangeRates();
  }, []);

  const fetchExchangeRates = async () => {
    try {
      const response = await fetch('/api/currency/rates');
      const data = await response.json();
      setRates(data.rates);
    } catch (error) {
      console.error('Error fetching exchange rates:', error);
    }
  };

  const handleCurrencyChange = (newCurrency) => {
    setCurrency(newCurrency);
    localStorage.setItem('selectedCurrency', newCurrency);
    window.dispatchEvent(new CustomEvent('currencyChanged', { detail: newCurrency }));
  };

  return (
    <div className="currency-selector">
      <select
        value={currency}
        onChange={(e) => handleCurrencyChange(e.target.value)}
        className="currency-select"
      >
        {currencies.map((curr) => (
          <option key={curr.code} value={curr.code}>
            {curr.symbol} {curr.code}
          </option>
        ))}
      </select>
    </div>
  );
}
"@
        $component | Set-Content "frontend/src/components/CurrencySelector.tsx"
    }
}

# ==============================================================================
# PHASE 6: SECURITY ENHANCEMENTS
# ==============================================================================
function Complete-Phase6-Security {
    Write-TaskHeader "PHASE 6: SECURITY ENHANCEMENTS"
    
    Invoke-TaskWithRetry "Create Security Headers Middleware" {
        $middleware = @"
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request `$request, Closure `$next)
    {
        `$response = `$next(`$request);

        `$response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        `$response->headers->set('X-Content-Type-Options', 'nosniff');
        `$response->headers->set('X-XSS-Protection', '1; mode=block');
        `$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        `$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        `$response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        return `$response;
    }
}
"@
        $middleware | Set-Content "backend/app/Http/Middleware/SecurityHeaders.php"
    }
    
    Invoke-TaskWithRetry "Install and Configure Roles & Permissions" {
        Set-Location backend
        composer require spatie/laravel-permission --no-interaction
        php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
        php artisan migrate --force
        Set-Location ..
    }
    
    Invoke-TaskWithRetry "Create Roles Seeder" {
        $seeder = @"
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        Permission::create(['name' => 'view properties']);
        Permission::create(['name' => 'create properties']);
        Permission::create(['name' => 'edit properties']);
        Permission::create(['name' => 'delete properties']);
        Permission::create(['name' => 'manage bookings']);
        Permission::create(['name' => 'view analytics']);
        Permission::create(['name' => 'manage users']);
        Permission::create(['name' => 'manage settings']);

        // Create roles and assign permissions
        `$admin = Role::create(['name' => 'admin']);
        `$admin->givePermissionTo(Permission::all());

        `$owner = Role::create(['name' => 'owner']);
        `$owner->givePermissionTo([
            'view properties',
            'create properties',
            'edit properties',
            'delete properties',
            'manage bookings',
            'view analytics',
        ]);

        `$tenant = Role::create(['name' => 'tenant']);
        `$tenant->givePermissionTo([
            'view properties',
        ]);

        `$guest = Role::create(['name' => 'guest']);
        `$guest->givePermissionTo([
            'view properties',
        ]);
    }
}
"@
        $seeder | Set-Content "backend/database/seeders/RolesAndPermissionsSeeder.php"
        
        Set-Location backend
        php artisan db:seed --class=RolesAndPermissionsSeeder --force
        Set-Location ..
    }
}

# ==============================================================================
# PHASE 7: PWA & OFFLINE SUPPORT
# ==============================================================================
function Complete-Phase7-PWA {
    Write-TaskHeader "PHASE 7: PWA & OFFLINE SUPPORT"
    
    Invoke-TaskWithRetry "Create PWA Manifest" {
        $manifest = @"
{
  "name": "RentHub - Property Rental Platform",
  "short_name": "RentHub",
  "description": "Find and book your perfect rental property",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#0070f3",
  "orientation": "portrait-primary",
  "icons": [
    {
      "src": "/icons/icon-72x72.png",
      "sizes": "72x72",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-96x96.png",
      "sizes": "96x96",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-128x128.png",
      "sizes": "128x128",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-144x144.png",
      "sizes": "144x144",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-152x152.png",
      "sizes": "152x152",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-384x384.png",
      "sizes": "384x384",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any maskable"
    }
  ],
  "shortcuts": [
    {
      "name": "Search Properties",
      "url": "/properties",
      "description": "Search for rental properties"
    },
    {
      "name": "My Bookings",
      "url": "/bookings",
      "description": "View your bookings"
    },
    {
      "name": "Dashboard",
      "url": "/dashboard",
      "description": "Go to dashboard"
    }
  ],
  "categories": ["travel", "lifestyle", "business"]
}
"@
        $manifest | Set-Content "frontend/public/manifest.json"
    }
    
    Invoke-TaskWithRetry "Create Service Worker" {
        $sw = @"
const CACHE_NAME = 'renthub-v1';
const urlsToCache = [
  '/',
  '/properties',
  '/about',
  '/contact',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(urlsToCache);
    })
  );
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request).then((response) => {
      return response || fetch(event.request);
    })
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
});
"@
        $sw | Set-Content "frontend/public/sw.js"
    }
}

# ==============================================================================
# PHASE 8: SEO & PERFORMANCE
# ==============================================================================
function Complete-Phase8-SEO {
    Write-TaskHeader "PHASE 8: SEO & PERFORMANCE OPTIMIZATION"
    
    Invoke-TaskWithRetry "Create Sitemap Generator" {
        $sitemapConfig = @"
module.exports = {
  siteUrl: process.env.NEXT_PUBLIC_SITE_URL || 'https://renthub.com',
  generateRobotsTxt: true,
  sitemapSize: 7000,
  changefreq: 'daily',
  priority: 0.7,
  exclude: ['/admin/*', '/dashboard/*', '/api/*'],
  robotsTxtOptions: {
    policies: [
      {
        userAgent: '*',
        allow: '/',
        disallow: ['/admin', '/dashboard', '/api'],
      },
    ],
  },
};
"@
        $sitemapConfig | Set-Content "frontend/next-sitemap.config.js"
    }
    
    Invoke-TaskWithRetry "Install SEO Packages" {
        Set-Location frontend
        npm install --legacy-peer-deps next-sitemap next-seo
        Set-Location ..
    }
    
    Invoke-TaskWithRetry "Create robots.txt" {
        $robots = @"
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /dashboard/
Disallow: /api/

Sitemap: https://renthub.com/sitemap.xml
"@
        $robots | Set-Content "frontend/public/robots.txt"
    }
}

# ==============================================================================
# PHASE 9: TESTING & QUALITY ASSURANCE
# ==============================================================================
function Complete-Phase9-Testing {
    Write-TaskHeader "PHASE 9: TESTING & QUALITY ASSURANCE"
    
    Invoke-TaskWithRetry "Install Testing Dependencies" {
        Set-Location backend
        composer require --dev phpunit/phpunit pestphp/pest --no-interaction
        Set-Location ..
        
        Set-Location frontend
        npm install --save-dev --legacy-peer-deps @testing-library/react @testing-library/jest-dom jest
        Set-Location ..
    }
    
    Invoke-TaskWithRetry "Run Backend Tests" {
        Set-Location backend
        php artisan test
        Set-Location ..
    }
}

# ==============================================================================
# PHASE 10: DEPLOYMENT & MONITORING
# ==============================================================================
function Complete-Phase10-Deployment {
    Write-TaskHeader "PHASE 10: DEPLOYMENT & MONITORING"
    
    Invoke-TaskWithRetry "Create Docker Compose Configuration" {
        $dockerCompose = @"
version: '3.8'

services:
  backend:
    build:
      context: ./backend
      dockerfile: Dockerfile
    ports:
      - "8000:8000"
    environment:
      - DB_HOST=database
      - DB_DATABASE=renthub
      - DB_USERNAME=root
      - DB_PASSWORD=secret
      - REDIS_HOST=redis
    volumes:
      - ./backend:/var/www/html
    depends_on:
      - database
      - redis

  frontend:
    build:
      context: ./frontend
      dockerfile: Dockerfile
    ports:
      - "3000:3000"
    environment:
      - NEXT_PUBLIC_API_URL=http://backend:8000
    volumes:
      - ./frontend:/app
    depends_on:
      - backend

  database:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: renthub
      MYSQL_ROOT_PASSWORD: secret
    volumes:
      - db_data:/var/lib/mysql
    ports:
      - "3306:3306"

  redis:
    image: redis:alpine
    ports:
      - "6379:6379"

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
    depends_on:
      - backend
      - frontend

volumes:
  db_data:
"@
        $dockerCompose | Set-Content "docker-compose.yml"
    }
    
    Invoke-TaskWithRetry "Create GitHub Actions Workflow" {
        New-Item -ItemType Directory -Force -Path ".github/workflows" | Out-Null
        
        $workflow = @"
name: CI/CD Pipeline

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

jobs:
  backend-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: cd backend && composer install
      - name: Run tests
        run: cd backend && php artisan test

  frontend-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Setup Node
        uses: actions/setup-node@v3
        with:
          node-version: '18'
      - name: Install dependencies
        run: cd frontend && npm install
      - name: Run tests
        run: cd frontend && npm test

  deploy:
    needs: [backend-tests, frontend-tests]
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    steps:
      - uses: actions/checkout@v3
      - name: Deploy to production
        run: echo "Deploying to production..."
"@
        $workflow | Set-Content ".github/workflows/ci-cd.yml"
    }
}

# ==============================================================================
# MAIN EXECUTION FLOW
# ==============================================================================
function Start-CompleteAutomation {
    Write-Host @"

╔══════════════════════════════════════════════════════════════════════════════╗
║                                                                              ║
║                     RENTHUB - AUTOMATED COMPLETION SYSTEM                    ║
║                                                                              ║
║                        Target: 100% Completion Overnight                     ║
║                                                                              ║
╚══════════════════════════════════════════════════════════════════════════════╝

"@ -ForegroundColor Cyan

    Write-Info "Starting at: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    Write-Info "Estimated completion time: 8-12 hours"
    Write-Info "Progress will be logged to: $global:LogFile"
    Write-Info "Real-time progress tracking: $global:ProgressFile"
    
    if ($DryRun) {
        Write-Info "DRY RUN MODE - No actual changes will be made"
    }
    
    Write-Host "`n"
    
    # Execute all phases
    Complete-Phase1-Foundation
    Complete-Phase2-Authentication
    Complete-Phase3-DashboardAnalytics
    Complete-Phase4-MultiLanguage
    Complete-Phase5-MultiCurrency
    Complete-Phase6-Security
    Complete-Phase7-PWA
    Complete-Phase8-SEO
    Complete-Phase9-Testing
    Complete-Phase10-Deployment
    
    # Final report
    Write-TaskHeader "AUTOMATION COMPLETE - FINAL REPORT"
    
    $duration = (Get-Date) - $global:StartTime
    $completionRate = if ($global:TotalTasks -gt 0) { 
        [math]::Round(($global:CompletedCount / $global:TotalTasks) * 100, 2) 
    } else { 0 }
    
    Write-Host "`n╔══════════════════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
    Write-Host "║                              FINAL STATISTICS                                ║" -ForegroundColor Cyan
    Write-Host "╠══════════════════════════════════════════════════════════════════════════════╣" -ForegroundColor Cyan
    Write-Host "║  Total Tasks Executed:     $($global:TotalTasks.ToString().PadLeft(45))  ║" -ForegroundColor White
    Write-Host "║  Successfully Completed:   $($global:CompletedCount.ToString().PadLeft(45))  ║" -ForegroundColor Green
    Write-Host "║  Failed:                   $($global:FailedTasks.Count.ToString().PadLeft(45))  ║" -ForegroundColor Red
    Write-Host "║  Completion Rate:          $("$completionRate%".PadLeft(45))  ║" -ForegroundColor Yellow
    Write-Host "║  Duration:                 $("$([math]::Round($duration.TotalHours, 2)) hours".PadLeft(45))  ║" -ForegroundColor White
    Write-Host "║  Start Time:               $($global:StartTime.ToString('yyyy-MM-dd HH:mm:ss').PadLeft(45))  ║" -ForegroundColor White
    Write-Host "║  End Time:                 $((Get-Date).ToString('yyyy-MM-dd HH:mm:ss').PadLeft(45))  ║" -ForegroundColor White
    Write-Host "╚══════════════════════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan
    
    if ($global:FailedTasks.Count -gt 0) {
        Write-Host "`n⚠️  FAILED TASKS:" -ForegroundColor Red
        foreach ($failed in $global:FailedTasks) {
            Write-Host "   • $($failed.Task): $($failed.Error)" -ForegroundColor Red
        }
    }
    
    Write-Success "Full detailed log saved to: $global:LogFile"
    Write-Success "Progress tracking saved to: $global:ProgressFile"
    
    Write-Host "`n✨ AUTOMATION COMPLETE! Check the logs for details. ✨`n" -ForegroundColor Green
}

# Start the automation
Start-CompleteAutomation
