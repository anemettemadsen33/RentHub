# 🚀 Guest Screening - Quick Start Guide

## ⚡ 5-Minute Setup

### 1️⃣ Backend (Already Done ✅)
```bash
cd backend
php artisan migrate  # ✅ Completed
```

### 2️⃣ Test API Endpoints

#### Get Verification Status:
```bash
curl -X GET http://localhost/api/v1/guest-verification \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Submit Identity:
```bash
curl -X POST http://localhost/api/v1/guest-verification/identity \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "document_type=passport" \
  -F "document_number=AB123456" \
  -F "document_front=@passport_front.jpg" \
  -F "selfie_photo=@selfie.jpg" \
  -F "document_expiry_date=2028-12-31"
```

#### Get Statistics:
```bash
curl -X GET http://localhost/api/v1/guest-verification/statistics \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 3️⃣ Access Admin Panel

1. Navigate to: `http://localhost/admin`
2. Look for **"Guest Verifications"** in sidebar
3. You'll see a badge with pending verification count
4. Click to manage verifications

### 4️⃣ Access Frontend Dashboard

1. Navigate to: `http://localhost:3000/verification`
2. You'll see:
   - Trust Score Card
   - Identity Verification Card
   - References Card
   - Credit Check Card

---

## 🎯 User Flow

### For Guests:

1. **Visit Verification Page**
   ```
   /verification
   ```

2. **Upload Identity Documents**
   - Choose document type (Passport, Driver's License, etc.)
   - Enter document number
   - Upload front/back photos
   - Take selfie photo
   - Set expiry date
   - Submit

3. **Add References** (Optional but recommended)
   - Add previous landlord
   - Add employer
   - Add personal references
   - References receive email to verify

4. **Request Credit Check** (Optional)
   - Click "Request Credit Check"
   - Wait for processing

5. **Check Trust Score**
   - View real-time trust score
   - See verification progress
   - Check if you can book

### For Admins:

1. **Review Pending Verifications**
   ```
   /admin/guest-verifications
   ```

2. **Approve or Reject**
   - View uploaded documents
   - Check details
   - Approve ✅ or Reject ❌ with reason

3. **Monitor Trust Scores**
   - Filter by high trust score
   - Filter by verification level
   - View statistics

---

## 📊 Quick Reference

### Trust Score Levels:
- **0.0 - 2.5** 🔴 Low (Booking difficult)
- **2.5 - 3.5** 🟡 Medium (Conditional booking)
- **3.5 - 4.5** 🔵 Good (Can book most properties)
- **4.5 - 5.0** 🟢 Excellent (Premium guest)

### Verification Statuses:

**Identity:**
- 🟡 Pending - Under review
- ✅ Verified - Approved
- ❌ Rejected - Not approved
- ⏰ Expired - Document expired

**Background:**
- 🟡 Pending - Not checked yet
- ✅ Clear - No issues
- 🚩 Flagged - Issues found

**Credit:**
- ⚪ Not Requested - Not started
- 🟡 Pending - Processing
- ✅ Approved - Good credit
- ❌ Rejected - Credit issues

---

## 🔑 Key Features

### ✅ What's Working:
1. **Identity Verification** - Upload & verify documents
2. **Trust Score** - Auto-calculated based on multiple factors
3. **References** - Email-based verification system
4. **Credit Check** - Optional credit verification
5. **Background Check** - Admin review process
6. **Booking Requirements** - Automatic eligibility check
7. **Admin Dashboard** - Full Filament resource
8. **Frontend Dashboard** - React components
9. **API Endpoints** - RESTful API
10. **Audit Logs** - Complete verification history

---

## 🎨 Component Usage

### In Any Next.js Page:
```tsx
import { VerificationDashboard } from '@/components/guest-verification';

export default function MyPage() {
  return <VerificationDashboard />;
}
```

### Individual Components:
```tsx
import { 
  TrustScoreCard,
  IdentityVerificationCard,
  ReferenceCard,
  CreditCheckCard
} from '@/components/guest-verification';

// Use individually
<TrustScoreCard statistics={stats} canBook={true} isFullyVerified={true} />
```

---

## 🐛 Troubleshooting

### Issue: Can't upload documents
**Solution:** Check storage permissions
```bash
cd backend
php artisan storage:link
chmod -R 775 storage
```

### Issue: Trust score not updating
**Solution:** Manually recalculate
```php
$verification = GuestVerification::find($id);
$verification->updateTrustScore();
```

### Issue: References not receiving emails
**Solution:** Configure SMTP in `.env`
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

---

## 📱 Mobile Responsive

All components are mobile-responsive:
- ✅ Works on phone (320px+)
- ✅ Works on tablet (768px+)
- ✅ Works on desktop (1024px+)

---

## 🔐 Security Features

1. **Document Encryption** - Files stored securely
2. **Token-based Reference Verification** - Unique tokens for each reference
3. **Audit Logging** - All actions logged with IP
4. **Admin-only Approval** - Only admins can approve
5. **Rate Limiting** - API rate limits applied

---

## 📈 Metrics to Track

Monitor these in your admin dashboard:
- ✅ Verification completion rate
- ⏱️ Average approval time
- 📊 Trust score distribution
- 🎯 Booking success rate by trust score
- 📉 Rejection reasons

---

## 🎯 Next Steps

1. **Configure Email** - Set up SMTP for reference verification
2. **Add Integrations** - Connect third-party verification services
3. **Customize Weights** - Adjust trust score algorithm
4. **Add Notifications** - Email guests on status changes
5. **Analytics Dashboard** - Build verification analytics

---

## 📚 Full Documentation

For complete documentation, see:
```
GUEST_SCREENING_README.md
```

---

## ✨ That's It!

You now have a fully functional Guest Screening system with:
- ✅ Identity verification
- ✅ Credit checks
- ✅ Reference verification
- ✅ Trust score calculation
- ✅ Admin management
- ✅ Frontend dashboard

**Happy verifying! 🎉**
