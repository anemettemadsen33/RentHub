import { NextRequest, NextResponse } from 'next/server';

/**
 * API Route: Detect user's locale from IP address
 * GET /api/locale/detect
 */
export async function GET(request: NextRequest) {
  const apiKey = process.env.NEXT_PUBLIC_IPSTACK_API_KEY;
  
  if (!apiKey) {
    return NextResponse.json(
      { error: 'IPStack API key not configured' },
      { status: 500 }
    );
  }

  try {
    // Get client IP from request headers
    const forwarded = request.headers.get('x-forwarded-for');
    const ip = forwarded ? forwarded.split(',')[0] : request.headers.get('x-real-ip');
    
    // If no IP or localhost, use IPStack's "check" endpoint
    const endpoint = !ip || ip === '127.0.0.1' || ip.startsWith('192.168.') 
      ? `http://api.ipstack.com/check?access_key=${apiKey}&fields=country_code,location.languages`
      : `http://api.ipstack.com/${ip}?access_key=${apiKey}&fields=country_code,location.languages`;

    const response = await fetch(endpoint, {
      next: { revalidate: 86400 } // Cache for 24 hours
    });

    if (!response.ok) {
      return NextResponse.json(
        { error: 'IPStack API request failed' },
        { status: response.status }
      );
    }

    const data = await response.json();
    
    // Map country code to locale
    const countryToLocale: Record<string, string> = {
      RO: 'ro',
      MD: 'ro',
    };

    let detectedLocale = countryToLocale[data.country_code] || 'en';
    
    // Fallback to language detection
    if (detectedLocale === 'en' && data.location?.languages?.[0]) {
      const langCode = data.location.languages[0].code.toLowerCase().split('-')[0];
      if (['en', 'ro'].includes(langCode)) {
        detectedLocale = langCode;
      }
    }

    return NextResponse.json({
      locale: detectedLocale,
      country_code: data.country_code,
      country_name: data.country_name || '',
      detected_from: ip || 'auto',
    });
  } catch (error) {
    console.error('Locale detection error:', error);
    return NextResponse.json(
      { error: 'Failed to detect locale', locale: 'en' },
      { status: 500 }
    );
  }
}
