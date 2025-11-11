# ==============================================================================
# RentHub - KYC Verification Complete Test
# ==============================================================================

Write-Host "`n╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║          🔐 RENTHUB - KYC VERIFICATION TEST 🔐                ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

$backendUrl = "http://127.0.0.1:8000/api/v1"
$testResults = @()
$token = ""
$userId = 0

# Helper function to test endpoint
function Test-Endpoint {
    param(
        [string]$Name,
        [string]$Url,
        [string]$Method = "GET",
        [hashtable]$Headers = @{},
        [object]$Body = $null,
        [string]$ContentType = "application/json"
    )
    
    Write-Host "Testing: $Name..." -ForegroundColor Yellow
    
    try {
        $params = @{
            Uri = $Url
            Method = $Method
            Headers = $Headers
            ErrorAction = "Stop"
        }
        
        if ($Body) {
            if ($ContentType -eq "application/json") {
                $params.Body = ($Body | ConvertTo-Json -Depth 10)
                $params.ContentType = $ContentType
            } else {
                $params.Body = $Body
                $params.ContentType = $ContentType
            }
        }
        
        $response = Invoke-RestMethod @params
        Write-Host "✅ PASSED: $Name" -ForegroundColor Green
        return @{ Success = $true; Response = $response; Name = $Name }
    }
    catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        $errorMessage = $_.Exception.Message
        Write-Host "❌ FAILED: $Name - $errorMessage (Status: $statusCode)" -ForegroundColor Red
        return @{ Success = $false; Error = $errorMessage; Name = $Name; StatusCode = $statusCode }
    }
}

# ==============================================================================
# STEP 1: LOGIN & GET TOKEN
# ==============================================================================
Write-Host "`n📋 STEP 1: User Authentication" -ForegroundColor Magenta
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

# Register a new test user for KYC
$timestamp = Get-Date -Format "yyyyMMddHHmmss"
$testEmail = "kyc_test_$timestamp@renthub.test"

$registerResult = Test-Endpoint `
    -Name "Register KYC Test User" `
    -Url "$backendUrl/register" `
    -Method "POST" `
    -Body @{
        name = "KYC Test User"
        email = $testEmail
        password = "Password123!"
        password_confirmation = "Password123!"
        role = "tenant"
    }

if ($registerResult.Success) {
    $token = $registerResult.Response.token
    $userId = $registerResult.Response.user.id
    $headers = @{
        "Authorization" = "Bearer $token"
        "Accept" = "application/json"
    }
    Write-Host "✅ User registered: $testEmail (ID: $userId)" -ForegroundColor Green
    Write-Host "🔑 Token: $($token.Substring(0, 20))..." -ForegroundColor Cyan
} else {
    Write-Host "❌ Registration failed - cannot proceed with tests" -ForegroundColor Red
    exit 1
}

Start-Sleep -Seconds 1

# ==============================================================================
# STEP 2: GET INITIAL VERIFICATION STATUS
# ==============================================================================
Write-Host "`n📋 STEP 2: Check Initial Verification Status" -ForegroundColor Magenta
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

$statusResult = Test-Endpoint `
    -Name "Get My Verification Status" `
    -Url "$backendUrl/my-verification" `
    -Headers $headers

if ($statusResult.Success) {
    Write-Host "`n📊 Initial Verification Status:" -ForegroundColor Cyan
    Write-Host "   ID Verification: $($statusResult.Response.id_verification_status ?? 'pending')" -ForegroundColor Yellow
    Write-Host "   Phone Verification: $($statusResult.Response.phone_verification_status ?? 'pending')" -ForegroundColor Yellow
    Write-Host "   Address Verification: $($statusResult.Response.address_verification_status ?? 'pending')" -ForegroundColor Yellow
    Write-Host "   Background Check: $($statusResult.Response.background_check_status ?? 'not_requested')" -ForegroundColor Yellow
}

# Test alternative endpoint
$statusResult2 = Test-Endpoint `
    -Name "Get Verification Status (Alternative)" `
    -Url "$backendUrl/verification-status" `
    -Headers $headers

Start-Sleep -Seconds 1

# ==============================================================================
# STEP 3: ID VERIFICATION - UPLOAD DOCUMENTS
# ==============================================================================
Write-Host "`n📋 STEP 3: ID Verification - Document Upload" -ForegroundColor Magenta
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

# Create test image files (1x1 pixel PNG)
$testImageBase64 = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=="
$testImageBytes = [Convert]::FromBase64String($testImageBase64)

$idFrontPath = "$env:TEMP\id_front_$timestamp.png"
$idBackPath = "$env:TEMP\id_back_$timestamp.png"
$selfiePath = "$env:TEMP\selfie_$timestamp.png"

[System.IO.File]::WriteAllBytes($idFrontPath, $testImageBytes)
[System.IO.File]::WriteAllBytes($idBackPath, $testImageBytes)
[System.IO.File]::WriteAllBytes($selfiePath, $testImageBytes)

Write-Host "📁 Created test files:" -ForegroundColor Cyan
Write-Host "   - ID Front: $idFrontPath" -ForegroundColor Gray
Write-Host "   - ID Back: $idBackPath" -ForegroundColor Gray
Write-Host "   - Selfie: $selfiePath`n" -ForegroundColor Gray

# Test ID upload with multipart/form-data
try {
    Write-Host "Testing: Upload ID Documents..." -ForegroundColor Yellow
    
    $boundary = [System.Guid]::NewGuid().ToString()
    $LF = "`r`n"
    
    $bodyLines = @()
    
    # Add id_document_type
    $bodyLines += "--$boundary"
    $bodyLines += "Content-Disposition: form-data; name=`"id_document_type`""
    $bodyLines += ""
    $bodyLines += "passport"
    
    # Add id_document_number
    $bodyLines += "--$boundary"
    $bodyLines += "Content-Disposition: form-data; name=`"id_document_number`""
    $bodyLines += ""
    $bodyLines += "AB123456"
    
    # Add id_front_image
    $bodyLines += "--$boundary"
    $bodyLines += "Content-Disposition: form-data; name=`"id_front_image`"; filename=`"id_front.png`""
    $bodyLines += "Content-Type: image/png"
    $bodyLines += ""
    
    $bodyString = $bodyLines -join $LF
    $bodyString += $LF
    
    $bodyBytes = [System.Text.Encoding]::UTF8.GetBytes($bodyString)
    $bodyBytes += $testImageBytes
    $bodyBytes += [System.Text.Encoding]::UTF8.GetBytes($LF)
    
    # Add id_back_image
    $bodyBytes += [System.Text.Encoding]::UTF8.GetBytes("--$boundary$LF")
    $bodyBytes += [System.Text.Encoding]::UTF8.GetBytes("Content-Disposition: form-data; name=`"id_back_image`"; filename=`"id_back.png`"$LF")
    $bodyBytes += [System.Text.Encoding]::UTF8.GetBytes("Content-Type: image/png$LF$LF")
    $bodyBytes += $testImageBytes
    $bodyBytes += [System.Text.Encoding]::UTF8.GetBytes($LF)
    
    # Add selfie_image
    $bodyBytes += [System.Text.Encoding]::UTF8.GetBytes("--$boundary$LF")
    $bodyBytes += [System.Text.Encoding]::UTF8.GetBytes("Content-Disposition: form-data; name=`"selfie_image`"; filename=`"selfie.png`"$LF")
    $bodyBytes += [System.Text.Encoding]::UTF8.GetBytes("Content-Type: image/png$LF$LF")
    $bodyBytes += $testImageBytes
    $bodyBytes += [System.Text.Encoding]::UTF8.GetBytes($LF)
    
    # Closing boundary
    $bodyBytes += [System.Text.Encoding]::UTF8.GetBytes("--$boundary--$LF")
    
    $response = Invoke-RestMethod `
        -Uri "$backendUrl/user-verifications/id" `
        -Method POST `
        -Headers @{
            "Authorization" = "Bearer $token"
            "Accept" = "application/json"
        } `
        -ContentType "multipart/form-data; boundary=$boundary" `
        -Body $bodyBytes
    
    Write-Host "✅ PASSED: Upload ID Documents" -ForegroundColor Green
    Write-Host "   Document Type: passport" -ForegroundColor Gray
    Write-Host "   Document Number: AB123456" -ForegroundColor Gray
    Write-Host "   Status: $($response.data.id_verification_status ?? $response.id_verification_status ?? 'submitted')" -ForegroundColor Cyan
}
catch {
    Write-Host "❌ FAILED: Upload ID Documents - $($_.Exception.Message)" -ForegroundColor Red
}

# Cleanup temp files
Remove-Item $idFrontPath, $idBackPath, $selfiePath -ErrorAction SilentlyContinue

Start-Sleep -Seconds 1

# ==============================================================================
# STEP 4: PHONE VERIFICATION
# ==============================================================================
Write-Host "`n📋 STEP 4: Phone Verification" -ForegroundColor Magenta
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

$phoneResult = Test-Endpoint `
    -Name "Send Phone Verification Code" `
    -Url "$backendUrl/user-verifications/phone/send" `
    -Method "POST" `
    -Headers $headers `
    -Body @{
        phone_number = "+1234567890"
    }

if ($phoneResult.Success) {
    Write-Host "📱 Phone verification code sent to: +1234567890" -ForegroundColor Cyan
    
    # Get the verification code from database or response
    if ($phoneResult.Response.verification_code) {
        $verificationCode = $phoneResult.Response.verification_code
        Write-Host "🔐 Verification Code: $verificationCode" -ForegroundColor Yellow
        
        Start-Sleep -Seconds 2
        
        # Verify the code
        $verifyResult = Test-Endpoint `
            -Name "Verify Phone Code" `
            -Url "$backendUrl/user-verifications/phone/verify" `
            -Method "POST" `
            -Headers $headers `
            -Body @{
                code = $verificationCode
            }
    } else {
        Write-Host "⚠️  Note: In production, code would be sent via SMS" -ForegroundColor Yellow
    }
}

Start-Sleep -Seconds 1

# ==============================================================================
# STEP 5: ADDRESS VERIFICATION
# ==============================================================================
Write-Host "`n📋 STEP 5: Address Verification" -ForegroundColor Magenta
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

# Create test address proof document
$addressProofPath = "$env:TEMP\address_proof_$timestamp.png"
[System.IO.File]::WriteAllBytes($addressProofPath, $testImageBytes)

try {
    Write-Host "Testing: Upload Address Proof..." -ForegroundColor Yellow
    
    $boundary = [System.Guid]::NewGuid().ToString()
    $LF = "`r`n"
    
    $bodyLines = @()
    
    # Add address
    $bodyLines += "--$boundary"
    $bodyLines += "Content-Disposition: form-data; name=`"address`""
    $bodyLines += ""
    $bodyLines += "123 Test Street, Test City, TS 12345"
    
    # Add address_proof_document
    $bodyLines += "--$boundary"
    $bodyLines += "Content-Disposition: form-data; name=`"address_proof_document`""
    $bodyLines += ""
    $bodyLines += "utility_bill"
    
    # Add address_proof_image
    $bodyLines += "--$boundary"
    $bodyLines += "Content-Disposition: form-data; name=`"address_proof_image`"; filename=`"address_proof.png`""
    $bodyLines += "Content-Type: image/png"
    $bodyLines += ""
    
    $bodyString = $bodyLines -join $LF
    $bodyString += $LF
    
    $bodyBytes = [System.Text.Encoding]::UTF8.GetBytes($bodyString)
    $bodyBytes += $testImageBytes
    $bodyBytes += [System.Text.Encoding]::UTF8.GetBytes($LF)
    $bodyBytes += [System.Text.Encoding]::UTF8.GetBytes("--$boundary--$LF")
    
    $response = Invoke-RestMethod `
        -Uri "$backendUrl/user-verifications/address" `
        -Method POST `
        -Headers @{
            "Authorization" = "Bearer $token"
            "Accept" = "application/json"
        } `
        -ContentType "multipart/form-data; boundary=$boundary" `
        -Body $bodyBytes
    
    Write-Host "✅ PASSED: Upload Address Proof" -ForegroundColor Green
    Write-Host "   Address: 123 Test Street, Test City, TS 12345" -ForegroundColor Gray
    Write-Host "   Document Type: utility_bill" -ForegroundColor Gray
}
catch {
    Write-Host "❌ FAILED: Upload Address Proof - $($_.Exception.Message)" -ForegroundColor Red
}

Remove-Item $addressProofPath -ErrorAction SilentlyContinue

Start-Sleep -Seconds 1

# ==============================================================================
# STEP 6: BACKGROUND CHECK REQUEST
# ==============================================================================
Write-Host "`n📋 STEP 6: Background Check Request" -ForegroundColor Magenta
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

$backgroundCheckResult = Test-Endpoint `
    -Name "Request Background Check" `
    -Url "$backendUrl/user-verifications/background-check" `
    -Method "POST" `
    -Headers $headers

if ($backgroundCheckResult.Success) {
    Write-Host "🔍 Background check requested successfully" -ForegroundColor Cyan
}

Start-Sleep -Seconds 1

# ==============================================================================
# STEP 7: FINAL VERIFICATION STATUS
# ==============================================================================
Write-Host "`n📋 STEP 7: Final Verification Status Check" -ForegroundColor Magenta
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

$finalStatusResult = Test-Endpoint `
    -Name "Get Updated Verification Status" `
    -Url "$backendUrl/my-verification" `
    -Headers $headers

if ($finalStatusResult.Success) {
    $verification = $finalStatusResult.Response
    
    Write-Host "`n📊 FINAL VERIFICATION STATUS:" -ForegroundColor Cyan
    Write-Host "═══════════════════════════════════════════════════════════════" -ForegroundColor Gray
    Write-Host "   👤 User ID: $userId" -ForegroundColor White
    Write-Host "   📧 Email: $testEmail" -ForegroundColor White
    Write-Host ""
    Write-Host "   🆔 ID Verification: $($verification.id_verification_status ?? 'pending')" -ForegroundColor $(if ($verification.id_verification_status -eq 'verified') { 'Green' } else { 'Yellow' })
    Write-Host "   📱 Phone Verification: $($verification.phone_verification_status ?? 'pending')" -ForegroundColor $(if ($verification.phone_verification_status -eq 'verified') { 'Green' } else { 'Yellow' })
    Write-Host "   🏠 Address Verification: $($verification.address_verification_status ?? 'pending')" -ForegroundColor $(if ($verification.address_verification_status -eq 'verified') { 'Green' } else { 'Yellow' })
    Write-Host "   🔍 Background Check: $($verification.background_check_status ?? 'not_requested')" -ForegroundColor $(if ($verification.background_check_status -eq 'completed') { 'Green' } else { 'Yellow' })
    Write-Host "   📈 Overall Status: $($verification.overall_status ?? 'incomplete')" -ForegroundColor $(if ($verification.overall_status -eq 'verified') { 'Green' } else { 'Yellow' })
    Write-Host "   ⭐ Verification Score: $($verification.verification_score ?? '0')/100" -ForegroundColor Cyan
    Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray
}

# ==============================================================================
# STEP 8: GET VERIFICATION STATISTICS (ADMIN ENDPOINT)
# ==============================================================================
Write-Host "`n📋 STEP 8: Verification Statistics" -ForegroundColor Magenta
Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray

$statsResult = Test-Endpoint `
    -Name "Get Verification Statistics" `
    -Url "$backendUrl/user-verifications/statistics" `
    -Headers $headers

if ($statsResult.Success) {
    Write-Host "📊 Verification Statistics Retrieved" -ForegroundColor Green
}

# ==============================================================================
# SUMMARY
# ==============================================================================
Write-Host "`n╔═══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                  📊 TEST SUMMARY                              ║" -ForegroundColor Cyan
Write-Host "╚═══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Cyan

Write-Host "✅ KYC Verification Flow Tested:" -ForegroundColor Green
Write-Host "   1. User Registration & Authentication" -ForegroundColor White
Write-Host "   2. Initial Verification Status Check" -ForegroundColor White
Write-Host "   3. ID Document Upload (Front/Back/Selfie)" -ForegroundColor White
Write-Host "   4. Phone Number Verification" -ForegroundColor White
Write-Host "   5. Address Proof Upload" -ForegroundColor White
Write-Host "   6. Background Check Request" -ForegroundColor White
Write-Host "   7. Final Status Verification" -ForegroundColor White
Write-Host "   8. Statistics Retrieval`n" -ForegroundColor White

Write-Host "📝 Test User Credentials:" -ForegroundColor Cyan
Write-Host "   Email: $testEmail" -ForegroundColor Yellow
Write-Host "   Password: Password123!" -ForegroundColor Yellow
Write-Host "   User ID: $userId`n" -ForegroundColor Yellow

Write-Host "🌐 Next Steps:" -ForegroundColor Magenta
Write-Host "   1. Login to admin panel: http://127.0.0.1:8000/admin" -ForegroundColor White
Write-Host "   2. Navigate to User Verifications" -ForegroundColor White
Write-Host "   3. Review and approve/reject documents" -ForegroundColor White
Write-Host "   4. Test verification in frontend: http://localhost:3000/verification`n" -ForegroundColor White

Write-Host "═══════════════════════════════════════════════════════════════`n" -ForegroundColor Gray
