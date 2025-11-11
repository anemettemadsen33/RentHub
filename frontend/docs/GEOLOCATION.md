# Automatic Language Detection with IPStack

This feature automatically detects the user's preferred language based on their geographic location using the IPStack API.

## How It Works

1. **First Visit Detection**: When a user visits the site for the first time (no locale cookie set), the system:
   - Calls the IPStack API to get the user's country and language based on their IP address
   - Maps the country code to a supported locale (e.g., RO → ro, MD → ro)
   - Falls back to the browser's Accept-Language header if IP detection fails
   - Sets a `NEXT_LOCALE` cookie to remember the preference

2. **Locale Priority Chain**:
   - User's manual selection (via language switcher) - **Highest priority**
   - Cookie (`NEXT_LOCALE`)
   - IP-based geolocation (IPStack)
   - Browser language (Accept-Language header)
   - Default (English) - **Fallback**

3. **User Experience**:
   - Automatic, seamless detection on first visit
   - Optional notification showing detected language
   - User can manually change language at any time via the language switcher
   - Preference is persisted for 1 year via cookie

## Configuration

### Environment Variables

Add to `.env.local`:

```bash
NEXT_PUBLIC_IPSTACK_API_KEY=your_api_key_here
```

### API Key Setup

1. Sign up at [IPStack](https://ipstack.com/)
2. Get your API key from the dashboard
3. Free tier includes 100 requests/month (sufficient for small sites)
4. Paid tiers available for higher traffic

### Country-to-Locale Mapping

Edit `src/lib/geolocation.ts` to add more country mappings:

```typescript
const COUNTRY_TO_LOCALE: Record<string, string> = {
  RO: 'ro', // Romania
  MD: 'ro', // Moldova
  // Add more mappings:
  ES: 'es', // Spain (if you add Spanish support)
  FR: 'fr', // France (if you add French support)
};
```

## API Endpoints

### `/api/locale/detect`

Server-side API route for locale detection.

**Response:**
```json
{
  "locale": "ro",
  "country_code": "RO",
  "country_name": "Romania",
  "detected_from": "89.45.67.123"
}
```

## Components

### `LocaleAutoDetect`
Client component that runs on first visit to detect and set locale.

### `LocaleDetectionNotification`
Shows a toast notification when locale is auto-detected.

### `LanguageSwitcher`
Manual language selection dropdown (user override).

## Testing

### Test Auto-Detection

1. Clear cookies and session storage
2. Use a VPN to simulate different countries
3. Reload the page
4. Check that locale is detected correctly

### Test Override

1. Let system auto-detect locale
2. Manually change language via switcher
3. Reload page
4. Verify manual selection persists

### Test Fallback

1. Disable IPStack API key
2. System should fall back to browser language
3. No errors should appear

## Performance Considerations

- **Caching**: IP detection results are cached for 24 hours
- **Session Storage**: Detection only runs once per session
- **Cookie Check**: Existing preferences skip detection entirely
- **API Quota**: Free tier limited to 100 requests/month
  - Production sites should use paid tier
  - Consider caching at CDN level for high traffic

## Privacy & GDPR

- IP addresses are only sent to IPStack API
- No IP addresses are stored in your database
- Users can opt-out by changing language manually
- Cookie consent banner covers the `NEXT_LOCALE` cookie
- IPStack is GDPR compliant

## Troubleshooting

### Detection Not Working

1. Check API key is set correctly in `.env.local`
2. Check browser console for errors
3. Verify IPStack API quota hasn't been exceeded
4. Test the `/api/locale/detect` endpoint directly

### Wrong Language Detected

1. Check country-to-locale mapping in `geolocation.ts`
2. Add specific country codes if needed
3. Test with different VPN locations

### Localhost Testing

IPStack's "check" endpoint uses their server's IP when detecting localhost requests. For proper testing:
- Deploy to staging environment
- Use ngrok or similar tunneling service
- Test with VPN from different countries

## Future Enhancements

- [ ] Add more supported languages
- [ ] Cache detection results in database
- [ ] A/B test notification vs silent detection
- [ ] Analytics for detected vs selected languages
- [ ] Fallback to alternative geolocation APIs
