# 🏠 RentHub - Complete Property Rental Platform

[![Status](https://img.shields.io/badge/status-production--ready-green.svg)](https://github.com)
[![Laravel](https://img.shields.io/badge/Laravel-11-red.svg)](https://laravel.com)
[![Next.js](https://img.shields.io/badge/Next.js-16-black.svg)](https://nextjs.org)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.0-blue.svg)](https://typescriptlang.org)
[![Filament](https://img.shields.io/badge/Filament-4.0-orange.svg)](https://filamentphp.com)

A **complete, production-ready** property rental management platform built with Laravel 11, Filament v4, and Next.js 16.

## ✨ Features

### 🔐 Authentication & User Management
- ✅ User Registration with Email Verification
- ✅ Social Login (Google, Facebook)
- ✅ Two-Factor Authentication (2FA)
- ✅ Phone Verification (SMS/WhatsApp)
- ✅ Profile Completion Wizard
- ✅ Password Reset
- ✅ ID Verification (Government ID)
- ✅ Role Management (Admin, Owner, Tenant)

### 🏡 Property Management
- ✅ Create, Edit, Delete Properties
- ✅ Multi-step Property Form (4 steps)
- ✅ Image Upload (Multiple images)
- ✅ Publish/Unpublish Properties
- ✅ Calendar Management (Block/Unblock dates)
- ✅ Custom Pricing
- ✅ Amenities Management
- ✅ Property Dashboard

### 🔍 Property Listing & Search
- ✅ Advanced Search & Filters
- ✅ Sort Options (Price, Rating, Newest)
- ✅ Property Details Page
- ✅ Image Gallery
- ✅ Reviews Display
- ✅ Featured Properties
- ✅ Similar Properties

### 📅 Booking System
- ✅ Create & Manage Bookings
- ✅ Availability Check
- ✅ Price Calculation
- ✅ Booking Status Tracking
- ✅ Cancel Bookings
- ✅ Check-in/Check-out
- ✅ My Bookings Dashboard

### 💰 Payment System
- ✅ Multiple Bank Accounts (Company + Agent)
- ✅ Automatic Invoice Generation
- ✅ PDF Invoice Generation
- ✅ Email Notifications with PDF
- ✅ Payment Processing (Bank Transfer, PayPal, Cash)
- ✅ Owner Payouts with Commission
- ✅ Payment History & Tracking
- ✅ Refund Processing

### ⭐ Review & Rating System
- ✅ 1-5 Star Rating System
- ✅ 6 Detailed Rating Categories
- ✅ Photo Upload (up to 5 photos)
- ✅ Edit & Delete Reviews
- ✅ Owner Response System
- ✅ Helpful Votes (Community Voting)
- ✅ Verified Guest Badge
- ✅ Rating Statistics & Breakdown
- ✅ Advanced Filtering & Sorting
- ✅ Admin Moderation Panel

### 🔐 Smart Locks Integration (NEW!)
- ✅ Multi-provider Support (Mock, Generic, August, Yale, etc.)
- ✅ Automatic Access Code Generation on Booking
- ✅ Time-Limited Access Codes
- ✅ Email Notifications with Codes
- ✅ Remote Lock/Unlock Control
- ✅ Activity Logging & Monitoring
- ✅ Battery Status Tracking
- ✅ Manual Code Management
- ✅ Guest Code Retrieval
- ✅ Security Audit Trail

### 👨‍💼 Admin Panel (Filament v4)
- ✅ User Management
- ✅ Property Management
- ✅ Booking Management
- ✅ Payment Management
- ✅ Bank Account Management
- ✅ Invoice Management
- ✅ Payout Management
- ✅ Review Moderation
- ✅ Smart Lock Management
- ✅ Access Code Management
- ✅ Lock Activity Monitoring
- ✅ Settings Management

## 📊 Project Statistics

- **Total Models**: 35+
- **API Endpoints**: 195+ (including 19 smart lock endpoints)
- **Filament Resources**: 20+
- **Database Tables**: 50+
- **Lines of Code**: ~38,000+
- **Documentation Pages**: 57+
- **Completed Tasks**: 20/23 (83% complete)

## 🏗️ Project Structure

```
RentHub/
├── backend/                    # Laravel 11 Backend
│   ├── app/
│   │   ├── Models/            # Eloquent Models (14 models)
│   │   ├── Http/Controllers/  # API Controllers
│   │   ├── Filament/          # Filament Resources
│   │   ├── Services/          # Business Logic Services
│   │   ├── Mail/              # Email Templates
│   │   └── Enums/             # Enumerations
│   ├── database/
│   │   ├── migrations/        # Database Migrations (25+)
│   │   └── seeders/           # Database Seeders
│   ├── resources/
│   │   └── views/             # Blade Templates (PDF, Email)
│   └── routes/
│       └── api.php            # API Routes (79+ endpoints)
│
├── frontend/                   # Next.js 16 Frontend
│   ├── src/
│   │   ├── app/               # Next.js App Router
│   │   ├── components/        # React Components
│   │   ├── lib/               # Utilities & API
│   │   └── types/             # TypeScript Types
│   └── public/                # Static Assets
│
├── docs/                       # Documentation
│   ├── TASK_1.1_COMPLETE.md   # Auth Documentation
│   ├── TASK_1.2_COMPLETE.md   # Properties Documentation
│   ├── TASK_1.3_COMPLETE.md   # Listing Documentation
│   ├── TASK_1.4_COMPLETE.md   # Booking Documentation
│   ├── TASK_1.5_COMPLETE.md   # Payment Documentation
│   ├── TASK_1.6_COMPLETE.md   # Review & Rating Documentation
│   ├── PAYMENT_API_GUIDE.md   # Payment API Guide
│   ├── REVIEW_API_GUIDE.md    # Review API Guide
│   ├── POSTMAN_REVIEW_TESTS.md # Postman Test Collection
│   └── ALL_TASKS_STATUS.md    # Overall Status
│
└── README.md                   # This file
```

## 🚀 Tech Stack

### Backend
- **Laravel 11** - PHP Framework
- **Filament v4** - Admin Panel
- **Laravel Sanctum** - API Authentication
- **DomPDF** - PDF Generation
- **Laravel Mail** - Email System
- **MySQL/SQLite** - Database

### Frontend
- **Next.js 16** - React Framework
- **React 19** - UI Library
- **TypeScript 5.0** - Type Safety
- **Tailwind CSS v4** - Styling
- **Axios** - HTTP Client
- **React Hook Form** - Form Management
- **Zod** - Validation

### DevOps
- **Composer** - PHP Dependencies
- **NPM** - JS Dependencies
- **Git** - Version Control

## Development Setup

### Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve
```

### Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

## Deployment

### Backend (Laravel Forge)
- Deploy to Laravel Forge
- Configure environment variables
- Set up database
- Run migrations

### Frontend (Vercel)
- Deploy to Vercel
- Configure environment variables
- Connect to backend API

## Environment Variables

### Backend (.env)
```
APP_URL=https://api.renthub.com
FRONTEND_URL=https://renthub.com
SESSION_DOMAIN=.renthub.com
SANCTUM_STATEFUL_DOMAINS=renthub.com
```

### Frontend (.env.local)
```
NEXT_PUBLIC_API_URL=https://api.renthub.com
NEXTAUTH_URL=https://renthub.com
NEXTAUTH_SECRET=your-secret-here
```

## 📚 Documentation

### Complete Feature Guides
- 📖 [Authentication System](./TASK_1.1_COMPLETE.md)
- 📖 [Property Management](./TASK_1.2_COMPLETE.md)
- 📖 [Booking System](./TASK_1.4_COMPLETE.md)
- 📖 [Payment & Invoicing](./TASK_1.5_COMPLETE.md)
- 📖 [Review System](./TASK_1.6_COMPLETE.md)
- 📖 [Messaging System](./TASK_2.1_COMPLETE.md)
- 📖 [Calendar Management](./TASK_2.3_COMPLETE.md)
- 📖 [Map Search](./TASK_2.4_MAP_SEARCH_COMPLETE.md)
- 📖 [Property Verification](./TASK_2_5_PROPERTY_VERIFICATION_COMPLETED.md)
- 📖 [Dashboard Analytics](./TASK_2.5_2.6_COMPLETE.md)
- 📖 [Multi-language Support](./TASK_2.7_MULTILANGUAGE_COMPLETE.md)
- 📖 [Smart Pricing](./TASK_3.1_SMART_PRICING_COMPLETE.md)
- 📖 [Long-term Rentals](./TASK_3.3_LONG_TERM_RENTALS_COMPLETE.md)
- 📖 [Property Comparison](./TASK_3.4_PROPERTY_COMPARISON_COMPLETE.md)
- 📖 [Insurance Integration](./TASK_3.6_INSURANCE_INTEGRATION_COMPLETE.md)
- 📖 **[Smart Locks Integration](./START_HERE_SMART_LOCKS.md)** ← **LATEST!**

### API Guides
- 📖 [Payment API Guide](./PAYMENT_API_GUIDE.md)
- 📖 [Review API Guide](./REVIEW_API_GUIDE.md)
- 📖 [Notification API Guide](./NOTIFICATION_API_GUIDE.md)
- 📖 [Messaging API Guide](./MESSAGING_API_GUIDE.md)
- 📖 [Calendar API Guide](./CALENDAR_API_GUIDE.md)
- 📖 [Map Search API Guide](./MAP_SEARCH_API_GUIDE.md)
- 📖 [Dashboard Analytics API Guide](./DASHBOARD_ANALYTICS_API_GUIDE.md)
- 📖 [Smart Pricing API Guide](./SMART_PRICING_API_GUIDE.md)
- 📖 [Insurance API Guide](./INSURANCE_API_GUIDE.md)
- 📖 **[Smart Locks API Guide](./SMART_LOCKS_API_GUIDE.md)** ← **NEW!**

### Quick Start Guides
- 🚀 [General Quick Start](./QUICKSTART.md)
- 🚀 [Authentication Setup](./QUICKSTART_AUTH.md)
- 🚀 [Invoice Automation](./QUICK_START_INVOICE_AUTOMATION.md)
- 🚀 [Map Search Setup](./SETUP_MAP_SEARCH.md)
- 🚀 **[Smart Locks Setup](./START_HERE_SMART_LOCKS.md)** ← **NEW!**

### Testing Guides
- 🧪 [Review Testing (Postman)](./POSTMAN_REVIEW_TESTS.md)
- 🧪 [Property Comparison Testing](./POSTMAN_PROPERTY_COMPARISON_TESTS.md)
- 🧪 [Smart Pricing Testing](./SMART_PRICING_TESTS.md)
- 🧪 **[Smart Locks Testing](./POSTMAN_SMART_LOCKS_TESTS.md)** ← **NEW!**

### Project Status
- 📊 [Overall Project Status](./PROJECT_STATUS_2025_11_02_SMART_LOCKS.md)
- 📊 [All Tasks Status](./ALL_TASKS_STATUS.md)
- 📊 [Implementation Complete](./IMPLEMENTATION_COMPLETE_NOV_2_2025.md)

## 🎯 Quick Links

- 🔐 **Smart Locks (Latest):**
  - [Quick Start](./START_HERE_SMART_LOCKS.md)
  - [API Guide](./SMART_LOCKS_API_GUIDE.md)
  - [Postman Tests](./POSTMAN_SMART_LOCKS_TESTS.md)
  - [Quick Reference](./SMART_LOCKS_QUICK_REFERENCE.md)

## License

Private
