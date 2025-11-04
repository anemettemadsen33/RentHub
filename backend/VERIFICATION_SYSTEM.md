# Property & Owner Verification System

## Overview
Sistem complet de verificare pentru utilizatori (owners) și proprietăți, conform cu cerințele platformelor de rent/booking.

## Features Implementate

### ✅ 1. User Verification (Owner Side)
- **ID Verification**
  - Upload document ID (passport, driving license, national ID)
  - Front & back images
  - Selfie verification
  - Admin review & approval

- **Phone Verification**
  - SMS code (6 digits)
  - 10 minute expiry
  - Automated verification

- **Email Verification**
  - Built-in Laravel email verification
  - Link verification

- **Address Verification**
  - Address proof upload (utility bill, bank statement, etc.)
  - Admin review & approval

- **Background Check** (Optional)
  - Integration placeholder pentru servicii terțe (Checkr, Certn)
  - Request manual prin admin

- **Verification Score** (0-100)
  - ID Verification: 30 points
  - Phone Verification: 20 points
  - Email Verification: 20 points
  - Address Verification: 20 points
  - Background Check: 10 points

### ✅ 2. Property Verification
- **Ownership Verification**
  - Document upload (deed, lease agreement, rental contract)
  - Multiple documents support
  - Admin review

- **Property Inspection** (Optional)
  - Schedule inspection
  - Inspector assignment
  - Inspection report & score

- **Photos Verification**
  - Admin review photos
  - Approve/reject with reasons

- **Details Verification**
  - Verify property details
  - Request corrections

- **Legal Compliance**
  - Business License
  - Safety Certificate
  - Insurance (with expiry tracking)

- **Verified Badge**
  - Awarded when verification_score >= 80
  - Annual re-verification

### ✅ 3. Admin Panel (Filament)
- UserVerification Resource
- PropertyVerification Resource
- Document viewer & approval
- Manual review workflow
- Reject with reasons
- Admin notes

### ✅ 4. API Endpoints

#### User Verification

```bash
# Get verification status
GET /api/v1/user/verification
Authorization: Bearer {token}

# Submit ID verification
POST /api/v1/user/verification/id
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "id_document_type": "passport",
  "id_document_number": "AB123456",
  "id_front_image": file,
  "id_back_image": file,
  "selfie_image": file
}

# Send phone verification code
POST /api/v1/user/verification/phone/send
Authorization: Bearer {token}

{
  "phone_number": "+40712345678"
}

# Verify phone with code
POST /api/v1/user/verification/phone/verify
Authorization: Bearer {token}

{
  "code": "123456"
}

# Submit address verification
POST /api/v1/user/verification/address
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "address": "Str. Exemplu 123, București",
  "address_proof_document": "utility_bill",
  "address_proof_image": file
}

# Request background check
POST /api/v1/user/verification/background-check
Authorization: Bearer {token}

# Upload additional document
POST /api/v1/user/verification/documents
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "document_type": "bank_statement",
  "file": file,
  "metadata": {
    "notes": "Optional notes"
  }
}

# Delete document
DELETE /api/v1/user/verification/documents/{documentId}
Authorization: Bearer {token}
```

#### Property Verification

```bash
# Get all property verifications
GET /api/v1/property/verifications
Authorization: Bearer {token}

# Get specific property verification
GET /api/v1/property/{propertyId}/verification
Authorization: Bearer {token}

# Submit ownership documents
POST /api/v1/property/{propertyId}/verification/ownership
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "ownership_document_type": "deed",
  "documents[]": file,
  "documents[]": file
}

# Submit legal documents
POST /api/v1/property/{propertyId}/verification/legal-documents
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "has_business_license": true,
  "business_license_document": file,
  "has_safety_certificate": true,
  "safety_certificate_document": file,
  "has_insurance": true,
  "insurance_document": file,
  "insurance_expiry_date": "2025-12-31"
}

# Request property inspection
POST /api/v1/property/{propertyId}/verification/inspection
Authorization: Bearer {token}
```

## Database Schema

### user_verifications
- Toate câmpurile pentru ID, phone, email, address, background check
- Overall status & verification score
- Admin notes & reviewer

### property_verifications
- Ownership, inspection, photos, details verification
- Legal compliance (license, certificate, insurance)
- Verified badge & re-verification tracking
- Admin notes & reviewer

### verification_documents
- Polymorphic relation (user_verification sau property_verification)
- File storage & metadata
- Status (pending, approved, rejected)
- Admin review notes

## Models

### UserVerification
- Relations: user, reviewer, documents
- Methods:
  - `calculateVerificationScore()`
  - `updateOverallStatus()`
  - `isFullyVerified()`
  - `canRequestBackgroundCheck()`

### PropertyVerification
- Relations: property, user, inspector, reviewer, documents
- Methods:
  - `calculateVerificationScore()`
  - `updateOverallStatus()`
  - `isVerified()`
  - `needsReverification()`
  - `isInsuranceExpired()`
  - `canScheduleInspection()`
  - `approve(User $admin)`
  - `reject(User $admin, string $reason)`

### VerificationDocument
- Polymorphic model
- Methods:
  - `getFileUrl()`
  - `fileExists()`
  - `approve(User $admin, string $notes = null)`
  - `reject(User $admin, string $reason, string $notes = null)`
  - `isPending()`, `isApproved()`, `isRejected()`
  - `getFileSizeFormatted()`

## Workflow

### User Verification Flow
1. User uploads ID documents → `under_review`
2. Admin reviews → `approved` or `rejected`
3. User verifies phone → SMS code → `verified`
4. User uploads address proof → `under_review`
5. Admin reviews → `approved` or `rejected`
6. Optional: Background check request
7. System calculates verification_score
8. If score >= 70 → `fully_verified`

### Property Verification Flow
1. Owner uploads ownership documents → `under_review`
2. Admin reviews ownership → `approved` or `rejected`
3. Owner uploads legal documents (optional)
4. Owner uploads property photos
5. Admin reviews photos → `approved` or `rejected`
6. Optional: Property inspection request
7. Inspector reviews property → inspection_score
8. System calculates verification_score
9. If score >= 80 && ownership approved → `verified` + badge
10. Re-verification due in 1 year

## Admin Actions in Filament

### UserVerification Resource
- View all pending verifications
- Review ID documents (image viewer)
- Approve/Reject with notes
- View verification history
- Manual status override

### PropertyVerification Resource
- View all pending property verifications
- Review ownership documents
- Schedule inspections
- Assign inspectors
- Review inspection reports
- Grant/revoke verified badge
- Track re-verification dates

## Security Features
- File upload validation (type, size)
- Authorization checks (only owner can upload)
- Soft deletes on documents
- Cannot delete reviewed documents
- Admin-only approval/rejection
- Verification code expiry (10 min)
- Secure file storage

## File Storage
- Location: `storage/app/public/verifications/`
- Subdirectories:
  - `id-documents/` - ID cards, passports
  - `selfies/` - Selfie photos
  - `address-proofs/` - Utility bills, bank statements
  - `ownership-documents/` - Property deeds
  - `licenses/` - Business licenses
  - `certificates/` - Safety certificates
  - `insurance/` - Insurance documents
  - `documents/` - Other documents

## Next Steps for Frontend (Next.js)

### Owner Dashboard Components
1. **VerificationWizard.tsx**
   - Multi-step form pentru ID, phone, address verification
   - File upload cu preview
   - Progress indicator

2. **PropertyVerificationForm.tsx**
   - Upload ownership documents
   - Legal documents form
   - Request inspection button

3. **VerificationStatus.tsx**
   - Display verification score
   - Show verified badge
   - List pending/approved/rejected items
   - Re-verification reminders

4. **DocumentUploader.tsx**
   - Drag & drop file upload
   - Multiple file support
   - File preview
   - Upload progress

### Public Website
1. **VerifiedBadge.tsx**
   - Display on property cards
   - Verified owner indicator
   - Tooltip with verification details

2. **PropertyTrustScore.tsx**
   - Visual representation of verification score
   - Trust indicators

## Testing (TODO)
- API endpoint tests
- File upload tests
- Verification workflow tests
- Admin action tests
- Email/SMS notification tests

## Notes
- SMS provider trebuie configurat (Twilio, Nexmo, etc.)
- Email provider configurat în .env
- Background check provider integration (optional)
- Consider adding expiry dates pentru ID documents
- Consider adding document encryption pentru sensitive data
