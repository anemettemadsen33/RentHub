# ==============================================================================
# RentHub - Browser Testing Guide
# Testing Frontend & Admin Panel Manually
# ==============================================================================

Write-Host "`n╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║        🌐 RENTHUB - BROWSER TESTING GUIDE 🌐                  ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

$frontendUrl = "http://localhost:3000"
$adminUrl = "http://127.0.0.1:8000/admin"
$backendUrl = "http://127.0.0.1:8000"

# Check if servers are running
Write-Host "🔍 Checking if servers are running...`n" -ForegroundColor Yellow

try {
    $response = Invoke-WebRequest -Uri "$backendUrl/api/health" -Method GET -TimeoutSec 2 -ErrorAction Stop
    Write-Host "✅ Backend Server: RUNNING on $backendUrl" -ForegroundColor Green
}
catch {
    Write-Host "❌ Backend Server: NOT RUNNING" -ForegroundColor Red
    Write-Host "   Please start backend: cd backend && php artisan serve`n" -ForegroundColor Yellow
}

try {
    $response = Invoke-WebRequest -Uri $frontendUrl -Method GET -TimeoutSec 2 -ErrorAction Stop
    Write-Host "✅ Frontend Server: RUNNING on $frontendUrl" -ForegroundColor Green
}
catch {
    Write-Host "❌ Frontend Server: NOT RUNNING" -ForegroundColor Red
    Write-Host "   Please start frontend: cd frontend && npm run dev`n" -ForegroundColor Yellow
}

Write-Host "`n═══════════════════════════════════════════════════════════════" -ForegroundColor Gray

# Display test credentials
Write-Host "`n📝 TEST CREDENTIALS:" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

Write-Host "👤 Regular User Account:" -ForegroundColor White
Write-Host "   Email: test@renthub.com" -ForegroundColor Yellow
Write-Host "   Password: Password123!`n" -ForegroundColor Yellow

Write-Host "👨‍💼 Admin Account:" -ForegroundColor White
Write-Host "   Email: admin@renthub.com" -ForegroundColor Yellow
Write-Host "   Password: admin123`n" -ForegroundColor Yellow

Write-Host "🏠 Property Owner Account:" -ForegroundColor White
Write-Host "   Email: owner@renthub.com" -ForegroundColor Yellow
Write-Host "   Password: Password123!`n" -ForegroundColor Yellow

# Display testing checklist
Write-Host "═══════════════════════════════════════════════════════════════" -ForegroundColor Gray
Write-Host "`n✅ FRONTEND TESTING CHECKLIST:" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

$frontendTests = @(
    "1️⃣  Homepage",
    "    □ Hero section loads",
    "    □ Featured properties display",
    "    □ Search bar functional",
    "    □ Navigation menu works",
    "",
    "2️⃣  Property Listing Page (/properties)",
    "    □ Properties grid/list view",
    "    □ Filters (price, location, bedrooms, etc.)",
    "    □ Sorting options",
    "    □ Pagination",
    "    □ Property cards clickable",
    "",
    "3️⃣  Property Details Page",
    "    □ Image gallery/carousel",
    "    □ Property information",
    "    □ Amenities list",
    "    □ Location map",
    "    □ Reviews section",
    "    □ Booking form",
    "    □ Contact owner button",
    "",
    "4️⃣  User Registration (/auth/register)",
    "    □ Form validation",
    "    □ Email format check",
    "    □ Password strength",
    "    □ Role selection",
    "    □ Success redirect",
    "",
    "5️⃣  User Login (/auth/login)",
    "    □ Login form",
    "    □ Remember me checkbox",
    "    □ Forgot password link",
    "    □ Token storage",
    "    □ Redirect to dashboard",
    "",
    "6️⃣  Booking Flow",
    "    □ Date picker works",
    "    □ Guest selection",
    "    □ Price calculation",
    "    □ Availability check",
    "    □ Payment options",
    "    □ Booking confirmation",
    "",
    "7️⃣  User Dashboard (/dashboard)",
    "    □ Statistics cards",
    "    □ Recent bookings",
    "    □ Upcoming trips",
    "    □ Notifications",
    "    □ Quick actions",
    "",
    "8️⃣  Profile Page (/profile)",
    "    □ View profile info",
    "    □ Edit profile button",
    "    □ Avatar upload",
    "    □ Phone number",
    "    □ Bio/description",
    "    □ Verification status",
    "",
    "9️⃣  KYC Verification (/verification)",
    "    □ ID upload form",
    "    □ Phone verification",
    "    □ Address proof upload",
    "    □ Progress indicator",
    "    □ Status badges",
    "",
    "🔟 My Bookings Page",
    "    □ Booking list",
    "    □ Filter by status",
    "    □ Booking details",
    "    □ Cancel booking",
    "    □ Leave review",
    "",
    "1️⃣1️⃣ Messages/Chat",
    "    □ Conversation list",
    "    □ Message thread",
    "    □ Send message",
    "    □ Unread count",
    "",
    "1️⃣2️⃣ Settings Page",
    "    □ Language selector",
    "    □ Currency selector",
    "    □ Notification preferences",
    "    □ Privacy settings",
    "    □ Save button works",
    "",
    "1️⃣3️⃣ Search & Filters",
    "    □ Location search",
    "    □ Date range picker",
    "    □ Price range slider",
    "    □ Amenities checkboxes",
    "    □ Property type filter",
    "    □ Apply filters button",
    "",
    "1️⃣4️⃣ Favorites/Wishlist",
    "    □ Add to favorites (heart icon)",
    "    □ View favorites page",
    "    □ Remove from favorites",
    "",
    "1️⃣5️⃣ Reviews & Ratings",
    "    □ View reviews",
    "    □ Star rating display",
    "    □ Write review form",
    "    □ Submit review"
)

foreach ($test in $frontendTests) {
    Write-Host $test -ForegroundColor White
}

Write-Host "`n═══════════════════════════════════════════════════════════════" -ForegroundColor Gray
Write-Host "`n🔧 ADMIN PANEL TESTING CHECKLIST:" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

$adminTests = @(
    "1️⃣  Admin Login",
    "    □ Access $adminUrl",
    "    □ Login with admin credentials",
    "    □ Dashboard loads",
    "",
    "2️⃣  Users Management",
    "    □ View users list",
    "    □ Search users",
    "    □ Filter by role",
    "    □ View user details",
    "    □ Edit user",
    "    □ Delete user",
    "    □ Create new user",
    "",
    "3️⃣  Properties Management",
    "    □ View properties list",
    "    □ Create new property",
    "    □ Edit property",
    "    □ Upload images",
    "    □ Manage amenities",
    "    □ Set pricing",
    "    □ Delete property",
    "",
    "4️⃣  Bookings Management",
    "    □ View all bookings",
    "    □ Filter by status",
    "    □ View booking details",
    "    □ Update booking status",
    "    □ Refund booking",
    "",
    "5️⃣  Verification Management",
    "    □ View pending verifications",
    "    □ Review ID documents",
    "    □ Approve/reject ID",
    "    □ Review address proof",
    "    □ Background check status",
    "",
    "6️⃣  Amenities Management",
    "    □ View amenities list",
    "    □ Create amenity",
    "    □ Edit amenity",
    "    □ Upload icon",
    "    □ Delete amenity",
    "",
    "7️⃣  Reviews Management",
    "    □ View all reviews",
    "    □ Moderate reviews",
    "    □ Delete inappropriate reviews",
    "",
    "8️⃣  Settings Page",
    "    □ General settings",
    "    □ Email configuration",
    "    □ Payment settings",
    "    □ API keys",
    "    □ Test email button",
    "",
    "9️⃣  Reports & Analytics",
    "    □ Revenue reports",
    "    □ Booking statistics",
    "    □ User growth charts",
    "    □ Export data",
    "",
    "🔟 Maintenance Requests",
    "    □ View requests",
    "    □ Assign to staff",
    "    □ Update status",
    "    □ Close request"
)

foreach ($test in $adminTests) {
    Write-Host $test -ForegroundColor White
}

Write-Host "`n═══════════════════════════════════════════════════════════════" -ForegroundColor Gray
Write-Host "`n🔍 BROWSER CONSOLE CHECKS:" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

Write-Host "Press F12 to open Developer Tools, then check:" -ForegroundColor White
Write-Host ""
Write-Host "📌 Console Tab:" -ForegroundColor Yellow
Write-Host "   □ No JavaScript errors (red messages)" -ForegroundColor White
Write-Host "   □ No failed API requests (404, 500 errors)" -ForegroundColor White
Write-Host "   □ No CORS errors" -ForegroundColor White
Write-Host "   □ No deprecated warnings" -ForegroundColor White
Write-Host ""
Write-Host "📌 Network Tab:" -ForegroundColor Yellow
Write-Host "   □ All API calls return 200/201" -ForegroundColor White
Write-Host "   □ No failed requests (red color)" -ForegroundColor White
Write-Host "   □ Response times are reasonable (<1s)" -ForegroundColor White
Write-Host "   □ Images load correctly" -ForegroundColor White
Write-Host ""
Write-Host "📌 Application Tab:" -ForegroundColor Yellow
Write-Host "   □ localStorage has auth_token" -ForegroundColor White
Write-Host "   □ localStorage has user data" -ForegroundColor White
Write-Host "   □ Session storage correct" -ForegroundColor White

Write-Host "`n═══════════════════════════════════════════════════════════════" -ForegroundColor Gray
Write-Host "`n🚀 OPENING BROWSERS..." -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

# Open browsers
Write-Host "Opening Frontend..." -ForegroundColor Yellow
Start-Process $frontendUrl

Start-Sleep -Seconds 2

Write-Host "Opening Admin Panel..." -ForegroundColor Yellow
Start-Process $adminUrl

Start-Sleep -Seconds 2

Write-Host "`n✅ Browsers opened!" -ForegroundColor Green
Write-Host "`n📋 Follow the checklists above and test each feature manually." -ForegroundColor Cyan
Write-Host "📝 Note any issues, errors, or broken functionality.`n" -ForegroundColor Yellow

Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray
