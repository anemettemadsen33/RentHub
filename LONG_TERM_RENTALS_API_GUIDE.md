# 🏠 Long-Term Rentals API Guide

## 📋 Overview

Sistemul de Long-Term Rentals permite gestionarea contractelor pe termen lung (lunar, trimestrial, anual) cu:
- ✅ Depozit + Chirie lunară
- ✅ Payment schedule automat
- ✅ Lease agreement generation
- ✅ Maintenance requests
- ✅ Renewal options
- ✅ Invoice automation

---

## 🗄️ Database Structure

### Tables Created:
1. **long_term_rentals** - Contracte principale
2. **rent_payments** - Plăți programate (depozit + chirie)
3. **maintenance_requests** - Cereri de întreținere

---

## 📡 API Endpoints

### Base URL: `/api/v1`

### 🏠 Long-Term Rentals

#### 1. **Get All Rentals**
```http
GET /api/v1/long-term-rentals
Authorization: Bearer {token}
```

**Query Parameters:**
- `status` - Filter by status (draft, pending_approval, active, completed, cancelled, terminated)
- `property_id` - Filter by property
- `tenant_id` - Filter by tenant
- `owner_id` - Filter by owner
- `expiring_soon` - Boolean (default 30 days)
- `days` - Days for expiring soon filter
- `start_date` - Filter by start date
- `end_date` - Filter by end date
- `per_page` - Results per page (default 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "property_id": 5,
      "tenant_id": 10,
      "owner_id": 3,
      "start_date": "2025-12-01",
      "end_date": "2026-12-01",
      "duration_months": 12,
      "rental_type": "monthly",
      "monthly_rent": 1500.00,
      "security_deposit": 3000.00,
      "total_rent": 18000.00,
      "payment_frequency": "monthly",
      "payment_day_of_month": 1,
      "deposit_status": "held",
      "status": "active",
      "auto_renewable": true,
      "renewal_notice_days": 30,
      "utilities_included": ["water", "trash"],
      "pets_allowed": false,
      "smoking_allowed": false,
      "property": {...},
      "tenant": {...},
      "owner": {...}
    }
  ],
  "meta": {...},
  "links": {...}
}
```

---

#### 2. **Create Rental**
```http
POST /api/v1/long-term-rentals
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "property_id": 5,
  "tenant_id": 10,
  "start_date": "2025-12-01",
  "duration_months": 12,
  "rental_type": "monthly",
  "monthly_rent": 1500.00,
  "security_deposit": 3000.00,
  "payment_frequency": "monthly",
  "payment_day_of_month": 1,
  "utilities_included": ["water", "trash"],
  "utilities_paid_by_tenant": ["electricity", "internet"],
  "utilities_estimate": 150.00,
  "maintenance_included": true,
  "maintenance_terms": "All repairs covered by owner",
  "auto_renewable": true,
  "renewal_notice_days": 30,
  "special_terms": "No parties allowed",
  "house_rules": ["No smoking", "Quiet hours 10pm-8am"],
  "pets_allowed": false,
  "smoking_allowed": false
}
```

**Validation Rules:**
- `property_id` - required, exists
- `tenant_id` - required, exists
- `start_date` - required, date, after:today
- `duration_months` - required, integer, min:1, max:120
- `rental_type` - required, in:monthly,quarterly,yearly
- `monthly_rent` - required, numeric, min:0
- `security_deposit` - required, numeric, min:0
- `payment_frequency` - required, in:monthly,quarterly,yearly
- `payment_day_of_month` - optional, integer, min:1, max:28

**Response:**
```json
{
  "message": "Long-term rental created successfully",
  "rental": {...}
}
```

---

#### 3. **Get Rental Details**
```http
GET /api/v1/long-term-rentals/{id}
Authorization: Bearer {token}
```

**Response includes:**
- Rental details
- Property info
- Tenant & Owner info
- All rent payments (ordered by due_date)
- All maintenance requests

---

#### 4. **Update Rental**
```http
PUT /api/v1/long-term-rentals/{id}
Authorization: Bearer {token}
```

**Allowed Updates:**
- `monthly_rent`
- `security_deposit`
- `payment_day_of_month`
- `utilities_included`
- `utilities_paid_by_tenant`
- `utilities_estimate`
- `maintenance_included`
- `maintenance_terms`
- `special_terms`
- `house_rules`
- `status`

---

#### 5. **Activate Rental** ⭐ Important
```http
POST /api/v1/long-term-rentals/{id}/activate
Authorization: Bearer {token}
```

**Ce face:**
1. Changes status from `draft` → `active`
2. Sets `lease_signed_at` to current date
3. **Generează automat payment schedule:**
   - 1 x Deposit payment (due 7 days before move-in)
   - N x Monthly rent payments (based on duration)

**Response:**
```json
{
  "message": "Rental activated successfully",
  "rental": {
    ...includes all rent_payments
  }
}
```

---

#### 6. **Request Renewal**
```http
POST /api/v1/long-term-rentals/{id}/request-renewal
Authorization: Bearer {token}
```

**Request:**
```json
{
  "duration_months": 12,
  "monthly_rent": 1600.00
}
```

---

#### 7. **Cancel Rental**
```http
POST /api/v1/long-term-rentals/{id}/cancel
Authorization: Bearer {token}
```

**Request:**
```json
{
  "reason": "Tenant relocating for work"
}
```

---

#### 8. **Get Statistics**
```http
GET /api/v1/long-term-rentals/statistics
Authorization: Bearer {token}
```

**Query Parameters:**
- `owner_id` - Filter by owner
- `tenant_id` - Filter by tenant

**Response:**
```json
{
  "total": 45,
  "active": 38,
  "pending": 3,
  "completed": 2,
  "expiring_30_days": 5,
  "total_monthly_revenue": 57000.00,
  "total_deposits_held": 114000.00
}
```

---

### 💰 Rent Payments

#### 1. **Get All Payments**
```http
GET /api/v1/rent-payments
Authorization: Bearer {token}
```

**Query Parameters:**
- `long_term_rental_id` - Filter by rental
- `tenant_id` - Filter by tenant
- `status` - Filter by status (scheduled, pending, paid, overdue, failed)
- `overdue` - Boolean, show only overdue
- `upcoming` - Boolean, show upcoming
- `days` - Days for upcoming filter (default 7)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "long_term_rental_id": 1,
      "tenant_id": 10,
      "payment_type": "deposit",
      "due_date": "2025-11-24",
      "amount_due": 3000.00,
      "amount_paid": 3000.00,
      "late_fee": 0,
      "discount": 0,
      "status": "paid",
      "payment_date": "2025-11-22",
      "payment_method": "bank_transfer",
      "transaction_id": "TXN123456",
      "invoice_id": 45
    },
    {
      "id": 2,
      "long_term_rental_id": 1,
      "tenant_id": 10,
      "payment_type": "monthly_rent",
      "month_number": 1,
      "due_date": "2025-12-01",
      "amount_due": 1500.00,
      "amount_paid": 0,
      "status": "scheduled"
    }
  ]
}
```

---

#### 2. **Mark Payment as Paid** ⭐
```http
POST /api/v1/rent-payments/{id}/mark-as-paid
Authorization: Bearer {token}
```

**Request:**
```json
{
  "amount": 1500.00,
  "payment_method": "bank_transfer",
  "transaction_id": "TXN789456",
  "generate_invoice": true
}
```

**Ce face:**
1. Marks payment as paid
2. Sets payment_date, amount_paid, payment_method
3. **Automatically generates invoice** (if generate_invoice=true)
4. **Sends invoice email to tenant**

---

#### 3. **Update Overdue Payments**
```http
POST /api/v1/rent-payments/update-overdue
Authorization: Bearer {token}
```

Rulează logic pentru calculare late fees și update status.

---

#### 4. **Send Payment Reminder**
```http
POST /api/v1/rent-payments/{id}/send-reminder
Authorization: Bearer {token}
```

---

### 🔧 Maintenance Requests

#### 1. **Get All Maintenance Requests**
```http
GET /api/v1/maintenance-requests
Authorization: Bearer {token}
```

**Query Parameters:**
- `long_term_rental_id` - Filter by rental
- `property_id` - Filter by property
- `tenant_id` - Filter by tenant
- `status` - submitted, acknowledged, scheduled, in_progress, completed, cancelled
- `priority` - low, medium, high, urgent
- `urgent` - Boolean, show only urgent
- `open` - Boolean, show only open requests

---

#### 2. **Create Maintenance Request**
```http
POST /api/v1/maintenance-requests
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request:**
```json
{
  "long_term_rental_id": 1,
  "property_id": 5,
  "tenant_id": 10,
  "title": "Leaking faucet in kitchen",
  "description": "Kitchen sink faucet drips constantly",
  "category": "plumbing",
  "priority": "high",
  "preferred_date": "2025-11-10 14:00:00",
  "requires_access": true,
  "access_instructions": "Key with neighbor in Apt 102",
  "photos[]": [file1.jpg, file2.jpg]
}
```

**Categories:**
- plumbing
- electrical
- hvac
- appliance
- structural
- pest_control
- cleaning
- other

**Priorities:**
- low
- medium
- high
- urgent

---

#### 3. **Assign Request**
```http
POST /api/v1/maintenance-requests/{id}/assign
Authorization: Bearer {token}
```

```json
{
  "assigned_to": 25
}
```

---

#### 4. **Complete Request**
```http
POST /api/v1/maintenance-requests/{id}/complete
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

```json
{
  "resolution_notes": "Replaced faucet cartridge",
  "actual_cost": 75.00,
  "completion_photos[]": [after1.jpg, after2.jpg]
}
```

---

## 🎨 Frontend Implementation

### Next.js Components Needed:

#### 1. **Owner Dashboard - Long-term Rentals Page**

```tsx
// components/owner/LongTermRentals.tsx
import { useState, useEffect } from 'react';

interface Rental {
  id: number;
  property: Property;
  tenant: User;
  start_date: string;
  end_date: string;
  monthly_rent: number;
  status: string;
  days_until_expiry?: number;
}

export function LongTermRentalsList() {
  const [rentals, setRentals] = useState<Rental[]>([]);
  
  useEffect(() => {
    fetch('/api/v1/long-term-rentals', {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    .then(res => res.json())
    .then(data => setRentals(data.data));
  }, []);

  return (
    <div>
      <h1>My Long-term Rentals</h1>
      
      {/* Statistics Cards */}
      <div className="grid grid-cols-4 gap-4 mb-8">
        <StatCard title="Active Rentals" value={stats.active} />
        <StatCard title="Expiring Soon" value={stats.expiring_30_days} />
        <StatCard title="Monthly Revenue" value={stats.total_monthly_revenue} />
        <StatCard title="Deposits Held" value={stats.total_deposits_held} />
      </div>

      {/* Rentals Table */}
      <table>
        <thead>
          <tr>
            <th>Property</th>
            <th>Tenant</th>
            <th>Period</th>
            <th>Monthly Rent</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {rentals.map(rental => (
            <tr key={rental.id}>
              <td>{rental.property.title}</td>
              <td>{rental.tenant.name}</td>
              <td>
                {formatDate(rental.start_date)} - {formatDate(rental.end_date)}
              </td>
              <td>${rental.monthly_rent}</td>
              <td>
                <StatusBadge status={rental.status} />
              </td>
              <td>
                <button onClick={() => viewDetails(rental.id)}>View</button>
                {rental.status === 'draft' && (
                  <button onClick={() => activateRental(rental.id)}>
                    Activate
                  </button>
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
```

---

#### 2. **Create Long-term Rental Form**

```tsx
// components/owner/CreateLongTermRental.tsx

export function CreateLongTermRentalForm({ propertyId }: { propertyId: number }) {
  const [formData, setFormData] = useState({
    property_id: propertyId,
    tenant_id: '',
    start_date: '',
    duration_months: 12,
    monthly_rent: '',
    security_deposit: '',
    payment_day_of_month: 1,
    utilities_included: [],
    auto_renewable: true,
    pets_allowed: false,
    smoking_allowed: false,
  });

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    const response = await fetch('/api/v1/long-term-rentals', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData),
    });

    const data = await response.json();
    
    if (response.ok) {
      // Show success message
      // Optionally activate immediately
      await activateRental(data.rental.id);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Create Long-term Rental</h2>
      
      {/* Tenant Selection */}
      <TenantSearch 
        onSelect={(tenant) => setFormData({...formData, tenant_id: tenant.id})}
      />

      {/* Rental Details */}
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label>Start Date</label>
          <input 
            type="date" 
            value={formData.start_date}
            onChange={(e) => setFormData({...formData, start_date: e.target.value})}
            required
          />
        </div>

        <div>
          <label>Duration (months)</label>
          <input 
            type="number" 
            min="1" 
            max="120"
            value={formData.duration_months}
            onChange={(e) => setFormData({...formData, duration_months: parseInt(e.target.value)})}
            required
          />
        </div>
      </div>

      {/* Financial Details */}
      <div className="grid grid-cols-2 gap-4">
        <div>
          <label>Monthly Rent</label>
          <input 
            type="number" 
            step="0.01"
            value={formData.monthly_rent}
            onChange={(e) => setFormData({...formData, monthly_rent: e.target.value})}
            required
          />
        </div>

        <div>
          <label>Security Deposit</label>
          <input 
            type="number" 
            step="0.01"
            value={formData.security_deposit}
            onChange={(e) => setFormData({...formData, security_deposit: e.target.value})}
            required
          />
        </div>
      </div>

      {/* Utilities */}
      <div>
        <label>Utilities Included</label>
        <CheckboxGroup 
          options={['water', 'electricity', 'gas', 'internet', 'trash']}
          value={formData.utilities_included}
          onChange={(utilities) => setFormData({...formData, utilities_included: utilities})}
        />
      </div>

      {/* House Rules */}
      <div className="grid grid-cols-2 gap-4">
        <label>
          <input 
            type="checkbox" 
            checked={formData.pets_allowed}
            onChange={(e) => setFormData({...formData, pets_allowed: e.target.checked})}
          />
          Pets Allowed
        </label>

        <label>
          <input 
            type="checkbox" 
            checked={formData.smoking_allowed}
            onChange={(e) => setFormData({...formData, smoking_allowed: e.target.checked})}
          />
          Smoking Allowed
        </label>
      </div>

      {/* Renewal Options */}
      <label>
        <input 
          type="checkbox" 
          checked={formData.auto_renewable}
          onChange={(e) => setFormData({...formData, auto_renewable: e.target.checked})}
        />
        Auto-renewable
      </label>

      <button type="submit">Create Rental</button>
    </form>
  );
}
```

---

#### 3. **Rent Payments Component**

```tsx
// components/owner/RentPayments.tsx

export function RentPaymentsList({ rentalId }: { rentalId: number }) {
  const [payments, setPayments] = useState([]);

  useEffect(() => {
    fetchPayments();
  }, [rentalId]);

  const fetchPayments = async () => {
    const response = await fetch(
      `/api/v1/rent-payments?long_term_rental_id=${rentalId}`,
      {
        headers: { 'Authorization': `Bearer ${token}` }
      }
    );
    const data = await response.json();
    setPayments(data.data);
  };

  const markAsPaid = async (paymentId: number) => {
    const amount = prompt('Enter amount paid:');
    const method = prompt('Payment method (bank_transfer/cash/check):');
    
    const response = await fetch(
      `/api/v1/rent-payments/${paymentId}/mark-as-paid`,
      {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          amount: parseFloat(amount),
          payment_method: method,
          generate_invoice: true,
        }),
      }
    );

    if (response.ok) {
      alert('Payment marked as paid. Invoice sent to tenant.');
      fetchPayments(); // Refresh list
    }
  };

  return (
    <div>
      <h3>Payment Schedule</h3>

      {/* Summary */}
      <div className="mb-4">
        <p>
          Paid: {payments.filter(p => p.status === 'paid').length} / {payments.length}
        </p>
        <p>
          Overdue: {payments.filter(p => p.status === 'overdue').length}
        </p>
      </div>

      {/* Payments Table */}
      <table>
        <thead>
          <tr>
            <th>Type</th>
            <th>Month</th>
            <th>Due Date</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {payments.map(payment => (
            <tr key={payment.id} className={payment.status === 'overdue' ? 'bg-red-50' : ''}>
              <td>
                {payment.payment_type === 'deposit' ? '🏦 Deposit' : '🏠 Rent'}
              </td>
              <td>{payment.month_number || '-'}</td>
              <td>{formatDate(payment.due_date)}</td>
              <td>${payment.amount_due}</td>
              <td>
                <StatusBadge status={payment.status} />
                {payment.status === 'overdue' && (
                  <span className="text-red-600">
                    ({payment.days_overdue} days)
                  </span>
                )}
              </td>
              <td>
                {payment.status !== 'paid' && (
                  <>
                    <button onClick={() => markAsPaid(payment.id)}>
                      Mark as Paid
                    </button>
                    <button onClick={() => sendReminder(payment.id)}>
                      Send Reminder
                    </button>
                  </>
                )}
                {payment.invoice_id && (
                  <a href={`/api/v1/invoices/${payment.invoice_id}/download`}>
                    📄 Invoice
                  </a>
                )}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
```

---

#### 4. **Tenant - Maintenance Request Form**

```tsx
// components/tenant/CreateMaintenanceRequest.tsx

export function CreateMaintenanceRequestForm({ rentalId, propertyId }: Props) {
  const [formData, setFormData] = useState({
    long_term_rental_id: rentalId,
    property_id: propertyId,
    title: '',
    description: '',
    category: 'other',
    priority: 'medium',
    preferred_date: '',
    requires_access: true,
    access_instructions: '',
    photos: [],
  });

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    const formDataToSend = new FormData();
    Object.keys(formData).forEach(key => {
      if (key === 'photos') {
        formData.photos.forEach(photo => {
          formDataToSend.append('photos[]', photo);
        });
      } else {
        formDataToSend.append(key, formData[key]);
      }
    });

    const response = await fetch('/api/v1/maintenance-requests', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
      },
      body: formDataToSend,
    });

    if (response.ok) {
      alert('Maintenance request submitted successfully!');
      // Reset form or redirect
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <h2>Report Maintenance Issue</h2>

      <div>
        <label>What's the issue?</label>
        <input 
          type="text" 
          value={formData.title}
          onChange={(e) => setFormData({...formData, title: e.target.value})}
          placeholder="e.g., Leaking faucet"
          required
        />
      </div>

      <div>
        <label>Description</label>
        <textarea 
          value={formData.description}
          onChange={(e) => setFormData({...formData, description: e.target.value})}
          rows={4}
          required
        />
      </div>

      <div className="grid grid-cols-2 gap-4">
        <div>
          <label>Category</label>
          <select 
            value={formData.category}
            onChange={(e) => setFormData({...formData, category: e.target.value})}
          >
            <option value="plumbing">Plumbing</option>
            <option value="electrical">Electrical</option>
            <option value="hvac">HVAC</option>
            <option value="appliance">Appliance</option>
            <option value="structural">Structural</option>
            <option value="pest_control">Pest Control</option>
            <option value="cleaning">Cleaning</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div>
          <label>Priority</label>
          <select 
            value={formData.priority}
            onChange={(e) => setFormData({...formData, priority: e.target.value})}
          >
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
            <option value="urgent">🚨 Urgent</option>
          </select>
        </div>
      </div>

      <div>
        <label>Upload Photos (optional)</label>
        <input 
          type="file" 
          accept="image/*"
          multiple
          onChange={(e) => setFormData({...formData, photos: Array.from(e.target.files)})}
        />
      </div>

      <div>
        <label>
          <input 
            type="checkbox" 
            checked={formData.requires_access}
            onChange={(e) => setFormData({...formData, requires_access: e.target.checked})}
          />
          Maintenance requires access to property
        </label>
      </div>

      {formData.requires_access && (
        <div>
          <label>Access Instructions</label>
          <textarea 
            value={formData.access_instructions}
            onChange={(e) => setFormData({...formData, access_instructions: e.target.value})}
            placeholder="e.g., Key with neighbor in Apt 102"
          />
        </div>
      )}

      <button type="submit">Submit Request</button>
    </form>
  );
}
```

---

## 🔔 Important Workflows

### Workflow 1: Creating a New Long-term Rental

```
1. Owner creates rental (draft status)
   POST /api/v1/long-term-rentals

2. Owner activates rental
   POST /api/v1/long-term-rentals/{id}/activate
   
   → Backend automatically:
     - Creates deposit payment (due 7 days before move-in)
     - Creates monthly rent payments (for entire duration)
     - All payments start with status: "scheduled"

3. As tenant pays each month:
   POST /api/v1/rent-payments/{id}/mark-as-paid
   
   → Backend automatically:
     - Generates invoice
     - Sends invoice email to tenant
     - Updates payment status to "paid"
```

---

### Workflow 2: Deposit + First Month Rent

```
Payment Schedule Example:
- Move-in date: 2025-12-01
- Monthly rent: $1,500
- Deposit: $3,000

Generated Payments:
1. Deposit: Due 2025-11-24 (7 days before), Amount: $3,000
2. Month 1: Due 2025-12-01, Amount: $1,500
3. Month 2: Due 2026-01-01, Amount: $1,500
4. Month 3: Due 2026-02-01, Amount: $1,500
...
```

Tenant trebuie să plătească:
- **Înainte de move-in:** Depozit ($3,000)
- **La move-in (ziua 1):** Prima lună de chirie ($1,500)
- **Total due before moving in:** $4,500

---

### Workflow 3: Handling Overdue Payments

```
1. Cron job rulează zilnic:
   POST /api/v1/rent-payments/update-overdue

2. Backend checks toate payments cu:
   - due_date < today
   - status != 'paid'

3. Pentru fiecare overdue payment:
   - Calculate days_overdue
   - Calculate late_fee (e.g., $5/day, max $100)
   - Update status to 'overdue'

4. Owner poate trimite reminder:
   POST /api/v1/rent-payments/{id}/send-reminder
```

---

## 🎯 Testing Checklist

### Backend Tests:

```bash
# Create a rental
POST /api/v1/long-term-rentals
{
  "property_id": 1,
  "tenant_id": 2,
  "start_date": "2025-12-01",
  "duration_months": 12,
  "monthly_rent": 1500,
  "security_deposit": 3000,
  "payment_frequency": "monthly"
}

# Activate rental (generates payment schedule)
POST /api/v1/long-term-rentals/1/activate

# Check generated payments
GET /api/v1/rent-payments?long_term_rental_id=1

# Mark first payment as paid
POST /api/v1/rent-payments/1/mark-as-paid
{
  "amount": 3000,
  "payment_method": "bank_transfer",
  "transaction_id": "TXN123",
  "generate_invoice": true
}

# Create maintenance request
POST /api/v1/maintenance-requests
{
  "long_term_rental_id": 1,
  "property_id": 1,
  "tenant_id": 2,
  "title": "Leaking faucet",
  "description": "Kitchen sink leaks",
  "category": "plumbing",
  "priority": "high"
}

# Get statistics
GET /api/v1/long-term-rentals/statistics?owner_id=3
```

---

## 📝 Notes for Your Clients

### Important Features:

1. **Automatic Invoice Generation** ✅
   - When a payment is marked as paid, invoice is automatically generated
   - Invoice includes bank account details
   - Email sent automatically to tenant

2. **Deposit Management** ✅
   - Deposit is tracked separately
   - Can be returned partially or fully
   - Status: pending → paid → held → returned

3. **Payment Schedule** ✅
   - Auto-generated when rental is activated
   - No manual work needed
   - All payments tracked with due dates

4. **Maintenance Tracking** ✅
   - Tenants can submit requests with photos
   - Owner can assign to specific person
   - Track costs and completion

5. **Renewal Options** ✅
   - Auto-renewable flag
   - Tenant can request renewal
   - Owner approves and creates new rental period

---

## 🚀 Next Steps

1. ✅ **Backend Complete** - All APIs ready
2. ⏳ **Filament Admin** - Basic resource created
3. ⏳ **Frontend Components** - Need to implement in Next.js
4. ⏳ **Email Templates** - For invoices and reminders
5. ⏳ **Cron Jobs** - For overdue payments and reminders

---

## 💡 Recommendations

### For Production:

1. **Cron Job** - Add to Laravel Scheduler:
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Update overdue payments daily at 1 AM
    $schedule->call(function () {
        Http::post(config('app.url') . '/api/v1/rent-payments/update-overdue');
    })->dailyAt('01:00');

    // Send payment reminders 3 days before due date
    $schedule->call(function () {
        $upcomingPayments = RentPayment::upcoming(3)->get();
        foreach ($upcomingPayments as $payment) {
            // Send reminder email
        }
    })->daily();
}
```

2. **Notifications** - Integrate with existing notification system

3. **Email Templates** - Create specific templates for:
   - Rent invoice
   - Payment reminder
   - Maintenance request confirmation
   - Maintenance completed

4. **PDF Lease Agreement** - Need to create template view

---

Gata! 🎉 Taskul **3.3 Long-term Rentals** este complet implementat pe backend!
