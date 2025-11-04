# 🚀 Quick Start: Insurance Integration

## ⚡ Fast Setup (5 minutes)

### 1. Database Setup
```bash
cd backend
php artisan migrate
php artisan db:seed --class=InsurancePlanSeeder
```

✅ Creates 3 tables: insurance_plans, booking_insurances, insurance_claims  
✅ Seeds 7 insurance plans (1 mandatory, 6 optional)

### 2. Test API
```bash
# Get available plans
curl -X POST http://localhost/api/v1/insurance/plans/available \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"booking_total": 500, "nights": 5}'

# Expected: List of 6-7 insurance plans with calculated premiums
```

### 3. Admin Panel
- Navigate to: `http://localhost/admin/insurance-plans`
- Create, edit, or view insurance plans
- Check "Active Policies" count

---

## 📋 Available Insurance Plans

After seeding, you'll have:

1. **Mandatory Security Protection** - €10 fixed (required)
2. **Basic Cancellation** - 5% of booking
3. **Premium Cancellation** - 10% of booking  
4. **Damage Protection** - €5/night
5. **Liability Coverage** - €25 fixed
6. **Travel Insurance** - 7.5% of booking
7. **Comprehensive Package** - 15% of booking (all-in-one)

---

## 🔌 Quick Integration

### Step 1: Get Available Plans
```javascript
const response = await fetch('/api/v1/insurance/plans/available', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    booking_total: 500,
    nights: 5
  })
});

const { data, mandatory_plans, optional_plans } = await response.json();
```

### Step 2: Add to Booking
```javascript
await fetch('/api/v1/insurance/add-to-booking', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    booking_id: 1,
    insurance_plan_id: 1
  })
});

// Returns: BookingInsurance with policy_number
```

### Step 3: Activate After Payment
```javascript
await fetch(`/api/v1/insurance/${insuranceId}/activate`, {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token
  }
});

// Insurance now active!
```

### Step 4: Submit Claim (if needed)
```javascript
await fetch('/api/v1/insurance/claims', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    booking_insurance_id: 1,
    type: 'cancellation',
    description: 'Medical emergency...',
    claimed_amount: 500,
    incident_date: '2025-11-28',
    supporting_documents: ['https://...']
  })
});

// Returns: Claim with claim_number
```

---

## 💻 Frontend Components

### Use Pre-built Components

```tsx
import InsuranceSelector from '@/components/booking/InsuranceSelector';
import SubmitClaim from '@/components/insurance/SubmitClaim';
import ClaimsList from '@/components/insurance/ClaimsList';

// In booking page
<InsuranceSelector
  bookingTotal={500}
  nights={5}
  onSelect={(planIds) => setSelectedInsurance(planIds)}
/>

// In claim page
<SubmitClaim bookingInsuranceId={1} />

// In user dashboard
<ClaimsList />
```

Component files available in `INSURANCE_API_GUIDE.md`

---

## 🧪 Testing Checklist

- [ ] Get available plans
- [ ] Add insurance to booking
- [ ] Check mandatory plans are included
- [ ] Activate insurance
- [ ] Submit claim
- [ ] View user claims
- [ ] Test admin panel

### Quick Test Commands

```bash
# Create test booking first
curl -X POST http://localhost/api/v1/bookings \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{...booking data...}'

# Get insurance plans
curl -X POST http://localhost/api/v1/insurance/plans/available \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"booking_total": 500, "nights": 5}'

# Add insurance
curl -X POST http://localhost/api/v1/insurance/add-to-booking \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"booking_id": 1, "insurance_plan_id": 1}'

# Activate
curl -X POST http://localhost/api/v1/insurance/1/activate \
  -H "Authorization: Bearer TOKEN"

# Submit claim
curl -X POST http://localhost/api/v1/insurance/claims \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "booking_insurance_id": 1,
    "type": "cancellation",
    "description": "Medical emergency requiring cancellation",
    "claimed_amount": 500,
    "incident_date": "2025-11-28"
  }'
```

---

## 💰 Pricing Examples

### Example 1: Weekend Getaway
- **Booking:** €300, 2 nights
- **Mandatory Security:** €10
- **Damage Protection:** €10 (€5 × 2)
- **Total Insurance:** €20
- **Booking + Insurance:** €320

### Example 2: Week Vacation
- **Booking:** €700, 7 nights
- **Mandatory Security:** €10
- **Basic Cancellation:** €35 (5%)
- **Damage Protection:** €35 (€5 × 7)
- **Total Insurance:** €80
- **Booking + Insurance:** €780

### Example 3: Premium Protection
- **Booking:** €1500, 10 nights
- **Comprehensive Package:** €225 (15%)
- **Total Insurance:** €225
- **Booking + Insurance:** €1725

---

## 🎯 Common Use Cases

### 1. Booking with Insurance
```
User selects property → 
Calculate total → 
Show insurance options → 
User selects plans → 
Complete payment → 
Activate insurances → 
Send confirmation
```

### 2. Cancel with Insurance Claim
```
User cancels booking → 
Check if has cancellation insurance → 
Show claim form → 
Submit claim with documents → 
Admin reviews → 
Approve/Reject → 
Process refund
```

### 3. Damage Claim
```
Checkout occurs → 
Owner reports damage → 
Guest notified → 
Check if has damage insurance → 
Guest files claim → 
Admin reviews evidence → 
Process claim
```

---

## 📚 Full Documentation

- **Complete API Docs:** `INSURANCE_API_GUIDE.md`
- **Implementation Details:** `TASK_3.6_INSURANCE_INTEGRATION_COMPLETE.md`
- **This Quick Start:** `START_HERE_INSURANCE.md`

---

## 🔗 API Endpoints Summary

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/insurance/plans/available` | Get eligible plans |
| POST | `/insurance/add-to-booking` | Add insurance |
| GET | `/insurance/booking/{id}` | Get booking insurances |
| POST | `/insurance/{id}/activate` | Activate insurance |
| POST | `/insurance/{id}/cancel` | Cancel insurance |
| POST | `/insurance/claims` | Submit claim |
| GET | `/insurance/claims` | List user claims |
| GET | `/insurance/claims/{id}` | Get claim details |

All require authentication (Bearer token).

---

## 🎨 Admin Features

**Access:** `/admin/insurance-plans`

**Can Do:**
- ✅ Create new insurance plans
- ✅ Edit existing plans
- ✅ Configure pricing (3 methods)
- ✅ Set coverage details
- ✅ Add exclusions
- ✅ Toggle active/mandatory
- ✅ View policies count
- ✅ Filter by type
- ✅ Soft delete support

---

## ⚠️ Important Notes

### Mandatory Insurance
- At least ONE plan must have `is_mandatory = true`
- Automatically added to all bookings
- Cannot be deselected by users
- Usually lowest-cost basic protection

### Pricing Configuration
- Use **ONE** pricing method per plan:
  - Fixed price (€X per booking)
  - Per night (€X per night)
  - Percentage (X% of booking total)
- System uses first non-zero value

### Claims
- Must be submitted within policy validity period
- Claimed amount cannot exceed max_coverage
- Requires supporting documents
- Admin review required for approval
- Refunds processed separately

---

## 🚨 Troubleshooting

### "Plan not eligible"
Check: min_nights, max_nights, min_booking_value, max_booking_value

### "Cannot add duplicate"
Each plan can only be added once per booking

### "Cannot activate"
Insurance must be in 'pending' status

### "Cannot claim"
Insurance must be 'active' and within valid dates

### "Claimed amount exceeds coverage"
Check max_coverage limit of the plan

---

## ✅ You're Ready!

The insurance system is now fully functional. Start integrating into your booking flow!

**Questions?** Check the full documentation in `INSURANCE_API_GUIDE.md`

🚀 Happy coding!
