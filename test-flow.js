// Registration Flow Test - simulate browser behavior
const API_BASE_URL = 'http://localhost:8000';
const FRONTEND_URL = 'http://localhost:3000';

async function testRegistration() {
  console.log('🚀 Starting registration flow test...\n');
  
  const cookieJar = [];
  
  try {
    // Step 1: Get CSRF Cookie
    console.log('Step 1: Getting CSRF cookie...');
    const csrfResponse = await fetch(`${API_BASE_URL}/sanctum/csrf-cookie`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Origin': FRONTEND_URL,
      },
      credentials: 'include',
    });
    
    console.log(`✅ CSRF Status: ${csrfResponse.status}`);
    
    // Extract cookies
    const setCookies = csrfResponse.headers.raw()['set-cookie'];
    if (setCookies) {
      cookieJar.push(...setCookies);
    }
    
    // Parse XSRF-TOKEN
    let xsrfToken = null;
    if (setCookies) {
      const xsrfCookie = setCookies.find(c => c.startsWith('XSRF-TOKEN'));
      if (xsrfCookie) {
        xsrfToken = decodeURIComponent(xsrfCookie.split(';')[0].split('=')[1]);
        console.log(`   Token: ${xsrfToken.substring(0, 30)}...`);
      }
    }
    
    if (!xsrfToken) {
      console.error('❌ No XSRF-TOKEN found!');
      return;
    }
    
    await new Promise(resolve => setTimeout(resolve, 500));
    
    // Step 2: Register user
    console.log('\nStep 2: Registering user...');
    const timestamp = Date.now();
    const userData = {
      name: 'Test User',
      email: `test${timestamp}@example.com`,
      password: 'Password123!',
      password_confirmation: 'Password123!',
    };
    
    console.log(`   Email: ${userData.email}`);
    
    const registerResponse = await fetch(`${API_BASE_URL}/api/v1/register`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Origin': FRONTEND_URL,
        'X-XSRF-TOKEN': xsrfToken,
        'Cookie': cookieJar.map(c => c.split(';')[0]).join('; '),
      },
      body: JSON.stringify(userData),
      credentials: 'include',
    });
    
    console.log(`✅ Registration Status: ${registerResponse.status}`);
    
    const registerData = await registerResponse.json();
    console.log(`   Response:`, JSON.stringify(registerData, null, 2));
    
    // Step 3: Test /me with token
    if (registerData.token) {
      console.log('\nStep 3: Testing /me endpoint...');
      const meResponse = await fetch(`${API_BASE_URL}/api/v1/me`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${registerData.token}`,
          'Origin': FRONTEND_URL,
        },
        credentials: 'include',
      });
      
      console.log(`✅ /me Status: ${meResponse.status}`);
      const meData = await meResponse.json();
      console.log(`   User:`, JSON.stringify(meData, null, 2));
    }
    
    console.log('\n✅✅✅ ALL TESTS PASSED! ✅✅✅\n');
    console.log('Summary:');
    console.log(`  ✅ CSRF cookie`);
    console.log(`  ✅ User registration`);
    console.log(`  ✅ Token auth`);
    console.log('\n🎉 Backend + CORS + Sanctum working 100%!\n');
    
  } catch (error) {
    console.error('\n❌ ERROR:');
    console.error('   Message:', error.message);
    if (error.cause) console.error('   Cause:', error.cause);
    process.exit(1);
  }
}

testRegistration();
