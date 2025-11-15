# File Upload Configuration Guide

## ✅ Current Status

**Backend Configuration:**
- ✅ Local storage configured (`FILESYSTEM_DISK=local`)
- ✅ Storage symlink created (`public/storage` → `storage/app/public`)
- ✅ Upload controllers exist (StorageController, FileUploadController)
- ✅ S3 configuration ready (needs credentials)

**Frontend Components:**
- ✅ ImageUpload component exists
- ✅ MultipleFileUpload component exists

## 🎯 Three Options for File Storage

### Option 1: Local Storage (RECOMMENDED for Development) ✅

**Already Configured!** No external services needed.

#### Configuration

**Backend** (`backend/.env`):
```env
FILESYSTEM_DISK=public
APP_URL=http://localhost:8000
```

**Frontend** (`frontend/.env.local`):
```env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1
```

#### How It Works

1. **Upload:**
   - Files saved to `backend/storage/app/public/`
   - Accessible via `http://localhost:8000/storage/filename.jpg`

2. **Image URLs:**
   ```
   backend/storage/app/public/properties/image.jpg
   →
   http://localhost:8000/storage/properties/image.jpg
   ```

3. **Test Upload:**
   ```bash
   cd backend
   php artisan tinker
   
   # Test file storage
   Storage::disk('public')->put('test.txt', 'Hello World');
   Storage::disk('public')->url('test.txt');
   # Returns: http://localhost:8000/storage/test.txt
   ```

#### Advantages
- ✅ **No cost** - completely free
- ✅ **No setup** - works immediately
- ✅ **Fast** - local file system
- ✅ **Simple** - no external dependencies

#### Disadvantages
- ❌ **Not scalable** - files stored on server
- ❌ **No CDN** - slower for distant users
- ❌ **Backup required** - manual file backups
- ❌ **Production issues** - not suitable for multi-server setup

#### When to Use
- ✅ Development/testing
- ✅ Proof of concept
- ✅ Small projects with single server
- ❌ Production with multiple servers
- ❌ High-traffic applications

---

### Option 2: AWS S3 (Best for Production)

**Professional cloud storage with global CDN.**

#### Setup Steps

1. **Create AWS Account:**
   - Visit: https://aws.amazon.com/
   - Sign up (requires credit card, but free tier available)

2. **Create S3 Bucket:**
   ```
   AWS Console → S3 → Create Bucket
   
   Bucket name: renthub-uploads
   Region: eu-central-1 (Frankfurt) or closest to you
   Block public access: OFF (we need public read)
   Object ownership: ACLs enabled
   ```

3. **Configure Bucket Policy:**
   - Go to bucket → Permissions → Bucket Policy
   - Add this policy (replace `renthub-uploads`):
   ```json
   {
     "Version": "2012-10-17",
     "Statement": [
       {
         "Sid": "PublicReadGetObject",
         "Effect": "Allow",
         "Principal": "*",
         "Action": "s3:GetObject",
         "Resource": "arn:aws:s3:::renthub-uploads/*"
       }
     ]
   }
   ```

4. **Enable CORS:**
   - Go to bucket → Permissions → CORS
   - Add this configuration:
   ```json
   [
     {
       "AllowedHeaders": ["*"],
       "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
       "AllowedOrigins": ["http://localhost:3001", "https://yourdomain.com"],
       "ExposeHeaders": ["ETag"]
     }
   ]
   ```

5. **Create IAM User:**
   ```
   AWS Console → IAM → Users → Add User
   
   Username: renthub-s3-uploader
   Access type: Programmatic access
   Permissions: Attach existing policy → AmazonS3FullAccess
   ```

6. **Get Credentials:**
   - After creating user, copy:
     - **Access Key ID**
     - **Secret Access Key**

7. **Update Backend .env:**
   ```env
   FILESYSTEM_DISK=s3
   
   AWS_ACCESS_KEY_ID=your_access_key_id_here
   AWS_SECRET_ACCESS_KEY=your_secret_access_key_here
   AWS_DEFAULT_REGION=eu-central-1
   AWS_BUCKET=renthub-uploads
   AWS_URL=https://renthub-uploads.s3.eu-central-1.amazonaws.com
   AWS_USE_PATH_STYLE_ENDPOINT=false
   ```

8. **Install AWS SDK (if not installed):**
   ```bash
   cd backend
   composer require league/flysystem-aws-s3-v3 "^3.0"
   ```

9. **Test S3 Upload:**
   ```bash
   php artisan tinker
   
   Storage::disk('s3')->put('test.txt', 'Hello from S3');
   Storage::disk('s3')->url('test.txt');
   # Returns: https://renthub-uploads.s3.eu-central-1.amazonaws.com/test.txt
   ```

#### Cost
- **Free tier:** 5GB storage, 20,000 GET requests, 2,000 PUT requests per month (12 months)
- **After free tier:** ~$0.023 per GB/month + ~$0.005 per 1,000 requests
- **Estimated cost:** $2-10/month for small-medium app

#### Advantages
- ✅ **Scalable** - unlimited storage
- ✅ **Fast** - global CDN
- ✅ **Reliable** - 99.999999999% durability
- ✅ **Professional** - industry standard
- ✅ **Automatic backups** - built-in versioning

#### Disadvantages
- ❌ **Costs money** - not free (but cheap)
- ❌ **Complex setup** - requires AWS account
- ❌ **Learning curve** - AWS console can be confusing

---

### Option 3: Cloudinary (Easiest Cloud Solution)

**Image hosting with automatic optimization and transformations.**

#### Setup Steps

1. **Create Cloudinary Account:**
   - Visit: https://cloudinary.com/users/register/free
   - Free tier: 25GB storage, 25GB bandwidth/month

2. **Get Credentials:**
   - Dashboard → Account Details
   - Copy:
     - **Cloud Name**
     - **API Key**
     - **API Secret**

3. **Install Cloudinary SDK:**
   ```bash
   cd backend
   composer require cloudinary/cloudinary_php
   ```

4. **Update Backend .env:**
   ```env
   CLOUDINARY_URL=cloudinary://api_key:api_secret@cloud_name
   CLOUDINARY_CLOUD_NAME=your_cloud_name
   CLOUDINARY_API_KEY=your_api_key
   CLOUDINARY_API_SECRET=your_api_secret
   
   FILESYSTEM_DISK=cloudinary
   ```

5. **Configure Filesystem** (`config/filesystems.php`):
   ```php
   'cloudinary' => [
       'driver' => 'cloudinary',
       'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
       'api_key' => env('CLOUDINARY_API_KEY'),
       'api_secret' => env('CLOUDINARY_API_SECRET'),
   ],
   ```

6. **Test Cloudinary:**
   ```bash
   php artisan tinker
   
   \Cloudinary\Uploader::upload('path/to/test-image.jpg');
   ```

#### Cost
- **Free tier:** 25GB storage, 25GB bandwidth, 25 credits/month
- **Paid plans:** Start at $89/month (for serious apps)
- **Overage:** $1 per 1GB bandwidth

#### Advantages
- ✅ **Easy setup** - simpler than AWS
- ✅ **Image optimization** - automatic compression
- ✅ **Transformations** - resize, crop, filters on-the-fly
- ✅ **Fast CDN** - global delivery
- ✅ **Generous free tier** - good for MVP

#### Disadvantages
- ❌ **Limited free tier** - 25GB total (vs S3's unlimited)
- ❌ **Expensive scaling** - $89/month minimum for paid
- ❌ **Vendor lock-in** - harder to migrate away

---

## 🧪 Testing File Upload

### Backend API Test

```bash
# Test with cURL
curl -X POST http://localhost:8000/api/v1/properties/1/images \
  -H "Authorization: Bearer your_token_here" \
  -F "image=@/path/to/test-image.jpg"
```

### Frontend Test

1. **Upload Component Usage:**
   ```tsx
   import { ImageUpload } from '@/components/ui/ImageUpload';
   
   <ImageUpload
     onUpload={(url) => console.log('Uploaded:', url)}
     maxSize={5 * 1024 * 1024} // 5MB
     accept="image/*"
   />
   ```

2. **Multiple Images:**
   ```tsx
   import { MultipleFileUpload } from '@/components/ui/MultipleFileUpload';
   
   <MultipleFileUpload
     maxFiles={10}
     onUploadComplete={(urls) => console.log('All uploaded:', urls)}
   />
   ```

### Database Check

```bash
cd backend
php artisan tinker

# Check property images
App\Models\Property::find(1)->images;

# Check uploaded files
App\Models\Media::latest()->take(5)->get();
```

---

## 📊 Recommendation Matrix

| Scenario | Recommended Option | Why |
|----------|-------------------|-----|
| **Development/Testing** | Local Storage | Free, simple, instant |
| **MVP/Demo** | Cloudinary Free Tier | Easy + professional URLs |
| **Small Production** | Cloudinary Paid | Managed service, less maintenance |
| **Medium Production** | AWS S3 | Best price/performance ratio |
| **Large Production** | AWS S3 + CloudFront | Enterprise-grade, fully scalable |

---

## 🚀 Quick Start (Recommended for Monday)

**Use Local Storage for Now:**

1. **Update backend/.env:**
   ```env
   FILESYSTEM_DISK=public
   ```

2. **Restart Laravel:**
   ```bash
   cd backend
   php artisan config:clear
   php artisan serve
   ```

3. **Test Upload:**
   - Go to http://localhost:3001
   - Create/edit property
   - Upload image
   - Image URL: `http://localhost:8000/storage/properties/image.jpg`

**✅ This works immediately!** No external services needed.

**Later (Production):** Switch to S3 by just changing `FILESYSTEM_DISK=s3` and adding AWS credentials. Your code doesn't change!

---

## 🔐 Security Best Practices

### File Validation

**Backend** (already implemented):
```php
$request->validate([
    'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
]);
```

### Allowed MIME Types
- **Images:** jpeg, png, jpg, gif, webp
- **Documents:** pdf
- **Max size:** 5MB for images, 10MB for documents

### Storage Structure
```
storage/app/public/
├── properties/
│   ├── {property_id}/
│   │   ├── main.jpg
│   │   ├── gallery-1.jpg
│   │   └── gallery-2.jpg
├── users/
│   ├── avatars/
│   └── documents/
└── temp/
```

---

## ⚠️ Common Issues

### "File not found after upload"
- **Problem:** Storage link not created
- **Solution:** `php artisan storage:link`

### "403 Forbidden on S3"
- **Problem:** Bucket policy not set
- **Solution:** Add public read policy (see AWS S3 section)

### "CORS error on upload"
- **Problem:** S3 CORS not configured
- **Solution:** Add CORS policy (see AWS S3 section)

### "Images don't load"
- **Problem:** Wrong `APP_URL` in .env
- **Solution:** Verify `APP_URL=http://localhost:8000`

---

## 📁 Current Implementation Status

✅ **Backend Ready:**
- File upload API endpoints exist
- Validation implemented
- Multiple storage drivers supported
- Image processing ready

✅ **Frontend Ready:**
- Upload components exist
- Drag & drop support
- Progress indicators
- Error handling

⚠️ **Needs:**
- Choose storage option (local/S3/Cloudinary)
- Add credentials (if using cloud)
- Test upload flow

---

## 🎯 Next Steps

**For Monday Launch:**
1. ✅ Keep local storage (`FILESYSTEM_DISK=public`)
2. Test property image upload
3. Verify images display correctly

**After Launch:**
1. Create AWS S3 account (~20 min)
2. Configure S3 bucket (~15 min)
3. Update .env with S3 credentials
4. Change `FILESYSTEM_DISK=s3`
5. Migrate existing local files to S3

**Total setup time:**
- Local: 0 minutes (already working!)
- S3: ~35 minutes
- Cloudinary: ~15 minutes

---

**Current Configuration: LOCAL STORAGE ✅**

Upload-ul funcționează deja cu storage local! Poți testa imediat încărcarea de imagini pentru proprietăți.
