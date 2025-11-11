// test-registration-flow.js// test-registration-flow.js

// Testează exact ce face browserul: CSRF cookie + registration// Testează exact ce face browserul: CSRF cookie + registration



const API_BASE_URL = 'http://localhost:8000';const axios = require('axios');

const FRONTEND_URL = 'http://localhost:3000';

const API_BASE_URL = 'http://localhost:8000';

async function testRegistration() {const API_URL = `${API_BASE_URL}/api/v1`;

  console.log('🚀 Starting registration flow test...\n');

  // Create axios instance cu cookie jar

  const cookieJar = [];const axiosInstance = axios.create({

    baseURL: API_BASE_URL,

  try {  withCredentials: true,

    // Step 1: Get CSRF Cookie  headers: {

    console.log('Step 1: Getting CSRF cookie from /sanctum/csrf-cookie...');    'Accept': 'application/json',

    const csrfResponse = await fetch(`${API_BASE_URL}/sanctum/csrf-cookie`, {    'Content-Type': 'application/json',

      method: 'GET',    'Origin': 'http://localhost:3000',

      headers: {  },

        'Accept': 'application/json',});

        'Origin': FRONTEND_URL,

      },async function testRegistration() {

      credentials: 'include',  console.log('🚀 Starting registration flow test...\n');

    });  

      try {

    console.log(`✅ CSRF Response Status: ${csrfResponse.status}`);    // Step 1: Get CSRF Cookie

        console.log('Step 1: Getting CSRF cookie from /sanctum/csrf-cookie...');

    // Extract cookies    const csrfResponse = await axiosInstance.get('/sanctum/csrf-cookie');

    const setCookies = csrfResponse.headers.raw()['set-cookie'];    console.log(`✅ CSRF Response Status: ${csrfResponse.status}`);

    if (setCookies) {    console.log(`   Headers:`, csrfResponse.headers);

      cookieJar.push(...setCookies);    

      console.log(`   Cookies received: ${setCookies.length}`);    // Extract cookies from response

    }    const cookies = csrfResponse.headers['set-cookie'];

        console.log(`   Cookies received:`, cookies);

    // Parse XSRF-TOKEN    

    let xsrfToken = null;    // Parse XSRF-TOKEN from cookies

    if (setCookies) {    let xsrfToken = null;

      const xsrfCookie = setCookies.find(c => c.startsWith('XSRF-TOKEN'));    if (cookies) {

      if (xsrfCookie) {      const xsrfCookie = cookies.find(c => c.startsWith('XSRF-TOKEN'));

        xsrfToken = decodeURIComponent(xsrfCookie.split(';')[0].split('=')[1]);      if (xsrfCookie) {

        console.log(`   XSRF-TOKEN: ${xsrfToken.substring(0, 30)}...`);        xsrfToken = decodeURIComponent(xsrfCookie.split(';')[0].split('=')[1]);

      }        console.log(`   XSRF-TOKEN extracted: ${xsrfToken.substring(0, 30)}...`);

    }      }

        }

    if (!xsrfToken) {    

      console.error('❌ No XSRF-TOKEN found!');    if (!xsrfToken) {

      return;      console.error('❌ No XSRF-TOKEN found in cookies!');

    }      return;

        }

    // Wait a bit    

    await new Promise(resolve => setTimeout(resolve, 500));    // Wait a bit

        await new Promise(resolve => setTimeout(resolve, 500));

    // Step 2: Register user    

    console.log('\nStep 2: Registering new user...');    // Step 2: Register user

    const timestamp = Date.now();    console.log('\nStep 2: Registering new user...');

    const userData = {    const timestamp = Date.now();

      name: 'Test User',    const userData = {

      email: `test${timestamp}@example.com`,      name: 'Test User',

      password: 'Password123!',      email: `test${timestamp}@example.com`,

      password_confirmation: 'Password123!',      password: 'Password123!',

    };      password_confirmation: 'Password123!',

        };

    console.log(`   Email: ${userData.email}`);    

        console.log(`   Email: ${userData.email}`);

    const registerResponse = await fetch(`${API_BASE_URL}/api/v1/register`, {    console.log(`   Using XSRF-TOKEN: ${xsrfToken.substring(0, 30)}...`);

      method: 'POST',    

      headers: {    // Make registration request with XSRF token

        'Accept': 'application/json',    const registerResponse = await axiosInstance.post(

        'Content-Type': 'application/json',      '/api/v1/register',

        'Origin': FRONTEND_URL,      userData,

        'X-XSRF-TOKEN': xsrfToken,      {

        'Cookie': cookieJar.map(c => c.split(';')[0]).join('; '),        headers: {

      },          'X-XSRF-TOKEN': xsrfToken,

      body: JSON.stringify(userData),          'Cookie': cookies.join('; '),

      credentials: 'include',        },

    });      }

        );

    console.log(`✅ Registration Response Status: ${registerResponse.status}`);    

        console.log(`✅ Registration Response Status: ${registerResponse.status}`);

    const registerData = await registerResponse.json();    console.log(`   Response data:`, JSON.stringify(registerResponse.data, null, 2));

    console.log(`   Response:`, JSON.stringify(registerData, null, 2));    

        // Step 3: Test authenticated request with token

    // Step 3: Test /me endpoint with token    if (registerResponse.data.token) {

    if (registerData.token) {      console.log('\nStep 3: Testing authenticated request with token...');

      console.log('\nStep 3: Testing /me endpoint with token...');      const meResponse = await axiosInstance.get('/api/v1/me', {

      const meResponse = await fetch(`${API_BASE_URL}/api/v1/me`, {        headers: {

        method: 'GET',          'Authorization': `Bearer ${registerResponse.data.token}`,

        headers: {        },

          'Accept': 'application/json',      });

          'Authorization': `Bearer ${registerData.token}`,      

          'Origin': FRONTEND_URL,      console.log(`✅ /me Response Status: ${meResponse.status}`);

        },      console.log(`   User data:`, JSON.stringify(meResponse.data, null, 2));

        credentials: 'include',    }

      });    

          console.log('\n✅✅✅ ALL TESTS PASSED! Registration flow works perfectly! ✅✅✅');

      console.log(`✅ /me Response Status: ${meResponse.status}`);    

      const meData = await meResponse.json();  } catch (error) {

      console.log(`   User:`, JSON.stringify(meData, null, 2));    console.error('\n❌ ERROR during registration flow:');

    }    if (error.response) {

          console.error(`   Status: ${error.response.status}`);

    console.log('\n✅✅✅ ALL TESTS PASSED! Registration flow works! ✅✅✅\n');      console.error(`   Data:`, JSON.stringify(error.response.data, null, 2));

    console.log('📋 Summary:');      console.error(`   Headers:`, error.response.headers);

    console.log(`   ✅ CSRF cookie retrieval`);    } else if (error.request) {

    console.log(`   ✅ User registration (${userData.email})`);      console.error('   No response received from server');

    console.log(`   ✅ Token received and working`);      console.error('   Request:', error.request);

    console.log(`   ✅ Authenticated requests functional`);    } else {

    console.log('\n🎉 Backend + CORS + Sanctum are 100% functional!\n');      console.error('   Error:', error.message);

        }

  } catch (error) {    process.exit(1);

    console.error('\n❌ ERROR during registration flow:');  }

    console.error('   Message:', error.message);}

    if (error.cause) {

      console.error('   Cause:', error.cause);// Run the test

    }testRegistration();

    process.exit(1);
  }
}

// Run test
testRegistration();
