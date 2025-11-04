# 📡 RentHub API Endpoints Documentation

**Base URL**: `http://localhost:8000/api/v1`  
**Authentication**: Bearer Token (Laravel Sanctum)

---

## 🔓 Public Endpoints (No Auth Required)

### Authentication

#### Register User
```http
POST /register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+1234567890",
  "role": "tenant"
}

Response 201:
{
  "success": true,
  "message": "Registration successful! Please check your email to verify your account.",
  "data": {
    "user": { ... },
    "token": "1|xxxxxxxxxxxxxxxxxxxx"
  }
}
```

#### Login
```http
POST /login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123",
  "remember": true
}

Response 200:
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "1|xxxxxxxxxxxxxxxxxxxx"
  }
}
```

#### Login with 2FA
```http
Response 200 (when 2FA enabled):
{
  "success": true,
  "message": "2FA code sent to your email",
  "requires_2fa": true,
  "code": "123456"  // Only in development
}
```

#### Verify Email
```http
GET /verify-email/{id}/{hash}

Response 200:
{
  "success": true,
  "message": "Email verified successfully"
}
```

#### Forgot Password
```http
POST /forgot-password
Content-Type: application/json

{
  "email": "john@example.com"
}

Response 200:
{
  "success": true,
  "message": "Password reset link sent to your email"
}
```

#### Reset Password
```http
POST /reset-password
Content-Type: application/json

{
  "token": "reset_token_here",
  "email": "john@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}

Response 200:
{
  "success": true,
  "message": "Password reset successfully"
}
```

### Social Authentication

#### Google Login
```http
GET /auth/google

Response: Redirect to Google OAuth
```

#### Google Callback
```http
GET /auth/google/callback?code=xxx&state=xxx

Response: Redirect to frontend with token
```

#### Facebook Login
```http
GET /auth/facebook

Response: Redirect to Facebook OAuth
```

#### Facebook Callback
```http
GET /auth/facebook/callback?code=xxx

Response: Redirect to frontend with token
```

### Two-Factor Authentication (Login)

#### Send 2FA Code
```http
POST /2fa/send-code
Content-Type: application/json

{
  "email": "john@example.com"
}

Response 200:
{
  "success": true,
  "message": "2FA code sent",
  "code": "123456"  // Only in development
}
```

#### Verify 2FA Code
```http
POST /2fa/verify
Content-Type: application/json

{
  "email": "john@example.com",
  "code": "123456"
}

Response 200:
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "1|xxxxxxxxxxxxxxxxxxxx"
  }
}
```

#### Verify Recovery Code
```http
POST /2fa/verify-recovery
Content-Type: application/json

{
  "email": "john@example.com",
  "recovery_code": "abc123-def456"
}

Response 200:
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "1|xxxxxxxxxxxxxxxxxxxx",
    "remaining_recovery_codes": 7
  }
}
```

---

## 🔒 Protected Endpoints (Auth Required)

**Headers Required:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Authentication

#### Logout
```http
POST /logout

Response 200:
{
  "success": true,
  "message": "Logged out successfully"
}
```

#### Get Current User
```http
GET /me

Response 200:
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "role": "tenant",
    "email_verified_at": "2025-11-02T10:30:00.000000Z",
    "phone_verified_at": "2025-11-02T10:35:00.000000Z",
    "profile_completed_at": "2025-11-02T10:40:00.000000Z",
    ...
  }
}
```

#### Resend Email Verification
```http
POST /resend-verification

Response 200:
{
  "success": true,
  "message": "Verification email sent"
}
```

### Phone Verification

#### Send Phone Verification Code
```http
POST /send-phone-verification
Content-Type: application/json

{
  "phone": "+1234567890"
}

Response 200:
{
  "success": true,
  "message": "Verification code sent to your phone",
  "code": "123456"  // Only in development
}
```

#### Verify Phone Code
```http
POST /verify-phone
Content-Type: application/json

{
  "code": "123456"
}

Response 200:
{
  "success": true,
  "message": "Phone verified successfully"
}
```

### Two-Factor Authentication (Settings)

#### Enable 2FA
```http
POST /2fa/enable

Response 200:
{
  "success": true,
  "message": "Two-factor authentication enabled",
  "data": {
    "recovery_codes": [
      "abc123-def456",
      "ghi789-jkl012",
      ...
    ]
  }
}
```

#### Disable 2FA
```http
POST /2fa/disable
Content-Type: application/json

{
  "password": "password123"
}

Response 200:
{
  "success": true,
  "message": "Two-factor authentication disabled"
}
```

### Profile Management

#### Get Profile Completion Status
```http
GET /profile/completion-status

Response 200:
{
  "success": true,
  "data": {
    "percentage": 75.5,
    "completed_steps": 3,
    "total_steps": 4,
    "steps": {
      "basic_info": {
        "label": "Basic Information",
        "fields": ["name", "email", "phone", "date_of_birth"],
        "completed": true
      },
      "address": {
        "label": "Address",
        "fields": ["address", "city", "state", "country", "zip_code"],
        "completed": true
      },
      "bio": {
        "label": "Bio & Avatar",
        "fields": ["bio", "avatar"],
        "completed": false
      },
      "verification": {
        "label": "Email & Phone Verification",
        "fields": ["email_verified_at", "phone_verified_at"],
        "completed": true
      }
    },
    "is_complete": false,
    "missing_fields": ["bio", "avatar"],
    "profile_completed_at": null
  }
}
```

#### Update Basic Info
```http
POST /profile/basic-info
Content-Type: application/json

{
  "name": "John Doe",
  "phone": "+1234567890",
  "date_of_birth": "1990-01-01",
  "gender": "male"
}

Response 200:
{
  "success": true,
  "message": "Basic information updated successfully",
  "data": { ... }
}
```

#### Update Contact Info
```http
POST /profile/contact-info
Content-Type: application/json

{
  "phone": "+1234567890"
}

Response 200:
{
  "success": true,
  "message": "Contact information updated successfully",
  "data": { ... }
}
```

#### Update Profile Details
```http
POST /profile/details
Content-Type: application/json

{
  "bio": "I'm a software developer...",
  "address": "123 Main St",
  "city": "New York",
  "state": "NY",
  "country": "USA",
  "zip_code": "10001"
}

Response 200:
{
  "success": true,
  "message": "Profile details updated successfully",
  "data": { ... }
}
```

#### Complete Profile Wizard
```http
POST /profile/complete

Response 200:
{
  "success": true,
  "message": "Profile completed successfully",
  "data": { ... }
}

Response 422 (if incomplete):
{
  "success": false,
  "message": "Please complete all required fields",
  "missing_fields": ["bio", "avatar"]
}
```

#### Get Profile
```http
GET /profile

Response 200:
{
  "success": true,
  "data": { ... }
}
```

#### Update Profile
```http
PUT /profile
Content-Type: application/json

{
  "name": "John Doe",
  "phone": "+1234567890",
  "bio": "Software developer",
  "date_of_birth": "1990-01-01",
  "gender": "male",
  "address": "123 Main St",
  "city": "New York",
  "state": "NY",
  "country": "USA",
  "zip_code": "10001"
}

Response 200:
{
  "success": true,
  "message": "Profile updated successfully",
  "data": { ... }
}
```

#### Upload Avatar
```http
POST /profile/avatar
Content-Type: multipart/form-data

FormData:
- avatar: (file) image.jpg

Response 200:
{
  "success": true,
  "message": "Avatar uploaded successfully",
  "data": {
    "avatar": "avatars/xxx.jpg",
    "avatar_url": "http://localhost:8000/storage/avatars/xxx.jpg"
  }
}
```

#### Delete Avatar
```http
DELETE /profile/avatar

Response 200:
{
  "success": true,
  "message": "Avatar deleted successfully"
}
```

---

## 🗄️ User Object Structure

```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "role": "tenant",
  "email_verified_at": "2025-11-02T10:30:00.000000Z",
  "phone_verified_at": "2025-11-02T10:35:00.000000Z",
  "avatar": "avatars/xxx.jpg",
  "avatar_url": "http://localhost:8000/storage/avatars/xxx.jpg",
  "bio": "I'm a software developer...",
  "date_of_birth": "1990-01-01",
  "gender": "male",
  "address": "123 Main St",
  "city": "New York",
  "state": "NY",
  "country": "USA",
  "zip_code": "10001",
  "profile_completed_at": "2025-11-02T10:40:00.000000Z",
  "two_factor_enabled": false,
  "settings": {},
  "privacy_settings": {},
  "created_at": "2025-11-02T10:00:00.000000Z",
  "updated_at": "2025-11-02T10:40:00.000000Z"
}
```

---

## ❌ Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "Invalid request"
}
```

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Internal server error"
}
```

---

## 📝 Notes

- All timestamps are in UTC
- All dates are in `YYYY-MM-DD` format
- Phone numbers can be in any format
- Passwords must be at least 8 characters
- Avatar max size: 2MB
- Avatar formats: jpeg, png, jpg, gif
- Verification codes expire after 10 minutes
- 2FA recovery codes expire after use

---

## 🧪 Testing with cURL

### Quick Test Script

```bash
# Register
TOKEN=$(curl -s -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123","role":"tenant"}' \
  | jq -r '.data.token')

echo "Token: $TOKEN"

# Get current user
curl -X GET http://localhost:8000/api/v1/me \
  -H "Authorization: Bearer $TOKEN"

# Update profile
curl -X POST http://localhost:8000/api/v1/profile/basic-info \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User Updated","phone":"+1234567890"}'

# Logout
curl -X POST http://localhost:8000/api/v1/logout \
  -H "Authorization: Bearer $TOKEN"
```

---

## 📦 Postman Collection

Import this JSON into Postman for easy testing:

[Download Postman Collection](./postman_collection.json)

Or use the online documentation:
[API Documentation](http://localhost:8000/docs)

---

**Last Updated**: November 2, 2025  
**API Version**: v1  
**Documentation Status**: Complete ✅
