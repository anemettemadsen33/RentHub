# 🚀 Quick Start: Long-term Rentals

## ⚡ Setup (Already Done)

✅ Migrations run  
✅ Models created  
✅ API routes configured  
✅ Controllers ready  

---

## 🧪 Test It Now

### 1. Create a Long-term Rental

```bash
curl -X POST http://localhost/api/v1/long-term-rentals \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "property_id": 1,
    "tenant_id": 2,
    "start_date": "2025-12-01",
    "duration_months": 12,
    "monthly_rent": 1500,
    "security_deposit": 3000,
    "payment_frequency": "monthly",
    "payment_day_of_month": 1,
    "utilities_included": ["water", "trash"],
    "auto_renewable": true,
    "pets_allowed": false,
    "smoking_allowed": false
  }'
```

### 2. Activate Rental (Generates Payment Schedule)

```bash
curl -X POST http://localhost/api/v1/long-term-rentals/1/activate \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**This will automatically create:**
- 1 deposit payment (due 7 days before move-in)
- 12 monthly rent payments

### 3. View Payment Schedule

```bash
curl -X GET "http://localhost/api/v1/rent-payments?long_term_rental_id=1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 4. Mark Payment as Paid (Auto-generates Invoice)

```bash
curl -X POST http://localhost/api/v1/rent-payments/1/mark-as-paid \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 3000,
    "payment_method": "bank_transfer",
    "transaction_id": "TXN123456",
    "generate_invoice": true
  }'
```

**This will:**
- Mark payment as paid ✅
- Generate invoice with bank details ✅
- Send invoice email to tenant ✅

### 5. Create Maintenance Request

```bash
curl -X POST http://localhost/api/v1/maintenance-requests \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "long_term_rental_id": 1,
    "property_id": 1,
    "tenant_id": 2,
    "title": "Leaking faucet in kitchen",
    "description": "Kitchen sink faucet drips constantly",
    "category": "plumbing",
    "priority": "high",
    "requires_access": true,
    "access_instructions": "Key with neighbor in Apt 102"
  }'
```

---

## 📊 Check Statistics

```bash
curl -X GET "http://localhost/api/v1/long-term-rentals/statistics?owner_id=YOUR_OWNER_ID" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🎨 Filament Admin

Access: `http://localhost/admin/long-term-rentals`

You can:
- View all rentals
- Filter by status
- See payment schedules
- Track maintenance requests

---

## 📚 Full Documentation

- **API Guide:** `LONG_TERM_RENTALS_API_GUIDE.md` (28KB - complete)
- **Task Summary:** `TASK_3.3_LONG_TERM_RENTALS_COMPLETE.md`
- **Frontend Examples:** Included in API guide

---

## 🔑 Key Concepts

### Payment Flow:
```
1. Create Rental (status: draft)
   ↓
2. Activate Rental
   ↓
3. Payment Schedule Auto-generated:
   - Deposit (due 7 days before move-in)
   - Month 1-12 rent (due on payment_day_of_month)
   ↓
4. Owner marks each payment as paid
   ↓
5. Invoice auto-generated & emailed
```

### Tenant Pays:
```
Before Move-in:
├── Deposit: $3,000
└── First Month: $1,500
    TOTAL: $4,500

Each Month After:
└── Monthly Rent: $1,500
```

### Late Fees:
```
Overdue days × $5/day (max $100)

Example:
- 5 days late = $25 late fee
- 20 days late = $100 late fee (capped)
```

---

## 🎯 Next Steps

1. **Test Backend** - Use Postman/curl to test all endpoints
2. **Build Frontend** - Implement Next.js components (see API guide)
3. **Setup Cron** - For overdue payments & reminders
4. **Email Templates** - Customize invoice emails
5. **PDF Template** - Create lease agreement template

---

## 💡 Pro Tips

### For Owners:
- Always activate rental to generate payment schedule
- Mark payments as paid promptly to auto-send invoices
- Track maintenance requests in Filament admin

### For Tenants:
- Submit maintenance with photos for faster response
- Check payment schedule to know upcoming dues
- Request renewal 30 days before lease ends

### For Admins:
- Run `POST /rent-payments/update-overdue` daily via cron
- Monitor expiring rentals with `expiring_soon=true` filter
- Use statistics endpoint for dashboard metrics

---

## 🐛 Troubleshooting

### Invoice not generated?
Check:
1. BankAccount exists for owner
2. `generate_invoice: true` in request
3. Email configuration in `.env`

### Payment schedule not created?
Check:
1. Rental must be in `draft` or `pending_approval` status
2. Use `/activate` endpoint, not just update status

### Late fees not calculated?
Run: `POST /rent-payments/update-overdue`

---

## ✅ Checklist for Production

- [ ] Setup cron job for overdue payments
- [ ] Configure SMTP for email sending
- [ ] Test invoice generation with real bank accounts
- [ ] Create custom email templates
- [ ] Setup monitoring for failed payments
- [ ] Test renewal workflow end-to-end
- [ ] Add analytics tracking
- [ ] Setup backup for rental documents

---

**Ready to test!** 🎉

Start with Step 1 above and work through the flow.
