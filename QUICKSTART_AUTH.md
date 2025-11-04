# 🚀 Quick Start - Authentication System

## ⚡ Fast Setup (5 minutes)

### 1. Backend Setup

```bash
cd backend

# Install dependencies (already done)
composer install

# Configure environment
cp .env.example .env
# Edit .env and set:
# - APP_KEY (run: php artisan key:generate)
# - DB_CONNECTION=sqlite (already set)
# - FRONTEND_URL=http://localhost:3000

# Run migrations (already done)
php artisan migrate

# Start server
php artisan serve
```

Backend running at: **http://localhost:8000**

### 2. Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Configure environment
cp .env.example .env.local
# Edit .env.local:
echo "NEXT_PUBLIC_API_URL=http://localhost:8000" > .env.local
echo "NEXT_PUBLIC_FRONTEND_URL=http://localhost:3000" >> .env.local

# Start development server
npm run dev
```

Frontend running at: **http://localhost:3000**

## 🎯 Test the Features

### 1. User Registration

Navigate to: **http://localhost:3000/auth/register**

**Test with Email:**
```
Name: Test User
Email: test@example.com
Password: password123
Password Confirmation: password123
Phone: +1234567890
Role: Rent properties (tenant)
```

**Or Test with Social Login:**
- Click "Continue with Google" (needs Google OAuth setup)
- Click "Continue with Facebook" (needs Facebook OAuth setup)

### 2. Profile Completion Wizard

After registration, you'll be redirected to: **http://localhost:3000/profile/complete-wizard**

**Step 1 - Basic Info:**
```
Name: Test User (prefilled)
Phone: +1234567890
Date of Birth: 1990-01-01
Gender: Male
```

**Step 2 - Address:**
```
Street Address: 123 Main Street
City: New York
State: NY
Country: USA
ZIP Code: 10001
```

**Step 3 - Phone Verification:**
- Click "Send Verification Code"
- Check console/logs for the code (in development mode)
- Enter the 6-digit code
- Click "Verify"

**Step 4 - Complete:**
- Click "Go to Dashboard"

### 3. Login

Navigate to: **http://localhost:3000/auth/login**

```
Email: test@example.com
Password: password123
```

## 📱 Test Phone Verification (Optional)

### Setup Twilio

1. Create account at: https://www.twilio.com/try-twilio
2. Get your credentials from Console
3. Add to `backend/.env`:

```env
TWILIO_SID=your_account_sid
TWILIO_TOKEN=your_auth_token
TWILIO_FROM=+1234567890
```

4. Restart backend server

Now SMS codes will be sent to real phone numbers!

## 🔐 Test Social Login (Optional)

### Google OAuth

1. Go to: https://console.cloud.google.com/
2. Create project → Enable Google+ API
3. Create OAuth 2.0 credentials
4. Add redirect URI: `http://localhost:8000/api/v1/auth/google/callback`
5. Add to `backend/.env`:

```env
GOOGLE_CLIENT_ID=your_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_client_secret
```

6. Restart backend server

### Facebook OAuth

1. Go to: https://developers.facebook.com/
2. Create App → Add Facebook Login
3. Add redirect URI: `http://localhost:8000/api/v1/auth/facebook/callback`
4. Add to `backend/.env`:

```env
FACEBOOK_CLIENT_ID=your_app_id
FACEBOOK_CLIENT_SECRET=your_app_secret
```

5. Restart backend server

## 🧪 API Testing with Postman/cURL

### Register User

```bash
curl -X POST http://localhost:8000/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+1234567890",
    "role": "tenant"
  }'
```

### Login

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

Response:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": { ... },
    "token": "1|xxxxxxxxxxxxxxxxxxxx"
  }
}
```

### Get Current User

```bash
curl -X GET http://localhost:8000/api/v1/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Send Phone Verification

```bash
curl -X POST http://localhost:8000/api/v1/send-phone-verification \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "phone": "+1234567890"
  }'
```

### Verify Phone

```bash
curl -X POST http://localhost:8000/api/v1/verify-phone \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "code": "123456"
  }'
```

### Profile Completion Status

```bash
curl -X GET http://localhost:8000/api/v1/profile/completion-status \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

Response:
```json
{
  "success": true,
  "data": {
    "percentage": 75.5,
    "completed_steps": 3,
    "total_steps": 4,
    "is_complete": false,
    "missing_fields": ["phone_verified_at"]
  }
}
```

## 🎨 UI Screenshots Locations

After starting the servers, you can access:

1. **Registration Page**: http://localhost:3000/auth/register
   - Email/Password form
   - Social login buttons
   - Role selection

2. **Login Page**: http://localhost:3000/auth/login
   - Email/Password form
   - Remember me checkbox
   - Forgot password link

3. **Profile Wizard**: http://localhost:3000/profile/complete-wizard
   - Multi-step progress bar
   - Form validation
   - Phone verification UI

## 🐛 Troubleshooting

### Backend Issues

**"Class not found" errors:**
```bash
cd backend
composer dump-autoload
```

**Database errors:**
```bash
php artisan migrate:fresh
```

**Port 8000 already in use:**
```bash
php artisan serve --port=8001
# Update NEXT_PUBLIC_API_URL în frontend/.env.local
```

### Frontend Issues

**Module not found:**
```bash
cd frontend
rm -rf node_modules package-lock.json
npm install
```

**Port 3000 already in use:**
```bash
npm run dev -- -p 3001
```

**API connection errors:**
- Check NEXT_PUBLIC_API_URL în .env.local
- Make sure backend is running
- Check browser console for CORS errors

### Common Errors

**"Email already exists"**
- Use different email or check database

**"Invalid credentials"**
- Check email/password combination
- Make sure user exists in database

**"Phone verification code expired"**
- Request new code
- Codes expire after 10 minutes

## 📚 Next Steps

1. ✅ Authentication is complete
2. 📝 Read `AUTHENTICATION_SETUP.md` for detailed documentation
3. 📝 Read `TASK_1.1_COMPLETE.md` for implementation details
4. 🚀 Start implementing Task 1.2 - Property Management

## 🎉 Success!

If you can:
- Register a user ✅
- Verify email ✅
- Complete profile wizard ✅
- Login successfully ✅
- See user data in /api/v1/me ✅

Then everything is working correctly! 🎊

## 💡 Tips

- Use **Chrome DevTools** to debug frontend
- Use **Laravel Telescope** for backend debugging (optional)
- Check `backend/storage/logs/laravel.log` for errors
- Use **Postman** for API testing
- Check database with **DB Browser for SQLite**

## 📞 Need Help?

Check the documentation files:
- `AUTHENTICATION_SETUP.md` - Complete setup guide
- `TASK_1.1_COMPLETE.md` - Implementation details
- `README.md` - Project overview

Happy coding! 🚀
