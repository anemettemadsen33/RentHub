# 🏠 RentHub Frontend

Modern Next.js frontend pentru platforma de închiriere RentHub, construit cu shadcn/ui.

> **🎉 Setup complet!** Frontend-ul este gata de utilizare cu toate componentele necesare.

## Tech Stack

- **Next.js 15** - React framework
- **React 19** - UI library
- **TypeScript** - Type safety
- **Tailwind CSS** - Styling
- **shadcn/ui** - UI components
- **Axios** - HTTP client

## Prerequisites

- Node.js 18+ and npm
- Backend API running (see `/backend` folder)

## Local Development

1. Install dependencies:
```bash
npm install
```

2. Copy environment file:
```bash
cp .env.example .env.local
```

3. Update `.env.local` with your backend API URL:
```env
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1
```

4. Run development server:
```bash
npm run dev
```

The application will be available at `http://localhost:3000`

## Building for Production

```bash
npm run build
npm start
```

## Deployment to Vercel

### Option 1: Vercel CLI

1. Install Vercel CLI:
```bash
npm i -g vercel
```

2. Login to Vercel:
```bash
vercel login
```

3. Deploy:
```bash
vercel
```

4. For production:
```bash
vercel --prod
```

### Option 2: GitHub Integration

1. Push code to GitHub
2. Import project in Vercel dashboard
3. Configure environment variables:
   - `NEXT_PUBLIC_API_URL` - Your production backend URL
   - `NEXT_PUBLIC_API_BASE_URL` - Your production API base URL
4. Deploy

## Environment Variables

Create a `.env.local` file with:

```env
# API Configuration
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1

# App Configuration
NEXT_PUBLIC_APP_NAME=RentHub
NEXT_PUBLIC_APP_URL=http://localhost:3000

# Optional: Google Maps API
NEXT_PUBLIC_GOOGLE_MAPS_API_KEY=your_key_here

# Optional: Analytics
NEXT_PUBLIC_GA_TRACKING_ID=your_ga_id

# Web Push (Pusher Beams)
NEXT_PUBLIC_PUSHER_BEAMS_INSTANCE_ID=your_instance_id_here
```

### Production Environment Variables (Vercel)

In your Vercel project settings, add:

```
NEXT_PUBLIC_API_URL=https://your-backend-url.com
NEXT_PUBLIC_API_BASE_URL=https://your-backend-url.com/api/v1
NEXT_PUBLIC_PUSHER_BEAMS_INSTANCE_ID=your_instance_id_here
```

## Project Structure

```
frontend/
├── src/
│   ├── app/              # Next.js app router pages
│   │   ├── auth/         # Authentication pages
│   │   ├── properties/   # Property listings
│   │   ├── dashboard/    # User dashboards
│   │   ├── layout.tsx    # Root layout
│   │   └── page.tsx      # Home page
│   ├── components/       # React components
│   │   ├── ui/           # shadcn/ui components
│   │   ├── layouts/      # Layout components
│   │   ├── navbar.tsx    # Navigation bar
│   │   └── footer.tsx    # Footer
│   ├── contexts/         # React contexts
│   │   └── auth-context.tsx
│   ├── lib/              # Utilities
│   │   ├── api-client.ts # Axios configuration
│   │   └── utils.ts      # Helper functions
│   └── hooks/            # Custom React hooks
├── public/               # Static assets
├── package.json
├── tsconfig.json
├── tailwind.config.ts
└── next.config.ts
```

## Features

- ✅ Modern UI with shadcn/ui components
- ✅ Authentication (Login/Register)
- ✅ Property listings and search
- ✅ Responsive design
- ✅ Dark mode support
- ✅ Type-safe with TypeScript
- ✅ Optimized for production
- ✅ Web push notifications (Pusher Beams ready)

## API Integration

The frontend connects to the Laravel backend API. Make sure:

1. Backend is running and accessible
2. CORS is configured properly in backend
3. API base URL is correct in environment variables

## Adding shadcn/ui Components

To add new shadcn/ui components:

```bash
npx shadcn@latest add [component-name]
```

Example:
```bash
npx shadcn@latest add dialog
npx shadcn@latest add select
npx shadcn@latest add table
```

## Common Issues

### CORS Errors
Make sure your backend has CORS properly configured for your frontend URL.

### API Connection Issues
- Verify `NEXT_PUBLIC_API_BASE_URL` is correct
- Check backend is running
- Ensure no firewall blocking

### Build Errors
- Clear `.next` folder: `rm -rf .next`
- Reinstall dependencies: `rm -rf node_modules && npm install`

## License

MIT License

## Web Push (Pusher Beams) Usage

After setting `NEXT_PUBLIC_PUSHER_BEAMS_INSTANCE_ID` and deploying a service worker (`public/pusher-beams-sw.js`), you can subscribe a user to interests:

```ts
// Example component usage
import { useEffect } from 'react';
import { startBeams } from '@/lib/beams';

export function NotificationsBootstrap({ userId }: { userId: string }) {
   useEffect(() => {
      // Subscribe to a user-specific interest and a global broadcast channel
      startBeams([`user-${userId}`, 'broadcast']).catch(console.error);
   }, [userId]);
   return null;
}
```

Server-side publishing example (Laravel):
```php
// app/Services/BeamsClient.php usage
$result = app(\App\Services\BeamsClient::class)
      ->publishToInterests(['broadcast'], 'New Listing', 'A new property was just added!', [
            'listing_id' => $listing->id,
      ]);
```

Security note: NEVER expose `PUSHER_BEAMS_SECRET_KEY` to the frontend; only the instance ID is public.
