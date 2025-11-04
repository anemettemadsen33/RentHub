# API Testing Guide - Property Verification System

## Setup

### 1. Start Laravel Server
```bash
cd C:\laragon\www\RentHub\backend
php artisan serve
```

### 2. Create Test User (if needed)
```bash
php artisan tinker

$user = \App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => bcrypt('password'),
    'role' => 'owner'
]);

$token = $user->createToken('test-token')->plainTextToken;
echo $token;
```

## User Verification Tests

### 1. Get My Verification Status
```bash
curl -X GET http://localhost:8000/api/v1/my-verification \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### 2. Submit ID Verification
```bash
curl -X POST http://localhost:8000/api/v1/user-verifications/id \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "id_document_type=passport" \
  -F "id_document_number=AB123456" \
  -F "id_front_image=@/path/to/front.jpg" \
  -F "id_back_image=@/path/to/back.jpg" \
  -F "selfie_image=@/path/to/selfie.jpg"
```

### 3. Request Phone Verification Code
```bash
curl -X POST http://localhost:8000/api/v1/user-verifications/phone/send \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+1234567890"
  }'
```

### 4. Verify Phone Code
```bash
curl -X POST http://localhost:8000/api/v1/user-verifications/phone/verify \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "123456"
  }'
```

### 5. Submit Address Verification
```bash
curl -X POST http://localhost:8000/api/v1/user-verifications/address \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "address=123 Main St, City, Country" \
  -F "address_proof_document=utility_bill" \
  -F "address_proof_image=@/path/to/bill.jpg"
```

### 6. Request Background Check
```bash
curl -X POST http://localhost:8000/api/v1/user-verifications/background-check \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

## Property Verification Tests

### 1. Get Property Verification Status
```bash
curl -X GET http://localhost:8000/api/v1/properties/1/verification \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### 2. Submit Ownership Documents
```bash
curl -X POST http://localhost:8000/api/v1/properties/1/verification/ownership \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "ownership_document_type=deed" \
  -F "ownership_documents[]=@/path/to/deed1.pdf" \
  -F "ownership_documents[]=@/path/to/deed2.pdf"
```

### 3. Submit Legal Documents
```bash
curl -X POST http://localhost:8000/api/v1/properties/1/verification/legal-documents \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json" \
  -F "has_business_license=true" \
  -F "business_license_document=@/path/to/license.pdf" \
  -F "has_safety_certificate=true" \
  -F "safety_certificate_document=@/path/to/certificate.pdf" \
  -F "has_insurance=true" \
  -F "insurance_document=@/path/to/insurance.pdf" \
  -F "insurance_expiry_date=2025-12-31"
```

### 4. Request Property Inspection
```bash
curl -X POST http://localhost:8000/api/v1/properties/1/verification/request-inspection \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### 5. Get All Property Verifications (Owner)
```bash
curl -X GET http://localhost:8000/api/v1/property-verifications \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

## Admin Tests

### 1. Get All User Verifications
```bash
curl -X GET "http://localhost:8000/api/v1/user-verifications?status=under_review" \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Accept: application/json"
```

### 2. Approve User ID Verification
```bash
curl -X POST http://localhost:8000/api/v1/admin/user-verifications/1/approve-id \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Accept: application/json"
```

### 3. Reject User ID Verification
```bash
curl -X POST http://localhost:8000/api/v1/admin/user-verifications/1/reject-id \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Document is not clear"
  }'
```

### 4. Approve Property Ownership
```bash
curl -X POST http://localhost:8000/api/v1/admin/property-verifications/1/approve-ownership \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Accept: application/json"
```

### 5. Reject Property Ownership
```bash
curl -X POST http://localhost:8000/api/v1/admin/property-verifications/1/reject-ownership \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "reason": "Ownership documents are incomplete"
  }'
```

### 6. Schedule Property Inspection
```bash
curl -X POST http://localhost:8000/api/v1/admin/property-verifications/1/schedule-inspection \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "inspection_scheduled_at": "2025-11-15 10:00:00",
    "inspector_id": 2
  }'
```

### 7. Complete Property Inspection
```bash
curl -X POST http://localhost:8000/api/v1/admin/property-verifications/1/complete-inspection \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "status": "completed",
    "inspection_score": 85,
    "inspection_notes": "Property is in good condition",
    "inspection_report": {
      "cleanliness": "excellent",
      "safety": "good",
      "amenities": "all working"
    }
  }'
```

### 8. Grant Verified Badge
```bash
curl -X POST http://localhost:8000/api/v1/admin/property-verifications/1/grant-badge \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Accept: application/json"
```

### 9. Get Verification Statistics
```bash
curl -X GET http://localhost:8000/api/v1/user-verifications/statistics \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Accept: application/json"
```

```bash
curl -X GET http://localhost:8000/api/v1/property-verifications/statistics \
  -H "Authorization: Bearer ADMIN_TOKEN" \
  -H "Accept: application/json"
```

## Postman Collection

You can import these as a Postman collection:

### Create Collection
1. Open Postman
2. Import > Raw Text
3. Paste the cURL commands
4. Set environment variables:
   - `BASE_URL`: http://localhost:8000
   - `USER_TOKEN`: Your user token
   - `ADMIN_TOKEN`: Admin token

### Environment Variables Setup
```json
{
  "BASE_URL": "http://localhost:8000",
  "USER_TOKEN": "your_user_token_here",
  "ADMIN_TOKEN": "your_admin_token_here",
  "PROPERTY_ID": "1",
  "VERIFICATION_ID": "1"
}
```

## Testing Workflow

### User Verification Flow:
1. User registers → GET /my-verification
2. User uploads ID → POST /user-verifications/id
3. Admin reviews → POST /admin/user-verifications/{id}/approve-id
4. User verifies phone → POST /user-verifications/phone/send
5. User enters code → POST /user-verifications/phone/verify
6. User uploads address proof → POST /user-verifications/address
7. Admin reviews → POST /admin/user-verifications/{id}/approve-address
8. (Optional) Background check → POST /user-verifications/background-check
9. Check status → GET /my-verification

### Property Verification Flow:
1. Owner creates property → GET /properties/{id}/verification
2. Owner uploads ownership docs → POST /properties/{id}/verification/ownership
3. Admin reviews ownership → POST /admin/property-verifications/{id}/approve-ownership
4. Owner uploads legal docs → POST /properties/{id}/verification/legal-documents
5. Owner requests inspection → POST /properties/{id}/verification/request-inspection
6. Admin schedules → POST /admin/property-verifications/{id}/schedule-inspection
7. Admin completes inspection → POST /admin/property-verifications/{id}/complete-inspection
8. Admin approves photos → POST /admin/property-verifications/{id}/approve-photos
9. Admin approves details → POST /admin/property-verifications/{id}/approve-details
10. Admin grants badge → POST /admin/property-verifications/{id}/grant-badge
11. Check status → GET /properties/{id}/verification

## Expected Responses

### Success Response:
```json
{
  "message": "ID verification submitted successfully",
  "verification": {
    "id": 1,
    "user_id": 1,
    "id_verification_status": "under_review",
    "overall_status": "partially_verified",
    "verification_score": 30,
    "created_at": "2025-11-02T20:00:00.000000Z",
    "updated_at": "2025-11-02T20:00:00.000000Z"
  }
}
```

### Error Response:
```json
{
  "message": "Unauthorized",
  "errors": {
    "id_document_type": ["The id document type field is required."]
  }
}
```

## Filament Admin Panel Testing

### Access Admin Panel:
```
URL: http://localhost:8000/admin
Login: admin@example.com / password
```

### Test Cases:
1. Navigate to User Verifications
2. Click on a pending verification
3. Review uploaded documents
4. Change status to "approved" or "rejected"
5. Add admin notes
6. Save changes
7. Verify score is auto-calculated
8. Check overall status updated

### Property Verification in Admin:
1. Navigate to Property Verifications
2. View property details
3. Review ownership documents
4. Schedule inspection
5. Complete inspection with score
6. Grant verified badge
7. Check next verification due date

## Common Issues & Solutions

### Issue: File Upload Fails
**Solution**: Check php.ini settings:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Issue: Phone Verification Code Not Received
**Solution**: Check app.debug is true to see code in response (development only)

### Issue: Unauthorized Error
**Solution**: Ensure token is valid and user has correct role

### Issue: Document Not Visible
**Solution**: Run `php artisan storage:link` to create symbolic link

## Database Seeding

### Create Test Data:
```php
// database/seeders/VerificationSeeder.php
php artisan make:seeder VerificationSeeder

// Add test verifications
php artisan db:seed --class=VerificationSeeder
```

## Monitoring

### Check Logs:
```bash
tail -f storage/logs/laravel.log
```

### Database Queries:
```sql
-- Check verification status
SELECT * FROM user_verifications WHERE user_id = 1;

-- Check property verifications
SELECT * FROM property_verifications WHERE property_id = 1;

-- Check pending reviews
SELECT * FROM user_verifications WHERE id_verification_status = 'under_review';
```

---

**Happy Testing! 🚀**
