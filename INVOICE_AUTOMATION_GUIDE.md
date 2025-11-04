# Invoice Automation System - Complete Guide 📄

## 🎯 Overview

Sistem complet de generare automată a facturilor cu:
- ✅ **Auto-generare** la confirmare booking
- ✅ **Selecție automată** cont bancar per agent/owner
- ✅ **Generare PDF** profesional cu branding
- ✅ **Trimitere automată** email cu factura
- ✅ **Multiple conturi** bancare per agent

---

## 🏗️ Arhitectură

### Services
```
BankAccountService          → Gestionare conturi bancare
InvoiceGenerationService    → Generare facturi automate
InvoicePdfService          → Generare PDF
InvoiceEmailService        → Trimitere email
```

### Observer
```
BookingObserver            → Detectează confirmare booking
                            → Generează automat invoice
                            → Trimite email cu PDF
```

### Flow Logic
```
1. Booking status changes to "confirmed"
2. BookingObserver.updated() is triggered
3. InvoiceGenerationService.createFromBooking()
   ├─ Selectează cont bancar (owner → fallback company)
   ├─ Creează invoice cu toate datele
   ├─ Generează PDF cu InvoicePdfService
   └─ Trimite email cu InvoiceEmailService
4. Customer primește email cu PDF attachment
```

---

## 🔧 Bank Account Selection Logic

### Priority Order
1. **Owner's Default Account** - Contul marcat ca default al owner-ului
2. **Owner's Any Active Account** - Orice cont activ al owner-ului
3. **Company Default Account** - Contul default al companiei
4. **Company Any Account** - Orice cont activ al companiei

### Code Example
```php
// BankAccountService@getForProperty()
$account = $bankAccountService->getForProperty($propertyId);

// Returns the best matching account based on priority
```

---

## 📡 API Endpoints

### 1. Generate Invoice Manually
**Owner/Admin poate genera manual invoice pentru un booking**

```http
POST /api/v1/bookings/{booking}/generate-invoice
Authorization: Bearer {token}
Content-Type: application/json

{
  "send_email": true  // optional, default: true
}
```

**Response Success:**
```json
{
  "success": true,
  "message": "Invoice generated successfully and sent to customer",
  "data": {
    "id": 1,
    "invoice_number": "2025110001",
    "booking_id": 15,
    "user_id": 5,
    "property_id": 3,
    "bank_account_id": 2,
    "total_amount": "1500.00",
    "currency": "EUR",
    "status": "sent",
    "pdf_path": "invoices/2025110001.pdf",
    "bankAccount": {
      "id": 2,
      "account_name": "John's Rentals",
      "iban": "RO49AAAA1B31007593840000",
      "bic_swift": "AAAROBU",
      "bank_name": "ING Bank Romania"
    }
  }
}
```

**Response Error:**
```json
{
  "success": false,
  "message": "Invoice already exists for this booking.",
  "invoice": { ... }
}
```

---

### 2. Get Booking Invoices
**Obține toate facturile pentru un booking**

```http
GET /api/v1/bookings/{booking}/invoices
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "invoice_number": "2025110001",
      "booking_id": 15,
      "total_amount": "1500.00",
      "currency": "EUR",
      "status": "sent",
      "invoice_date": "2025-11-02",
      "due_date": "2025-11-09",
      "pdf_url": "/storage/invoices/2025110001.pdf",
      "bankAccount": {
        "account_name": "John's Rentals",
        "iban": "RO49 AAAA 1B31 0075 9384 0000",
        "bank_name": "ING Bank Romania"
      }
    }
  ]
}
```

---

### 3. Download Invoice PDF
**Descarcă PDF-ul facturii**

```http
GET /api/v1/invoices/{invoice}/download
Authorization: Bearer {token}
```

**Response:** PDF file download

---

### 4. Resend Invoice Email
**Retrimite email cu factura**

```http
POST /api/v1/invoices/{invoice}/resend
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Invoice resent successfully",
  "invoice": { ... }
}
```

---

## 🏦 Bank Account Management

### Filament Admin

#### Create Bank Account
1. Navighează la **Bank Accounts** în Filament
2. Click **New Bank Account**
3. Completează:
   - **Agent/Owner**: Selectează owner sau lasă gol pentru company account
   - **Account Name**: Ex: "John's Rentals" 
   - **Account Holder Name**: Nume legal
   - **IBAN**: Ex: RO49AAAA1B31007593840000
   - **BIC/SWIFT**: Ex: AAAROBU
   - **Bank Name**: Ex: ING Bank Romania
   - **Currency**: EUR, USD, RON, GBP
   - **Active**: Toggle ON
   - **Set as Default**: Toggle ON pentru default

#### Multiple Accounts per Agent
- Un agent poate avea **unlimited accounts**
- Doar **unul poate fi default** (auto-selected)
- Poți **activa/dezactiva** conturi
- Poți **schimba default-ul** oricând

---

## 🎨 Invoice PDF Design

### Features
- ✅ Professional header cu logo RentHub
- ✅ Invoice number și date
- ✅ Status badge (Paid, Sent, Draft, Overdue)
- ✅ Customer details (Bill To)
- ✅ Property details cu check-in/out dates
- ✅ Itemized breakdown:
  - Rental Fee
  - Cleaning Fee
  - Security Deposit
  - Taxes & Fees
- ✅ **Bank Details** section evidențiat:
  - Account Name
  - Account Holder
  - IBAN (formatat cu spații)
  - BIC/SWIFT
  - Bank Name
  - Bank Address
  - Payment Reference instruction
- ✅ Notes section
- ✅ Professional footer

### Preview
```
┌─────────────────────────────────────┐
│         RentHub                      │
│    Property Rental Platform          │
├─────────────────────────────────────┤
│ INVOICE #2025110001                  │
│ Date: 02 Nov 2025                    │
│ Status: [SENT]                       │
├─────────────────────────────────────┤
│ Bill To:              Property:      │
│ John Doe              Beach Villa    │
│ john@email.com        Miami, FL      │
├─────────────────────────────────────┤
│ Description              Amount      │
│ Rental Fee              1200.00 EUR  │
│ Cleaning Fee             150.00 EUR  │
│ Taxes                    150.00 EUR  │
│                                      │
│ TOTAL AMOUNT           1500.00 EUR   │
├─────────────────────────────────────┤
│ 🏦 PAYMENT DETAILS                   │
│                                      │
│ Account Name: John's Rentals         │
│ IBAN: RO49 AAAA 1B31 0075 9384 0000 │
│ BIC/SWIFT: AAAROBU                   │
│ Bank: ING Bank Romania               │
│                                      │
│ ⚠️ Reference: Please include         │
│    invoice #2025110001               │
└─────────────────────────────────────┘
```

---

## 📧 Email Template

### Features
- ✅ Responsive HTML design
- ✅ Professional branding
- ✅ Invoice summary table
- ✅ Bank payment instructions evidențiate
- ✅ Booking details
- ✅ PDF attachment automat
- ✅ Clear call-to-action

### Subject
```
Your Invoice #2025110001 from RentHub
```

### Content Highlights
- Personalized greeting
- Invoice summary
- Payment instructions with bank details
- Booking details (check-in, check-out, guests)
- PDF attachment notice
- Contact information

---

## 🧪 Testing Guide

### 1. Test Auto-generation

```php
// Create a booking
$booking = Booking::create([...]);

// Confirm it (triggers auto-invoice)
$booking->update(['status' => 'confirmed']);

// Check invoice was created
$invoice = $booking->invoices()->first();
expect($invoice)->not->toBeNull();
expect($invoice->pdf_path)->not->toBeNull();
expect($invoice->sent_at)->not->toBeNull();
```

### 2. Test Bank Account Selection

```php
// Create owner with default account
$owner = User::factory()->create(['role' => 'owner']);
$bankAccount = BankAccount::factory()->create([
    'user_id' => $owner->id,
    'is_default' => true,
    'is_active' => true,
]);

$property = Property::factory()->create(['user_id' => $owner->id]);
$booking = Booking::factory()->create(['property_id' => $property->id]);

$booking->update(['status' => 'confirmed']);

$invoice = $booking->invoices()->first();
expect($invoice->bank_account_id)->toBe($bankAccount->id);
```

### 3. Test Manual Generation

```bash
POST /api/v1/bookings/1/generate-invoice
Authorization: Bearer owner_token

# Should return invoice with bank details
```

### 4. Test Email Sending

```php
Mail::fake();

$booking->update(['status' => 'confirmed']);

Mail::assertSent(InvoiceMail::class, function ($mail) use ($booking) {
    return $mail->invoice->booking_id === $booking->id;
});
```

---

## 🚀 Deployment Checklist

### Before Deploy
- [ ] Run migrations (already done)
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Clear config: `php artisan config:clear`
- [ ] Optimize: `php artisan optimize`

### Create Bank Accounts
1. [ ] Login to Filament Admin
2. [ ] Create company default account
3. [ ] Create accounts for each property owner
4. [ ] Set default accounts
5. [ ] Verify all accounts are active

### Test Invoice Generation
1. [ ] Create test booking
2. [ ] Confirm booking
3. [ ] Verify invoice created
4. [ ] Check PDF generated
5. [ ] Verify email sent
6. [ ] Check bank details in PDF

### Queue Configuration
- [ ] Configure queue driver (Redis recommended)
- [ ] Start queue worker: `php artisan queue:work`
- [ ] Monitor queue: `php artisan queue:monitor`

---

## 🔐 Security Notes

- ✅ Only **owner** or **admin** can generate invoices
- ✅ Users can only view their own invoices
- ✅ Bank account validation before use
- ✅ PDF stored securely in storage
- ✅ Email sent via queue (async)

---

## 💡 Tips & Best Practices

### Multiple Accounts Strategy
```
Company Account (Default)    → Fallback pentru toate booking-urile
├─ Agent John                → 2 conturi (EUR default, USD)
├─ Agent Mary                → 1 cont (EUR default)
└─ Agent Peter               → 3 conturi (EUR, USD, GBP)
```

### When Invoice is Generated
- ✅ **Auto**: Booking status → confirmed
- ✅ **Manual**: POST /bookings/{id}/generate-invoice (owner/admin)
- ❌ **Not Generated**: Cancelled, pending, expired bookings

### Bank Account Best Practices
1. Întotdeauna setează un **company default** account
2. Setează **default account** pentru fiecare owner activ
3. Verifică că toate conturile au **IBAN, BIC, Bank Name**
4. Testează generarea de facturi înainte de producție
5. Monitorizează logs pentru erori de generare

---

## 📊 Database Schema

### invoices
```sql
- invoice_number (unique)
- booking_id (foreign key)
- user_id (customer)
- property_id
- bank_account_id (foreign key) ← Link to bank account
- total_amount
- currency
- status (draft, sent, paid, overdue)
- pdf_path
- sent_at
- customer_* (cached customer info)
- property_* (cached property info)
```

### bank_accounts
```sql
- user_id (nullable) ← NULL = company account
- account_name
- account_holder_name
- iban
- bic_swift
- bank_name
- bank_address
- currency
- is_default
- is_active
- account_type (business/personal)
```

---

## 🆘 Troubleshooting

### Invoice not auto-generated
1. Check logs: `storage/logs/laravel.log`
2. Verify observer is registered: `AppServiceProvider@boot`
3. Check booking status is "confirmed"
4. Verify bank account exists and is active

### Email not sent
1. Check queue is running: `php artisan queue:work`
2. Check mail configuration: `.env` MAIL_* settings
3. Check logs for mail errors
4. Verify customer email is valid

### PDF not generated
1. Check dompdf is installed: `composer require barryvdh/laravel-dompdf`
2. Check storage permissions: `storage/app/invoices/`
3. Check PDF template exists: `resources/views/invoices/pdf.blade.php`

### Wrong bank account selected
1. Check owner has active default account
2. Check company default account exists
3. Review priority logic in `BankAccountService@getForProperty`

---

## 📞 Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Review code: `app/Services/InvoiceGenerationService.php`
- Email: dev@renthub.com

---

**Status**: ✅ IMPLEMENTED & READY FOR USE
**Version**: 1.0
**Last Updated**: 02 Nov 2025
