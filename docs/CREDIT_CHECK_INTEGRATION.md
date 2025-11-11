# Credit Check Integration Guide for RentHub

## Overview
This guide covers integrating credit check services for guest verification using Experian, Equifax, or TransUnion APIs.

## Current Implementation Status

### ✅ Completed
- GuestVerification model with credit check fields
- Credit check request endpoint
- Database schema for storing credit results
- UI placeholder for credit check status

### ⚠️ Pending
- Actual API integration with credit bureaus
- Credit score calculation and risk assessment
- Compliance with FCRA regulations
- User consent workflow

## Credit Bureau Options

### Option 1: Experian (Recommended)

#### Features
- Real-time credit reports
- Identity verification
- Fraud detection
- Rental-specific scores

#### Setup
```bash
composer require guzzlehttp/guzzle
```

Configuration in `.env`:
```env
EXPERIAN_API_KEY=your_api_key
EXPERIAN_API_SECRET=your_secret
EXPERIAN_SANDBOX=true  # false for production
EXPERIAN_URL=https://sandbox-us-api.experian.com  # or production URL
```

Add to `config/services.php`:
```php
'experian' => [
    'api_key' => env('EXPERIAN_API_KEY'),
    'api_secret' => env('EXPERIAN_API_SECRET'),
    'sandbox' => env('EXPERIAN_SANDBOX', true),
    'url' => env('EXPERIAN_URL', 'https://sandbox-us-api.experian.com'),
],
```

### Option 2: Equifax

#### Features
- Comprehensive credit data
- Employment verification
- Income verification

Configuration in `.env`:
```env
EQUIFAX_USERNAME=your_username
EQUIFAX_PASSWORD=your_password
EQUIFAX_SECURITY_CODE=your_security_code
EQUIFAX_MEMBER_NUMBER=your_member_number
EQUIFAX_SANDBOX=true
```

### Option 3: TransUnion

#### Features
- ResidentScore for rental screening
- Identity verification
- Criminal background checks (where permitted)

Configuration in `.env`:
```env
TRANSUNION_API_KEY=your_api_key
TRANSUNION_SUBSCRIPTION_ID=your_subscription_id
TRANSUNION_SANDBOX=true
```

## Implementation

### Create Credit Check Service

Create `app/Services/CreditCheckService.php`:

```php
<?php

namespace App\Services;

use App\Models\GuestVerification;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CreditCheckService
{
    protected Client $client;
    protected string $apiKey;
    protected string $apiSecret;
    protected string $baseUrl;
    protected bool $sandbox;

    public function __construct()
    {
        $this->apiKey = config('services.experian.api_key');
        $this->apiSecret = config('services.experian.api_secret');
        $this->baseUrl = config('services.experian.url');
        $this->sandbox = config('services.experian.sandbox', true);
        
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
        ]);
    }

    /**
     * Request credit check for a user
     */
    public function requestCreditCheck(User $user, GuestVerification $verification): array
    {
        try {
            // Get user consent (must be obtained before making request)
            if (!$this->hasConsent($user)) {
                throw new \Exception('User consent required for credit check');
            }

            // Make API request to Experian
            $response = $this->client->post('/credit/v1/scores', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessToken(),
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'consumerIdentity' => [
                        'firstName' => $user->name,
                        'lastName' => $user->last_name ?? '',
                        'dateOfBirth' => $user->date_of_birth,
                        'ssn' => $this->getSSN($user),  // Encrypted in database
                    ],
                    'address' => [
                        'line1' => $user->address,
                        'city' => $user->city,
                        'state' => $user->state,
                        'zipCode' => $user->zip_code,
                    ],
                    'products' => ['CreditScore', 'CreditReport', 'RiskModels'],
                ],
            ]);

            $data = json_decode($response->getBody(), true);

            // Parse and store results
            $this->storeResults($verification, $data);

            return [
                'success' => true,
                'credit_score' => $data['creditScore']['score'] ?? null,
                'risk_level' => $this->calculateRiskLevel($data),
            ];

        } catch (\Exception $e) {
            Log::error('Credit check failed: ' . $e->getMessage());
            
            // Update verification status
            $verification->update([
                'credit_status' => 'failed',
                'credit_check_notes' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get OAuth access token
     */
    protected function getAccessToken(): string
    {
        // Check cache first
        $token = cache('experian_access_token');
        if ($token) {
            return $token;
        }

        // Request new token
        $response = $this->client->post('/oauth/v1/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->apiKey,
                'client_secret' => $this->apiSecret,
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $token = $data['access_token'];

        // Cache token (typically valid for 30 minutes)
        cache(['experian_access_token' => $token], now()->addMinutes(25));

        return $token;
    }

    /**
     * Store credit check results
     */
    protected function storeResults(GuestVerification $verification, array $data): void
    {
        $creditScore = $data['creditScore']['score'] ?? null;
        $riskLevel = $this->calculateRiskLevel($data);

        $verification->update([
            'credit_status' => 'completed',
            'credit_score' => $creditScore,
            'credit_report_data' => json_encode($data),  // Store full report
            'credit_check_completed_at' => now(),
            'trust_score' => $this->calculateTrustScore($verification),
        ]);

        // Log the check
        \App\Models\VerificationLog::log(
            $verification->id,
            'credit',
            'completed',
            "Credit score: {$creditScore}, Risk: {$riskLevel}"
        );
    }

    /**
     * Calculate risk level based on credit data
     */
    protected function calculateRiskLevel(array $data): string
    {
        $score = $data['creditScore']['score'] ?? 0;

        return match (true) {
            $score >= 750 => 'low',
            $score >= 650 => 'medium',
            $score >= 550 => 'high',
            default => 'very_high',
        };
    }

    /**
     * Calculate trust score (0-100)
     */
    protected function calculateTrustScore(GuestVerification $verification): int
    {
        $score = 0;

        // Credit score component (40 points)
        if ($verification->credit_score) {
            $score += min(40, ($verification->credit_score / 850) * 40);
        }

        // Identity verification (20 points)
        if ($verification->identity_status === 'verified') {
            $score += 20;
        }

        // Reference checks (20 points)
        if ($verification->references_status === 'verified') {
            $score += 20;
        }

        // Background check (10 points)
        if ($verification->background_status === 'verified') {
            $score += 10;
        }

        // Employment verification (10 points)
        if ($verification->employment_verified) {
            $score += 10;
        }

        return (int) $score;
    }

    /**
     * Check if user has given consent
     */
    protected function hasConsent(User $user): bool
    {
        // Check user's consent record
        return $user->credit_check_consent === true 
            && $user->credit_check_consent_date 
            && $user->credit_check_consent_date->isAfter(now()->subYear());
    }

    /**
     * Get encrypted SSN from user
     */
    protected function getSSN(User $user): ?string
    {
        // Decrypt SSN from database
        // This should be properly encrypted at rest
        return $user->encrypted_ssn ? decrypt($user->encrypted_ssn) : null;
    }

    /**
     * Sandbox mode - return mock data
     */
    protected function getSandboxData(): array
    {
        return [
            'success' => true,
            'credit_score' => 720,
            'risk_level' => 'low',
            'mock' => true,
        ];
    }
}
```

### Update GuestVerificationController

Update `app/Http/Controllers/Api/GuestVerificationController.php` at line 190:

```php
// Current (lines 185-200):
$verification->update([
    'credit_check_enabled' => true,
    'credit_status' => 'pending',
]);

// TODO: Integrate with credit check API (Experian, Equifax, etc.)

// New implementation:
$verification->update([
    'credit_check_enabled' => true,
    'credit_status' => 'pending',
]);

// Integrate with credit check API
$creditService = app(CreditCheckService::class);
$result = $creditService->requestCreditCheck($user, $verification);

if (!$result['success']) {
    return response()->json([
        'success' => false,
        'message' => 'Credit check request failed',
        'error' => $result['error'] ?? 'Unknown error',
    ], 500);
}
```

## Database Migrations

Add credit check consent fields to users table:

```php
// database/migrations/YYYY_MM_DD_add_credit_check_consent_to_users.php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('credit_check_consent')->default(false);
        $table->timestamp('credit_check_consent_date')->nullable();
        $table->text('encrypted_ssn')->nullable();  // Encrypted
        $table->date('date_of_birth')->nullable();
        $table->string('zip_code')->nullable();
    });
}
```

## Legal Compliance (FCRA)

### Required Notices

1. **Pre-Screening Notice**
   - Inform user that credit check will be performed
   - Obtain explicit written consent
   - Explain how data will be used

2. **Adverse Action Notice**
   - If application denied based on credit report
   - Must provide specific reasons
   - Include credit bureau contact information
   - Allow user to dispute findings

### Implementation

Create `app/Services/FcraComplianceService.php`:

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Mail\AdverseActionNoticeMail;
use Illuminate\Support\Facades\Mail;

class FcraComplianceService
{
    public function sendPreScreeningNotice(User $user): void
    {
        // Send pre-screening disclosure
        // User must consent before proceeding
    }

    public function sendAdverseActionNotice(User $user, array $reasons): void
    {
        // Required if denying based on credit report
        Mail::to($user->email)->send(new AdverseActionNoticeMail($user, $reasons));
    }

    public function logConsentObtained(User $user): void
    {
        $user->update([
            'credit_check_consent' => true,
            'credit_check_consent_date' => now(),
        ]);
    }
}
```

## Testing

### Sandbox Testing

All providers offer sandbox environments:

**Experian Sandbox**:
```env
EXPERIAN_SANDBOX=true
EXPERIAN_URL=https://sandbox-us-api.experian.com
```

Test SSNs (Experian sandbox):
- `666-55-0000` - Returns good credit
- `666-55-0001` - Returns poor credit
- `666-55-0002` - Returns no credit history

### Unit Tests

Create `tests/Feature/CreditCheckTest.php`:

```php
public function test_credit_check_requires_consent()
{
    $user = User::factory()->create(['credit_check_consent' => false]);
    $verification = GuestVerification::factory()->create(['user_id' => $user->id]);

    $service = new CreditCheckService();
    $result = $service->requestCreditCheck($user, $verification);

    $this->assertFalse($result['success']);
}

public function test_credit_check_success()
{
    config(['services.experian.sandbox' => true]);
    
    $user = User::factory()->create([
        'credit_check_consent' => true,
        'credit_check_consent_date' => now(),
    ]);
    $verification = GuestVerification::factory()->create(['user_id' => $user->id]);

    $service = new CreditCheckService();
    $result = $service->requestCreditCheck($user, $verification);

    $this->assertTrue($result['success']);
    $this->assertNotNull($result['credit_score']);
}
```

## Cost Considerations

- **Experian**: $1.50 - $3.00 per report
- **Equifax**: $2.00 - $4.00 per report
- **TransUnion**: $1.75 - $3.50 per report

Bulk pricing available for high-volume customers.

## Security Best Practices

1. **Encrypt SSN**: Always encrypt at rest
2. **Minimal Storage**: Store only what's legally required
3. **Access Logging**: Log all credit check requests
4. **Data Retention**: Follow FCRA guidelines (typically 7 years)
5. **Secure Transmission**: Use HTTPS/TLS 1.2+
6. **Role-Based Access**: Limit who can initiate checks

## Production Checklist

- [ ] Credit bureau account setup
- [ ] API credentials configured
- [ ] SSL certificate installed
- [ ] User consent workflow implemented
- [ ] Pre-screening notice created
- [ ] Adverse action notice template ready
- [ ] Data encryption enabled
- [ ] Access logging configured
- [ ] FCRA compliance review completed
- [ ] Privacy policy updated
- [ ] Test in sandbox environment
- [ ] Staff training on compliance
- [ ] Dispute resolution process documented

## Resources

- **FCRA Compliance Guide**: https://www.ftc.gov/enforcement/statutes/fair-credit-reporting-act
- **Experian API Docs**: https://developer.experian.com/
- **Equifax API Docs**: https://developer.equifax.com/
- **TransUnion API Docs**: https://www.transunion.com/business
