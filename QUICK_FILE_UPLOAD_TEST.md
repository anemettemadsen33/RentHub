# 🧪 Quick File Upload Test Guide

## ✅ Configuration Status

**Backend:**
- ✅ FILESYSTEM_DISK=public (configured)
- ✅ Storage link exists (public/storage → storage/app/public)
- ✅ Upload API endpoint: `POST /api/v1/properties/{property}/images`
- ✅ Validation: max 10 images, 5MB each, jpeg/jpg/png/gif only

**Storage Location:**
```
backend/storage/app/public/properties/
→ Accessible via: http://localhost:8000/storage/properties/filename.jpg
```

---

## 🚀 Test Upload via API (cURL)

### 1. Get Auth Token

```bash
# Login as owner
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "owner@renthub.test",
    "password": "password"
  }'

# Copy the "token" from response
```

### 2. Upload Image to Property

```bash
# Replace YOUR_TOKEN and path/to/image.jpg
curl -X POST http://localhost:8000/api/v1/properties/1/images \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "images[]=@C:/path/to/test-image.jpg"

# Example response:
{
  "success": true,
  "message": "Images uploaded successfully",
  "data": {
    "uploaded": ["properties/abc123.jpg"],
    "property": {
      "id": 1,
      "images": ["properties/abc123.jpg"],
      "main_image": "properties/abc123.jpg"
    }
  }
}
```

### 3. Verify Image URL

```
Image stored at:
backend/storage/app/public/properties/abc123.jpg

Accessible via:
http://localhost:8000/storage/properties/abc123.jpg
```

Open in browser: `http://localhost:8000/storage/properties/abc123.jpg` ✅

---

## 🎨 Test Upload via Frontend

### 1. Open Property Create/Edit Page

```
http://localhost:3001/properties/create
or
http://localhost:3001/properties/1/edit
```

### 2. Upload Images

1. **Login as owner:**
   - Email: owner@renthub.test
   - Password: password

2. **Navigate to property form**

3. **Upload images:**
   - Click image upload area
   - Select 1-10 images (jpeg, png, jpg, gif)
   - Max 5MB each
   - Wait for upload confirmation

4. **Verify:**
   - Images appear in gallery
   - Main image set automatically
   - Image URLs: `http://localhost:8000/storage/properties/...`

---

## 🧰 Test via Artisan Tinker

```bash
cd backend
php artisan tinker
```

### Create Test File

```php
// Test storage write
Storage::disk('public')->put('properties/test.txt', 'Hello from RentHub!');

// Get URL
Storage::disk('public')->url('properties/test.txt');
// Returns: http://localhost:8000/storage/properties/test.txt

// Check if file exists
Storage::disk('public')->exists('properties/test.txt');
// Returns: true

// List all property images
Storage::disk('public')->files('properties');
// Returns: array of all uploaded images

// Delete test file
Storage::disk('public')->delete('properties/test.txt');
```

### Check Property Images

```php
// Get property with images
$property = App\Models\Property::find(1);
$property->images; // Array of image paths

// Get full URLs
$property->images->map(fn($path) => Storage::disk('public')->url($path));

// Count images
count($property->images);
```

---

## 📁 File Structure Check

### Expected Directory Structure

```
backend/
├── storage/
│   └── app/
│       └── public/
│           └── properties/          ← Images stored here
│               ├── abc123.jpg
│               ├── def456.jpg
│               └── ...
└── public/
    └── storage/                     ← Symlink to storage/app/public
        └── properties/              ← Accessible via browser
```

### Verify Symlink

```bash
# PowerShell
cd c:\laragon\www\RentHub\backend\public
Get-Item storage | Select-Object Target

# Should show: C:\laragon\www\RentHub\backend\storage\app\public
```

---

## ✅ Quick Validation Checklist

**Backend Setup:**
- [ ] `FILESYSTEM_DISK=public` in .env
- [ ] `php artisan config:clear` executed
- [ ] Storage symlink exists
- [ ] Laravel server running (localhost:8000)

**API Test:**
- [ ] Login successful (token received)
- [ ] Upload image via cURL (200 OK response)
- [ ] Image path returned in response
- [ ] Image accessible at URL

**Frontend Test:**
- [ ] Frontend running (localhost:3001)
- [ ] Login as owner successful
- [ ] Property form accessible
- [ ] Image upload component visible
- [ ] Upload completes without errors
- [ ] Image displays in gallery

**File System:**
- [ ] Image file exists in `storage/app/public/properties/`
- [ ] Image accessible via `/storage/properties/filename.jpg`
- [ ] Multiple uploads work (max 10)
- [ ] File size validation works (max 5MB)

---

## ⚠️ Troubleshooting

### Issue: "404 Not Found" when accessing image URL

**Problem:** Storage link broken or not created

**Solution:**
```bash
cd backend
php artisan storage:link
php artisan config:clear
```

### Issue: "Unauthorized" when uploading

**Problem:** Not logged in or wrong property owner

**Solution:**
- Login as property owner (owner@renthub.test)
- Or login as admin (admin@renthub.com)
- Verify token in Authorization header

### Issue: "The images.0 must be an image"

**Problem:** Wrong file type or corrupted file

**Solution:**
- Use jpeg, jpg, png, or gif only
- Check file is not corrupted
- Verify file size < 5MB

### Issue: Image uploaded but doesn't display

**Problem:** Wrong APP_URL or CORS issue

**Solution:**
```env
# backend/.env
APP_URL=http://localhost:8000

# Restart Laravel
php artisan config:clear
php artisan serve
```

### Issue: "Storage disk [public] does not exist"

**Problem:** Config cache outdated

**Solution:**
```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🎯 Expected Results

### Successful Upload Response

```json
{
  "success": true,
  "message": "Images uploaded successfully",
  "data": {
    "uploaded": [
      "properties/abc123def456.jpg",
      "properties/789ghi012jkl.jpg"
    ],
    "property": {
      "id": 1,
      "title": "Beautiful Apartment",
      "images": [
        "properties/abc123def456.jpg",
        "properties/789ghi012jkl.jpg"
      ],
      "main_image": "properties/abc123def456.jpg"
    }
  }
}
```

### Image URLs

```
Stored:   backend/storage/app/public/properties/abc123.jpg
URL:      http://localhost:8000/storage/properties/abc123.jpg
Frontend: <img src="http://localhost:8000/storage/properties/abc123.jpg" />
```

---

## 📊 Validation Rules

| Field | Rules | Example |
|-------|-------|---------|
| **images** | required, array, min:1, max:10 | `['image1.jpg', 'image2.jpg']` |
| **images.\*** | required, image, mimes:jpeg,jpg,png,gif, max:5120 | 5MB max |

**Allowed MIME Types:**
- ✅ image/jpeg
- ✅ image/jpg
- ✅ image/png
- ✅ image/gif
- ❌ image/webp (not allowed)
- ❌ image/svg+xml (security risk)

---

## 🚀 Quick Test Command

```bash
# One-line test (PowerShell)
cd c:\laragon\www\RentHub\backend; php artisan tinker --execute="Storage::disk('public')->put('test.txt', 'Works!'); echo Storage::disk('public')->url('test.txt');"

# Expected output:
# http://localhost:8000/storage/test.txt
```

Then open: http://localhost:8000/storage/test.txt ✅

---

## ✅ Test Complete!

**File upload system is ready for Monday deployment!**

**Current Configuration:**
- Storage: Local (public disk)
- Location: storage/app/public/properties/
- URL: http://localhost:8000/storage/properties/
- Max files: 10 per upload
- Max size: 5MB per file
- Allowed: JPEG, JPG, PNG, GIF

**Production Upgrade Path:**
- Later: Switch to AWS S3 (see FILE_UPLOAD_GUIDE.md)
- Just change FILESYSTEM_DISK=s3
- No code changes needed! ✅
