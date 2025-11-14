@echo off
echo 🔧 RentHub Local Development Testing
echo ======================================

echo.
echo Testing Frontend...
curl -s -o nul -w "%%{http_code}" http://localhost:3000 > temp_frontend.txt
set /p frontend_status=<temp_frontend.txt
del temp_frontend.txt

if "%frontend_status%"=="200" (
    echo ✅ Frontend running on http://localhost:3000
) else (
    echo ❌ Frontend not responding (Status: %frontend_status%)
)

echo.
echo Testing Backend API...
curl -s -o temp_backend.txt -w "%%{http_code}" http://127.0.0.1:8000/api/health
set /p backend_status=<temp_backend.txt

if "%backend_status%"=="200" (
    echo ✅ Backend API running on http://127.0.0.1:8000
    echo   Response: 
    type temp_backend.txt
) else (
    echo ❌ Backend API not responding (Status: %backend_status%)
)
del temp_backend.txt

echo.
echo Testing API Endpoints...
curl -s -o nul -w "%%{http_code}" http://127.0.0.1:8000/api/v1/settings/public > temp_settings.txt
set /p settings_status=<temp_settings.txt
del temp_settings.txt

if "%settings_status%"=="200" (
    echo ✅ Public Settings API working
) else (
    echo ⚠️  Public Settings API status: %settings_status%
)

echo.
echo Testing TypeScript...
cd frontend
npm run type-check > ../temp_typescript.txt 2>&1
set typescript_result=%errorlevel%
cd ..

if "%typescript_result%"=="0" (
    echo ✅ TypeScript compilation successful
) else (
    echo ❌ TypeScript errors found
    type temp_typescript.txt
del temp_typescript.txt
)

echo.
echo 🎯 Testing Complete!
echo.
echo Next Steps:
echo - Review any failed tests above
echo - Fix identified issues
echo - Run staging tests when ready
echo - Deploy to production after validation

pause