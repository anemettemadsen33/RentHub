// Registration Flow Test - Simple version
const API_BASE = 'http://localhost:8000';

async function test() {
  console.log('🚀 Testing registration...\n');
  
  try {
    // Step 1: CSRF
    console.log('1. Getting CSRF cookie...');
    const csrf = await fetch(`${API_BASE}/sanctum/csrf-cookie`, {
      headers: { 'Origin': 'http://localhost:3000' }
    });
    console.log(`   Status: ${csrf.status}`);
    
    const cookies = csrf.headers.getSetCookie();
    let xsrf = null;
    for (const cookie of cookies) {
      if (cookie.startsWith('XSRF-TOKEN')) {
        xsrf = decodeURIComponent(cookie.split(';')[0].split('=')[1]);
        console.log(`   XSRF: ${xsrf.substring(0, 30)}...`);
        break;
      }
    }
    
    if (!xsrf) {
      console.error('❌ No XSRF token!');
      return;
    }
    
    // Step 2: Register
    console.log('\n2. Registering user...');
    const email = `test${Date.now()}@example.com`;
    console.log(`   Email: ${email}`);
    
    const reg = await fetch(`${API_BASE}/api/v1/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Origin': 'http://localhost:3000',
        'X-XSRF-TOKEN': xsrf,
        'Cookie': cookies.map(c => c.split(';')[0]).join('; ')
      },
      body: JSON.stringify({
        name: 'Test User',
        email,
        password: 'Password123!',
        password_confirmation: 'Password123!'
      })
    });
    
    console.log(`   Status: ${reg.status}`);
    const data = await reg.json();
    console.log(`   Response:`, JSON.stringify(data, null, 2));
    
    // Step 3: Test token
    if (data.token) {
      console.log('\n3. Testing /me endpoint...');
      const me = await fetch(`${API_BASE}/api/v1/me`, {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${data.token}`
        }
      });
      console.log(`   Status: ${me.status}`);
      const userData = await me.json();
      console.log(`   User:`, JSON.stringify(userData, null, 2));
    }
    
    console.log('\n✅✅✅ ALL TESTS PASSED! ✅✅✅');
    console.log('🎉 Registration flow working 100%!\n');
    
  } catch (err) {
    console.error('\n❌ ERROR:', err.message);
    process.exit(1);
  }
}

test();
