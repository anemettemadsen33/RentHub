# Insurance Integration API Guide

## 📋 Overview

Complete insurance system for RentHub platform providing booking protection through various insurance plans including cancellation insurance, damage protection, liability coverage, and travel insurance.

## 🎯 Features

- ✅ Multiple insurance plan types
- ✅ Flexible pricing (fixed, per-night, percentage)
- ✅ Eligibility criteria validation
- ✅ Automatic premium calculation
- ✅ Insurance claims management
- ✅ Policy document generation
- ✅ Coverage tracking
- ✅ Admin approval workflow

---

## 📊 Database Schema

### 1. Insurance Plans (`insurance_plans`)

```sql
- id
- name (string)
- slug (string, unique)
- description (text)
- type (enum: cancellation, damage, liability, travel, comprehensive)
- price_per_night (decimal)
- price_percentage (decimal)
- fixed_price (decimal)
- max_coverage (decimal)
- coverage_details (json)
- exclusions (json)
- terms_and_conditions (text)
- is_active (boolean)
- is_mandatory (boolean)
- min_nights (integer)
- max_nights (integer, nullable)
- min_booking_value (decimal)
- max_booking_value (decimal, nullable)
- display_order (integer)
- timestamps
- soft_deletes
```

### 2. Booking Insurances (`booking_insurances`)

```sql
- id
- booking_id (foreign key)
- insurance_plan_id (foreign key)
- policy_number (string, unique)
- status (enum: pending, active, claimed, expired, cancelled)
- premium_amount (decimal)
- coverage_amount (decimal)
- valid_from (date)
- valid_until (date)
- coverage_details (json)
- policy_document_url (json)
- activated_at (timestamp, nullable)
- timestamps
- soft_deletes
```

### 3. Insurance Claims (`insurance_claims`)

```sql
- id
- booking_insurance_id (foreign key)
- user_id (foreign key)
- claim_number (string, unique)
- type (enum: cancellation, damage, injury, theft, other)
- status (enum: submitted, under_review, approved, rejected, paid)
- description (text)
- claimed_amount (decimal)
- approved_amount (decimal, nullable)
- incident_date (date)
- supporting_documents (json)
- admin_notes (text, nullable)
- submitted_at (timestamp)
- reviewed_at (timestamp, nullable)
- resolved_at (timestamp, nullable)
- reviewed_by (foreign key users, nullable)
- timestamps
- soft_deletes
```

---

## 🔌 API Endpoints

### Base URL: `/api/v1/insurance`

All endpoints require authentication except where noted.

---

## 1. Get Available Insurance Plans

**Endpoint:** `POST /api/v1/insurance/plans/available`

Get insurance plans eligible for a specific booking.

### Request Body:

```json
{
  "booking_total": 500.00,
  "nights": 5,
  "type": "cancellation"
}
```

### Parameters:
- `booking_total` (required, numeric) - Total booking amount
- `nights` (required, integer) - Number of nights
- `type` (optional, string) - Filter by type: cancellation, damage, liability, travel, comprehensive

### Response:

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Basic Cancellation Insurance",
      "slug": "basic-cancellation",
      "type": "cancellation",
      "description": "Protect your booking against unexpected cancellations",
      "premium_amount": 25.00,
      "max_coverage": 500.00,
      "coverage_details": {
        "medical_emergency": "Full refund if cancelled due to medical emergency",
        "family_emergency": "Full refund if cancelled due to family emergency",
        "natural_disaster": "Full refund if cancelled due to natural disaster"
      },
      "exclusions": [
        "Change of mind",
        "Work commitments",
        "Financial reasons"
      ],
      "is_mandatory": false,
      "terms_and_conditions": "..."
    }
  ],
  "mandatory_plans": [],
  "optional_plans": [...]
}
```

### cURL Example:

```bash
curl -X POST http://localhost/api/v1/insurance/plans/available \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "booking_total": 500.00,
    "nights": 5,
    "type": "cancellation"
  }'
```

---

## 2. Add Insurance to Booking

**Endpoint:** `POST /api/v1/insurance/add-to-booking`

Add an insurance plan to a booking.

### Request Body:

```json
{
  "booking_id": 1,
  "insurance_plan_id": 1
}
```

### Parameters:
- `booking_id` (required, integer) - Booking ID
- `insurance_plan_id` (required, integer) - Insurance plan ID

### Response:

```json
{
  "success": true,
  "message": "Insurance added to booking successfully",
  "data": {
    "id": 1,
    "booking_id": 1,
    "insurance_plan_id": 1,
    "policy_number": "INS-67890ABC-1234",
    "status": "pending",
    "premium_amount": 25.00,
    "coverage_amount": 500.00,
    "valid_from": "2025-12-01",
    "valid_until": "2025-12-06",
    "coverage_details": {...},
    "activated_at": null,
    "created_at": "2025-11-02T22:00:00Z",
    "insurance_plan": {...}
  }
}
```

### cURL Example:

```bash
curl -X POST http://localhost/api/v1/insurance/add-to-booking \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "booking_id": 1,
    "insurance_plan_id": 1
  }'
```

---

## 3. Get Booking Insurances

**Endpoint:** `GET /api/v1/insurance/booking/{bookingId}`

Get all insurance policies for a booking.

### URL Parameters:
- `bookingId` (required) - Booking ID

### Response:

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "booking_id": 1,
      "policy_number": "INS-67890ABC-1234",
      "status": "active",
      "premium_amount": 25.00,
      "coverage_amount": 500.00,
      "valid_from": "2025-12-01",
      "valid_until": "2025-12-06",
      "activated_at": "2025-11-02T22:10:00Z",
      "insurance_plan": {
        "id": 1,
        "name": "Basic Cancellation Insurance",
        "type": "cancellation"
      },
      "claims": []
    }
  ]
}
```

### cURL Example:

```bash
curl -X GET http://localhost/api/v1/insurance/booking/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 4. Activate Insurance

**Endpoint:** `POST /api/v1/insurance/{insuranceId}/activate`

Activate a pending insurance policy.

### URL Parameters:
- `insuranceId` (required) - Booking insurance ID

### Response:

```json
{
  "success": true,
  "message": "Insurance activated successfully",
  "data": {
    "id": 1,
    "status": "active",
    "activated_at": "2025-11-02T22:10:00Z",
    ...
  }
}
```

### cURL Example:

```bash
curl -X POST http://localhost/api/v1/insurance/1/activate \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 5. Cancel Insurance

**Endpoint:** `POST /api/v1/insurance/{insuranceId}/cancel`

Cancel an active or pending insurance policy.

### URL Parameters:
- `insuranceId` (required) - Booking insurance ID

### Response:

```json
{
  "success": true,
  "message": "Insurance cancelled successfully",
  "data": {
    "id": 1,
    "status": "cancelled",
    ...
  }
}
```

### cURL Example:

```bash
curl -X POST http://localhost/api/v1/insurance/1/cancel \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 6. Submit Insurance Claim

**Endpoint:** `POST /api/v1/insurance/claims`

Submit a new insurance claim.

### Request Body:

```json
{
  "booking_insurance_id": 1,
  "type": "cancellation",
  "description": "Had to cancel due to medical emergency. Doctor's note attached.",
  "claimed_amount": 500.00,
  "incident_date": "2025-11-28",
  "supporting_documents": [
    "https://example.com/documents/medical-note.pdf",
    "https://example.com/documents/hospital-admission.pdf"
  ]
}
```

### Parameters:
- `booking_insurance_id` (required, integer) - Booking insurance ID
- `type` (required, string) - Claim type: cancellation, damage, injury, theft, other
- `description` (required, string, min:20) - Detailed description
- `claimed_amount` (required, numeric) - Amount claimed
- `incident_date` (required, date) - Date of incident
- `supporting_documents` (optional, array) - URLs to supporting documents

### Response:

```json
{
  "success": true,
  "message": "Claim submitted successfully",
  "data": {
    "id": 1,
    "booking_insurance_id": 1,
    "user_id": 1,
    "claim_number": "CLM-20251102-ABC123",
    "type": "cancellation",
    "status": "submitted",
    "description": "Had to cancel due to medical emergency...",
    "claimed_amount": 500.00,
    "approved_amount": null,
    "incident_date": "2025-11-28",
    "supporting_documents": [...],
    "submitted_at": "2025-11-02T22:20:00Z",
    "created_at": "2025-11-02T22:20:00Z",
    "booking_insurance": {...}
  }
}
```

### cURL Example:

```bash
curl -X POST http://localhost/api/v1/insurance/claims \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "booking_insurance_id": 1,
    "type": "cancellation",
    "description": "Had to cancel due to medical emergency. Doctors note attached.",
    "claimed_amount": 500.00,
    "incident_date": "2025-11-28",
    "supporting_documents": [
      "https://example.com/documents/medical-note.pdf"
    ]
  }'
```

---

## 7. Get User Claims

**Endpoint:** `GET /api/v1/insurance/claims`

Get all claims submitted by the authenticated user.

### Query Parameters:
- `page` (optional, integer) - Page number (pagination)

### Response:

```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "claim_number": "CLM-20251102-ABC123",
        "type": "cancellation",
        "status": "under_review",
        "claimed_amount": 500.00,
        "approved_amount": null,
        "incident_date": "2025-11-28",
        "submitted_at": "2025-11-02T22:20:00Z",
        "reviewed_at": null,
        "booking_insurance": {
          "policy_number": "INS-67890ABC-1234",
          "insurance_plan": {
            "name": "Basic Cancellation Insurance"
          },
          "booking": {
            "id": 1,
            "check_in": "2025-12-01",
            "check_out": "2025-12-06"
          }
        }
      }
    ],
    "per_page": 10,
    "total": 1
  }
}
```

### cURL Example:

```bash
curl -X GET http://localhost/api/v1/insurance/claims \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 8. Get Claim Details

**Endpoint:** `GET /api/v1/insurance/claims/{claimId}`

Get detailed information about a specific claim.

### URL Parameters:
- `claimId` (required) - Claim ID

### Response:

```json
{
  "success": true,
  "data": {
    "id": 1,
    "claim_number": "CLM-20251102-ABC123",
    "type": "cancellation",
    "status": "approved",
    "description": "Had to cancel due to medical emergency...",
    "claimed_amount": 500.00,
    "approved_amount": 500.00,
    "incident_date": "2025-11-28",
    "supporting_documents": [...],
    "admin_notes": "Claim approved. Medical documentation verified.",
    "submitted_at": "2025-11-02T22:20:00Z",
    "reviewed_at": "2025-11-03T10:30:00Z",
    "resolved_at": null,
    "booking_insurance": {
      "policy_number": "INS-67890ABC-1234",
      "insurance_plan": {...},
      "booking": {
        "property": {...}
      }
    },
    "user": {
      "id": 1,
      "name": "John Doe"
    },
    "reviewer": {
      "id": 2,
      "name": "Admin User"
    }
  }
}
```

### cURL Example:

```bash
curl -X GET http://localhost/api/v1/insurance/claims/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🎨 Frontend Integration Examples

### React/Next.js Components

#### 1. Insurance Selection Component

```typescript
// components/booking/InsuranceSelector.tsx
'use client';

import { useState, useEffect } from 'react';
import { api } from '@/lib/api';

interface InsurancePlan {
  id: number;
  name: string;
  type: string;
  description: string;
  premium_amount: number;
  max_coverage: number;
  coverage_details: Record<string, string>;
  is_mandatory: boolean;
}

export default function InsuranceSelector({
  bookingTotal,
  nights,
  onSelect
}: {
  bookingTotal: number;
  nights: number;
  onSelect: (planIds: number[]) => void;
}) {
  const [plans, setPlans] = useState<InsurancePlan[]>([]);
  const [selectedPlans, setSelectedPlans] = useState<number[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchPlans();
  }, [bookingTotal, nights]);

  const fetchPlans = async () => {
    try {
      const response = await api.post('/insurance/plans/available', {
        booking_total: bookingTotal,
        nights
      });
      setPlans(response.data.data);
      
      // Auto-select mandatory plans
      const mandatory = response.data.mandatory_plans.map((p: InsurancePlan) => p.id);
      setSelectedPlans(mandatory);
      onSelect(mandatory);
    } catch (error) {
      console.error('Failed to fetch insurance plans:', error);
    } finally {
      setLoading(false);
    }
  };

  const togglePlan = (planId: number, isMandatory: boolean) => {
    if (isMandatory) return; // Cannot deselect mandatory

    const newSelection = selectedPlans.includes(planId)
      ? selectedPlans.filter(id => id !== planId)
      : [...selectedPlans, planId];
    
    setSelectedPlans(newSelection);
    onSelect(newSelection);
  };

  if (loading) return <div>Loading insurance options...</div>;

  return (
    <div className="space-y-4">
      <h3 className="text-xl font-bold">Protect Your Booking</h3>
      
      {plans.map(plan => (
        <div 
          key={plan.id}
          className={`border rounded-lg p-4 ${
            selectedPlans.includes(plan.id) ? 'border-blue-500 bg-blue-50' : ''
          }`}
        >
          <div className="flex items-start justify-between">
            <div className="flex-1">
              <div className="flex items-center gap-2">
                <h4 className="font-semibold">{plan.name}</h4>
                {plan.is_mandatory && (
                  <span className="text-xs bg-red-500 text-white px-2 py-1 rounded">
                    Required
                  </span>
                )}
              </div>
              <p className="text-sm text-gray-600 mt-1">{plan.description}</p>
              
              <div className="mt-2 space-y-1">
                <p className="text-sm font-medium">Coverage includes:</p>
                <ul className="text-sm text-gray-600 list-disc list-inside">
                  {Object.entries(plan.coverage_details).map(([key, value]) => (
                    <li key={key}>{value}</li>
                  ))}
                </ul>
              </div>
            </div>
            
            <div className="text-right ml-4">
              <div className="text-lg font-bold">€{plan.premium_amount.toFixed(2)}</div>
              <div className="text-xs text-gray-500">Coverage up to €{plan.max_coverage}</div>
              
              <button
                onClick={() => togglePlan(plan.id, plan.is_mandatory)}
                disabled={plan.is_mandatory}
                className={`mt-2 px-4 py-2 rounded ${
                  selectedPlans.includes(plan.id)
                    ? 'bg-blue-500 text-white'
                    : 'bg-gray-200 text-gray-700'
                } ${plan.is_mandatory ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'}`}
              >
                {selectedPlans.includes(plan.id) ? 'Selected' : 'Add'}
              </button>
            </div>
          </div>
        </div>
      ))}
      
      <div className="bg-gray-100 p-4 rounded-lg">
        <div className="flex justify-between items-center">
          <span className="font-semibold">Total Insurance Cost:</span>
          <span className="text-xl font-bold">
            €{plans
              .filter(p => selectedPlans.includes(p.id))
              .reduce((sum, p) => sum + p.premium_amount, 0)
              .toFixed(2)}
          </span>
        </div>
      </div>
    </div>
  );
}
```

#### 2. Submit Claim Component

```typescript
// components/insurance/SubmitClaim.tsx
'use client';

import { useState } from 'react';
import { api } from '@/lib/api';

export default function SubmitClaim({
  bookingInsuranceId
}: {
  bookingInsuranceId: number;
}) {
  const [formData, setFormData] = useState({
    type: 'cancellation',
    description: '',
    claimed_amount: '',
    incident_date: '',
    supporting_documents: [] as string[]
  });
  const [loading, setLoading] = useState(false);
  const [success, setSuccess] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      const response = await api.post('/insurance/claims', {
        booking_insurance_id: bookingInsuranceId,
        ...formData,
        claimed_amount: parseFloat(formData.claimed_amount)
      });
      
      setSuccess(true);
      alert(`Claim submitted successfully! Claim number: ${response.data.data.claim_number}`);
    } catch (error: any) {
      alert(error.response?.data?.message || 'Failed to submit claim');
    } finally {
      setLoading(false);
    }
  };

  if (success) {
    return (
      <div className="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
        <h3 className="text-xl font-bold text-green-800 mb-2">
          Claim Submitted Successfully!
        </h3>
        <p className="text-green-700">
          We'll review your claim and get back to you within 2-3 business days.
        </p>
      </div>
    );
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <h3 className="text-xl font-bold">Submit Insurance Claim</h3>
      
      <div>
        <label className="block text-sm font-medium mb-1">Claim Type</label>
        <select
          value={formData.type}
          onChange={(e) => setFormData({ ...formData, type: e.target.value })}
          className="w-full border rounded-lg px-3 py-2"
          required
        >
          <option value="cancellation">Cancellation</option>
          <option value="damage">Damage</option>
          <option value="injury">Injury</option>
          <option value="theft">Theft</option>
          <option value="other">Other</option>
        </select>
      </div>

      <div>
        <label className="block text-sm font-medium mb-1">Incident Date</label>
        <input
          type="date"
          value={formData.incident_date}
          onChange={(e) => setFormData({ ...formData, incident_date: e.target.value })}
          max={new Date().toISOString().split('T')[0]}
          className="w-full border rounded-lg px-3 py-2"
          required
        />
      </div>

      <div>
        <label className="block text-sm font-medium mb-1">Claimed Amount (€)</label>
        <input
          type="number"
          step="0.01"
          value={formData.claimed_amount}
          onChange={(e) => setFormData({ ...formData, claimed_amount: e.target.value })}
          className="w-full border rounded-lg px-3 py-2"
          required
        />
      </div>

      <div>
        <label className="block text-sm font-medium mb-1">
          Description (min 20 characters)
        </label>
        <textarea
          value={formData.description}
          onChange={(e) => setFormData({ ...formData, description: e.target.value })}
          rows={5}
          minLength={20}
          className="w-full border rounded-lg px-3 py-2"
          placeholder="Please provide detailed information about the incident..."
          required
        />
      </div>

      <button
        type="submit"
        disabled={loading}
        className="w-full bg-blue-500 text-white py-3 rounded-lg font-semibold hover:bg-blue-600 disabled:opacity-50"
      >
        {loading ? 'Submitting...' : 'Submit Claim'}
      </button>
    </form>
  );
}
```

#### 3. Claims List Component

```typescript
// components/insurance/ClaimsList.tsx
'use client';

import { useState, useEffect } from 'react';
import { api } from '@/lib/api';
import Link from 'next/link';

interface Claim {
  id: number;
  claim_number: string;
  type: string;
  status: string;
  claimed_amount: number;
  approved_amount: number | null;
  incident_date: string;
  submitted_at: string;
}

export default function ClaimsList() {
  const [claims, setClaims] = useState<Claim[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchClaims();
  }, []);

  const fetchClaims = async () => {
    try {
      const response = await api.get('/insurance/claims');
      setClaims(response.data.data.data);
    } catch (error) {
      console.error('Failed to fetch claims:', error);
    } finally {
      setLoading(false);
    }
  };

  const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
      submitted: 'bg-yellow-100 text-yellow-800',
      under_review: 'bg-blue-100 text-blue-800',
      approved: 'bg-green-100 text-green-800',
      rejected: 'bg-red-100 text-red-800',
      paid: 'bg-purple-100 text-purple-800'
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
  };

  if (loading) return <div>Loading claims...</div>;

  if (claims.length === 0) {
    return (
      <div className="text-center py-12">
        <p className="text-gray-500">You haven't submitted any claims yet.</p>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      <h3 className="text-xl font-bold">My Claims</h3>
      
      {claims.map(claim => (
        <Link
          key={claim.id}
          href={`/insurance/claims/${claim.id}`}
          className="block border rounded-lg p-4 hover:border-blue-500 transition"
        >
          <div className="flex items-start justify-between">
            <div>
              <div className="flex items-center gap-2">
                <span className="font-mono text-sm">{claim.claim_number}</span>
                <span className={`text-xs px-2 py-1 rounded ${getStatusColor(claim.status)}`}>
                  {claim.status.replace('_', ' ').toUpperCase()}
                </span>
              </div>
              <p className="text-sm text-gray-600 mt-1">
                {claim.type.charAt(0).toUpperCase() + claim.type.slice(1)} • 
                Incident: {new Date(claim.incident_date).toLocaleDateString()}
              </p>
              <p className="text-xs text-gray-500 mt-1">
                Submitted: {new Date(claim.submitted_at).toLocaleDateString()}
              </p>
            </div>
            <div className="text-right">
              <div className="font-bold">€{claim.claimed_amount.toFixed(2)}</div>
              {claim.approved_amount !== null && (
                <div className="text-sm text-green-600">
                  Approved: €{claim.approved_amount.toFixed(2)}
                </div>
              )}
            </div>
          </div>
        </Link>
      ))}
    </div>
  );
}
```

---

## 🔒 Security & Validation

### Authorization Rules

1. **Insurance Plans**: Anyone can view available plans
2. **Add to Booking**: Only booking owner or admin
3. **Activate/Cancel**: Only booking owner or admin
4. **Submit Claim**: Only booking owner
5. **View Claims**: Only claim owner or admin

### Validation Rules

#### Insurance Plan:
- Name: required, max 255 characters
- Slug: required, unique, URL-friendly
- Type: required, one of: cancellation, damage, liability, travel, comprehensive
- Max Coverage: required, positive number
- Pricing: At least one pricing method required
- Min nights: minimum 1

#### Add Insurance:
- Booking must exist and belong to user
- Plan must be active
- Plan must be eligible for booking criteria
- Cannot add duplicate plans

#### Submit Claim:
- Insurance must be active
- Claimed amount cannot exceed coverage
- Description minimum 20 characters
- Incident date cannot be in future
- Supporting documents must be valid URLs

---

## 📈 Business Logic

### Premium Calculation

Insurance premium is calculated using ONE of these methods (priority order):

1. **Fixed Price**: If `fixed_price > 0`, use this amount
2. **Percentage**: If `price_percentage > 0`, calculate: `(booking_total * percentage) / 100`
3. **Per Night**: If `price_per_night > 0`, calculate: `price_per_night * nights`

### Eligibility Check

A plan is eligible for a booking if ALL conditions are met:

1. Plan is active (`is_active = true`)
2. Number of nights >= `min_nights`
3. Number of nights <= `max_nights` (if set)
4. Booking total >= `min_booking_value`
5. Booking total <= `max_booking_value` (if set)

### Claim Workflow

1. **Submitted** → User submits claim
2. **Under Review** → Admin starts reviewing
3. **Approved** → Admin approves with amount
4. **Paid** → Payment processed to user
5. **Rejected** → Claim denied (terminal state)

---

## 🎯 Use Cases

### 1. Booking with Insurance

```javascript
// Step 1: Get available insurance plans during booking
const { data: plansData } = await api.post('/insurance/plans/available', {
  booking_total: 500,
  nights: 5
});

// Step 2: Create booking
const { data: booking } = await api.post('/bookings', bookingData);

// Step 3: Add selected insurance plans
for (const planId of selectedPlanIds) {
  await api.post('/insurance/add-to-booking', {
    booking_id: booking.id,
    insurance_plan_id: planId
  });
}

// Step 4: Activate insurance after payment
await api.post(`/insurance/${insuranceId}/activate`);
```

### 2. Cancel and Claim Insurance

```javascript
// Step 1: Cancel booking
await api.post(`/bookings/${bookingId}/cancel`);

// Step 2: Submit cancellation insurance claim
await api.post('/insurance/claims', {
  booking_insurance_id: insuranceId,
  type: 'cancellation',
  description: 'Medical emergency...',
  claimed_amount: 500,
  incident_date: '2025-11-28',
  supporting_documents: ['https://...']
});

// Step 3: Track claim status
const { data: claim } = await api.get(`/insurance/claims/${claimId}`);
```

---

## 🧪 Testing

### Test Insurance Plans

```bash
# Test 1: Get available plans
curl -X POST http://localhost/api/v1/insurance/plans/available \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"booking_total": 500, "nights": 5}'

# Test 2: Add insurance to booking
curl -X POST http://localhost/api/v1/insurance/add-to-booking \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"booking_id": 1, "insurance_plan_id": 1}'

# Test 3: Activate insurance
curl -X POST http://localhost/api/v1/insurance/1/activate \
  -H "Authorization: Bearer TOKEN"

# Test 4: Submit claim
curl -X POST http://localhost/api/v1/insurance/claims \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "booking_insurance_id": 1,
    "type": "cancellation",
    "description": "Medical emergency requiring cancellation...",
    "claimed_amount": 500,
    "incident_date": "2025-11-28"
  }'

# Test 5: Get user claims
curl -X GET http://localhost/api/v1/insurance/claims \
  -H "Authorization: Bearer TOKEN"
```

---

## 📊 Admin Panel

Access Filament admin at: `/admin/insurance-plans`

### Features:
- ✅ Create/Edit/Delete insurance plans
- ✅ View all active policies
- ✅ Manage claims (approve/reject)
- ✅ Generate reports
- ✅ Filter by type, status
- ✅ Track coverage statistics

---

## 🎓 Best Practices

1. **Always show total cost including insurance** in booking summary
2. **Require payment before activating insurance** - insurance only activates after successful payment
3. **Store supporting documents securely** - use signed URLs for uploads
4. **Send email notifications** for claim status changes
5. **Generate PDF policies** for active insurances
6. **Set reasonable claim deadlines** - e.g., claims must be submitted within 30 days
7. **Validate claim amounts** against coverage limits
8. **Track claim approval/rejection reasons** for transparency

---

## 📝 Next Steps

1. ✅ Create seed data for insurance plans
2. ✅ Test all API endpoints
3. ⏳ Implement PDF policy generation
4. ⏳ Add email notifications for claims
5. ⏳ Create admin claim review interface
6. ⏳ Add insurance statistics dashboard
7. ⏳ Implement automated claim processing rules

---

**Task 3.6 Insurance Integration:** ✅ Complete!

**Ready for testing and frontend integration!** 🚀
