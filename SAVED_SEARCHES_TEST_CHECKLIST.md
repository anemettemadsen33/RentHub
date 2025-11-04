# ✅ Saved Searches - Testing Checklist

## Backend API Tests

### ✅ Setup
- [x] Migration ran successfully
- [x] Model exists with all relationships
- [x] Controller created with 9 endpoints
- [x] Routes registered

### 🔄 CRUD Operations
- [ ] **Create**: POST `/api/v1/saved-searches`
  ```bash
  curl -X POST http://localhost/api/v1/saved-searches \
    -H "Authorization: Bearer TOKEN" \
    -H "Content-Type: application/json" \
    -d '{"name":"Test Search","min_price":100,"max_price":500,"enable_alerts":true}'
  ```
  Expected: 201 Created with saved search data

- [ ] **Read All**: GET `/api/v1/saved-searches`
  ```bash
  curl http://localhost/api/v1/saved-searches -H "Authorization: Bearer TOKEN"
  ```
  Expected: 200 OK with array of searches

- [ ] **Read One**: GET `/api/v1/saved-searches/1`
  ```bash
  curl http://localhost/api/v1/saved-searches/1 -H "Authorization: Bearer TOKEN"
  ```
  Expected: 200 OK with single search

- [ ] **Update**: PUT `/api/v1/saved-searches/1`
  ```bash
  curl -X PUT http://localhost/api/v1/saved-searches/1 \
    -H "Authorization: Bearer TOKEN" \
    -d '{"name":"Updated Name"}'
  ```
  Expected: 200 OK with updated data

- [ ] **Delete**: DELETE `/api/v1/saved-searches/1`
  ```bash
  curl -X DELETE http://localhost/api/v1/saved-searches/1 \
    -H "Authorization: Bearer TOKEN"
  ```
  Expected: 200 OK with success message

### 🔍 Search Operations
- [ ] **Execute Search**: POST `/api/v1/saved-searches/1/execute`
  - Should return matching properties
  - Should increment `search_count`
  - Should update `last_searched_at`

- [ ] **Check New Listings**: GET `/api/v1/saved-searches/1/new-listings`
  - Should return properties created after `last_alert_sent_at`
  - Should show count and since date

- [ ] **Toggle Alerts**: POST `/api/v1/saved-searches/1/toggle-alerts`
  - Should flip `enable_alerts` boolean
  - Should return updated search

- [ ] **Statistics**: GET `/api/v1/saved-searches/statistics`
  - Should return total_searches
  - Should return most_used list
  - Should return recent list

## 📧 Email Notifications

### Setup
- [ ] Configure `.env` mail settings:
  ```env
  MAIL_MAILER=smtp
  MAIL_HOST=smtp.mailtrap.io
  MAIL_PORT=2525
  MAIL_USERNAME=
  MAIL_PASSWORD=
  ```

### Test Email
- [ ] Run command manually:
  ```bash
  php artisan saved-searches:send-alerts
  ```
- [ ] Check email received (Mailtrap/MailHog)
- [ ] Verify email content:
  - [ ] Subject includes property count
  - [ ] Search name displayed
  - [ ] Up to 3 property previews shown
  - [ ] Action button works
  - [ ] Footer links present

### Test Notification Logic
- [ ] Create test saved search
- [ ] Create new property matching criteria
- [ ] Run alert command
- [ ] Verify email sent
- [ ] Check `last_alert_sent_at` updated
- [ ] Check `new_listings_count` updated

## 🎛️ Console Commands

- [ ] Command exists: `php artisan list | grep saved-searches`
- [ ] Help works: `php artisan saved-searches:send-alerts --help`
- [ ] Run instant alerts: `php artisan saved-searches:send-alerts --frequency=instant`
- [ ] Run daily alerts: `php artisan saved-searches:send-alerts --frequency=daily`
- [ ] Run weekly alerts: `php artisan saved-searches:send-alerts --frequency=weekly`
- [ ] Run all alerts: `php artisan saved-searches:send-alerts`
- [ ] Check output shows summary table

## ⏰ Scheduled Tasks

- [ ] Check schedule list: `php artisan schedule:list`
- [ ] Verify instant alerts scheduled hourly
- [ ] Verify daily alerts scheduled daily
- [ ] Verify weekly alerts scheduled weekly
- [ ] Add to crontab:
  ```bash
  * * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1
  ```

## 🎨 Filament Admin

- [ ] Access admin panel: `/admin/saved-searches`
- [ ] List page shows all searches
- [ ] Can view search details
- [ ] Can create new search
- [ ] Can edit existing search
- [ ] Can delete search
- [ ] Filters work
- [ ] Search works
- [ ] Bulk actions work (if implemented)

## 🎨 Frontend Components

### Setup
- [ ] Install dependencies: `npm install axios`
- [ ] Types file exists: `src/types/saved-search.ts`
- [ ] API service exists: `src/services/api/savedSearches.ts`
- [ ] Components exist: `src/components/SavedSearches/`

### SavedSearchCard Component
- [ ] Displays search name
- [ ] Shows location with icon
- [ ] Shows price range
- [ ] Shows bedrooms/bathrooms
- [ ] Shows alert frequency badge
- [ ] Shows new listings count (if any)
- [ ] "Search Now" button works
- [ ] Alert toggle button works
- [ ] Edit button works
- [ ] Delete button works (with confirmation)

### SavedSearchesList Component
- [ ] Loads searches on mount
- [ ] Shows loading spinner while loading
- [ ] Displays searches in grid layout
- [ ] "All Searches" filter works
- [ ] "Active Alerts" filter works
- [ ] Empty state shows when no searches
- [ ] "Create New Search" button works
- [ ] Execute search redirects to results
- [ ] Toggle alerts updates immediately
- [ ] Delete removes from list
- [ ] Edit navigates to edit page

### Page Integration
- [ ] Page accessible at `/saved-searches`
- [ ] Page layout renders correctly
- [ ] Mobile responsive
- [ ] Desktop layout (3 columns)
- [ ] Tablet layout (2 columns)
- [ ] Mobile layout (1 column)

## 🔗 Integration Tests

### Save Search from Results
- [ ] Add "Save Search" button to search results page
- [ ] Button opens modal/dialog
- [ ] User can name the search
- [ ] Current filters auto-populate
- [ ] Save creates saved search via API
- [ ] Success message shown
- [ ] User redirected to saved searches page

### Execute and View Results
- [ ] Click "Search Now" on saved search card
- [ ] Redirects to `/search?saved_search={id}`
- [ ] Search executes automatically
- [ ] Results displayed
- [ ] Filters populated from saved search
- [ ] Property count shown
- [ ] Message: "Showing results for {search_name}"

## 🐛 Error Handling

### API Errors
- [ ] 401 Unauthorized - redirects to login
- [ ] 404 Not Found - shows error message
- [ ] 422 Validation Error - shows validation messages
- [ ] 500 Server Error - shows generic error

### Frontend Errors
- [ ] Network error - shows retry option
- [ ] Invalid token - clears and redirects
- [ ] Empty results - shows empty state
- [ ] Loading states - shows spinners

## 🔒 Security Tests

- [ ] Cannot access another user's saved search (GET /api/v1/saved-searches/X)
- [ ] Cannot update another user's search (PUT)
- [ ] Cannot delete another user's search (DELETE)
- [ ] Cannot execute another user's search (POST execute)
- [ ] Unauthenticated requests return 401
- [ ] SQL injection prevented (test with malicious input)
- [ ] XSS prevented (test with script tags in name)

## 📊 Performance Tests

- [ ] List page loads in < 1 second
- [ ] Execute search completes in < 3 seconds
- [ ] Alert command processes 100+ searches in < 1 minute
- [ ] Email queue processes without blocking
- [ ] Database queries optimized (check query log)
- [ ] No N+1 queries

## 🌐 Browser Compatibility

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Mobile Chrome (Android)

## ✅ Final Checklist

- [ ] All API endpoints tested and working
- [ ] Email notifications sent successfully
- [ ] Frontend components render correctly
- [ ] Mobile responsive design
- [ ] Error handling implemented
- [ ] Security validated
- [ ] Performance acceptable
- [ ] Documentation complete
- [ ] Code reviewed
- [ ] Ready for production

---

## 🎯 Quick Test Script

```bash
# Backend
cd backend

# Test API (replace TOKEN with actual token)
TOKEN="your_auth_token_here"

# Create
curl -X POST http://localhost/api/v1/saved-searches \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","min_price":100,"enable_alerts":true}'

# List
curl http://localhost/api/v1/saved-searches -H "Authorization: Bearer $TOKEN"

# Execute
curl -X POST http://localhost/api/v1/saved-searches/1/execute \
  -H "Authorization: Bearer $TOKEN"

# Test alerts
php artisan saved-searches:send-alerts

# Check logs
tail -f storage/logs/laravel.log
```

---

## 📝 Notes

- Use Postman collection for easier API testing
- Test with real data for accurate results
- Monitor email queue: `php artisan queue:work`
- Check database directly for verification
- Use browser DevTools for frontend debugging

**Status**: Ready for comprehensive testing!
