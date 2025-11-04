# 🚀 Quick Start - Wishlist/Favorites System

## Getting Started in 5 Minutes

### Prerequisites
- Backend running on `http://localhost:8000`
- Frontend running on `http://localhost:3000`
- User account created and logged in

### Step 1: Verify Installation ✅

```bash
# Check migrations ran
cd backend
php artisan migrate:status

# Should see:
# ✓ 2025_11_02_172722_create_wishlists_table
# ✓ 2025_11_02_172726_create_wishlist_items_table
```

### Step 2: Test Backend API 🔧

```bash
# Get your auth token first
POST http://localhost:8000/api/v1/login
{
  "email": "user@example.com",
  "password": "password"
}

# Test wishlists endpoint
GET http://localhost:8000/api/v1/wishlists
Authorization: Bearer YOUR_TOKEN
```

**Expected Response:**
```json
{
  "success": true,
  "data": []
}
```

### Step 3: Test Frontend 🎨

1. **Navigate to wishlists page:**
   ```
   http://localhost:3000/wishlists
   ```

2. **You should see:**
   - "My Wishlists" heading
   - "Create Wishlist" button
   - Empty state (if no wishlists yet)

3. **Create your first wishlist:**
   - Click "Create Wishlist"
   - Name: "My Favorites"
   - Click "Create"

### Step 4: Add a Property ❤️

1. **Go to properties page:**
   ```
   http://localhost:3000/properties
   ```

2. **Click the heart icon** on any property card

3. **Verify:**
   - Heart should fill with red
   - Toast notification appears
   - Property is saved to "My Favorites"

### Step 5: View Your Wishlist 👀

1. **Navigate back to wishlists:**
   ```
   http://localhost:3000/wishlists
   ```

2. **Click on "My Favorites"**

3. **You should see:**
   - Your saved property
   - "Alerts" button
   - "Remove" button

## Common Use Cases

### Use Case 1: Quick Save Properties
```tsx
// Add WishlistButton to any property card
import WishlistButton from '@/components/wishlists/WishlistButton';

<WishlistButton propertyId={property.id} />
```

### Use Case 2: Organize by Category
```typescript
// Create multiple wishlists
await createWishlist({ name: "Beach Vacations" });
await createWishlist({ name: "City Breaks" });
await createWishlist({ name: "Business Travel" });
```

### Use Case 3: Set Price Alerts
```typescript
// Add property with price alert
await addPropertyToWishlist(wishlistId, {
  property_id: propertyId,
  price_alert: 100.00, // Get notified if price drops to €100 or below
  notify_availability: true
});
```

### Use Case 4: Share Your Wishlist
```typescript
// Make wishlist public
await updateWishlist(wishlistId, { is_public: true });

// Get share URL
const wishlist = await getWishlist(wishlistId);
const shareUrl = `${window.location.origin}/wishlists/shared/${wishlist.share_token}`;

// Share the URL with friends!
```

## Admin Panel

### Access Filament Admin
```
http://localhost:8000/admin/wishlists
```

**You can:**
- View all users' wishlists
- See property counts
- Check public/private status
- View share tokens
- Manage wishlists

## Testing Checklist

Use this checklist to verify everything works:

- [ ] Create a wishlist
- [ ] Add property to wishlist (via heart button)
- [ ] View wishlist with properties
- [ ] Set price alert on a property
- [ ] Remove property from wishlist
- [ ] Update wishlist name
- [ ] Delete wishlist
- [ ] Make wishlist public
- [ ] Copy share link
- [ ] View shared wishlist (logged out)
- [ ] Check property status (in wishlist or not)

## Troubleshooting

### Issue: "Unauthenticated" Error
**Solution:** Make sure you're logged in. Check token in localStorage:
```javascript
localStorage.getItem('token')
```

### Issue: Heart Button Not Working
**Solution:** 
1. Check browser console for errors
2. Verify API endpoint is reachable
3. Check CORS settings

### Issue: Can't See Properties in Wishlist
**Solution:**
1. Verify property was actually added (check API response)
2. Reload the page
3. Check database: `SELECT * FROM wishlist_items;`

### Issue: Price Alerts Not Sending
**Solution:**
1. Check queue worker is running: `php artisan queue:work`
2. Verify email config in `.env`
3. Test email: `php artisan tinker` then:
   ```php
   Mail::raw('Test', function($m) {
       $m->to('test@example.com')->subject('Test');
   });
   ```

### Issue: Share Link Not Working
**Solution:**
1. Verify wishlist is public (`is_public = true`)
2. Check share_token exists in database
3. Try the URL in incognito mode

## API Quick Reference

```bash
# Get all wishlists
GET /api/v1/wishlists

# Create wishlist
POST /api/v1/wishlists
{"name": "My List", "is_public": false}

# Quick add/remove property
POST /api/v1/wishlists/toggle-property
{"property_id": 5}

# Check if property is saved
GET /api/v1/wishlists/check/5

# View shared wishlist
GET /api/v1/wishlists/shared/{token}
```

## Next Steps

Now that you have wishlists working:

1. **Customize the UI** - Update styles in component files
2. **Add more features** - See TASK_2.2_WISHLIST_COMPLETE.md for enhancement ideas
3. **Test edge cases** - Try with multiple users, many properties, etc.
4. **Monitor notifications** - Test price drop alerts
5. **Share feedback** - Report bugs or suggest improvements

## Quick Links

- 📖 **Full Documentation:** `TASK_2.2_WISHLIST_COMPLETE.md`
- 🔌 **API Guide:** `WISHLIST_API_GUIDE.md`
- 🎯 **Task Roadmap:** `ROADMAP.md`
- 💬 **Other Features:** `ALL_TASKS_COMPLETE.md`

## Support

**Backend Issues:**
```bash
# Check logs
tail -f backend/storage/logs/laravel.log

# Clear cache
php artisan cache:clear
php artisan config:clear
```

**Frontend Issues:**
```bash
# Check console in browser DevTools
# Clear Next.js cache
rm -rf .next
npm run build
npm run dev
```

**Database Issues:**
```bash
# Check tables exist
php artisan db:show

# Re-run migrations
php artisan migrate:fresh
```

---

## Success! 🎉

If you completed all steps and everything works, you now have a fully functional wishlist/favorites system!

Users can:
- ❤️ Save favorite properties
- 📋 Organize in multiple wishlists
- 🔔 Get price drop alerts
- 🔗 Share wishlists with friends
- 📱 Access on any device

**Happy house hunting!** 🏠

---

**Questions?** Check the full documentation or contact support.

**Found a bug?** Open an issue with:
- Steps to reproduce
- Expected vs actual behavior
- Screenshots if applicable
- Browser/environment details
