# RentHub Frontend - Next.js 16 + TypeScript + shadcn/ui

## Overview

The RentHub frontend is a modern, responsive web application built with Next.js 14/19, TypeScript, and shadcn/ui components. It provides a seamless user experience for property browsing, booking, and management across multiple languages and currencies.

## Technology Stack

- **Framework**: Next.js 16.0.1 (App Router)
- **Language**: TypeScript 5.9.3
- **Styling**: Tailwind CSS 4.x
- **UI Components**: shadcn/ui (Radix UI primitives)
- **State Management**: React Query (TanStack Query)
- **Forms**: React Hook Form + Zod validation
- **Authentication**: NextAuth.js
- **Internationalization**: i18next + next-intl
- **Animations**: Framer Motion
- **Real-time**: Socket.io Client
- **Maps**: Mapbox GL
- **Date Handling**: date-fns + react-datepicker
- **PWA**: next-pwa

## Prerequisites

- Node.js 18+ and npm
- Backend API running (see BACKEND_README.md)

## Installation

### 1. Install Dependencies

```bash
cd frontend
npm install
```

### 2. Environment Configuration

```bash
cp .env.example .env.local
```

Configure your `.env.local` file:

```env
# API Configuration
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000/api

# NextAuth
NEXTAUTH_URL=http://localhost:3000
NEXTAUTH_SECRET=your-secret-key

# OAuth Providers
NEXT_PUBLIC_GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=

NEXT_PUBLIC_FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=

# Maps
NEXT_PUBLIC_MAPBOX_TOKEN=

# WebSocket
NEXT_PUBLIC_SOCKET_URL=http://localhost:6001

# Analytics
NEXT_PUBLIC_GA_ID=
NEXT_PUBLIC_PLAUSIBLE_DOMAIN=

# Feature Flags
NEXT_PUBLIC_ENABLE_PWA=true
NEXT_PUBLIC_ENABLE_ANALYTICS=false
```

### 3. Start Development Server

```bash
npm run dev
```

Open [http://localhost:3000](http://localhost:3000) in your browser.

## Project Structure

```
frontend/
├── src/
│   ├── app/                    # Next.js App Router pages
│   │   ├── (auth)/            # Auth pages (login, register)
│   │   ├── (dashboard)/       # Dashboard pages
│   │   ├── properties/        # Property pages
│   │   ├── bookings/          # Booking pages
│   │   ├── messages/          # Messages/chat
│   │   ├── profile/           # User profile
│   │   ├── layout.tsx         # Root layout
│   │   └── page.tsx           # Homepage
│   ├── components/            # React components
│   │   ├── ui/               # shadcn/ui components
│   │   ├── forms/            # Form components
│   │   ├── cards/            # Card components
│   │   ├── filters/          # Filter components
│   │   ├── layout/           # Layout components
│   │   └── features/         # Feature-specific components
│   ├── contexts/             # React contexts
│   ├── hooks/                # Custom React hooks
│   ├── lib/                  # Utility functions
│   │   ├── api.ts           # API client
│   │   ├── auth.ts          # Auth helpers
│   │   ├── i18n.ts          # i18n configuration
│   │   └── utils.ts         # General utilities
│   ├── services/            # API service layers
│   ├── styles/              # Global styles
│   └── types/               # TypeScript type definitions
├── public/                  # Static assets
│   ├── locales/            # Translation files
│   ├── images/             # Images
│   └── icons/              # Icons
├── e2e/                    # E2E tests
├── components.json         # shadcn/ui configuration
├── next.config.ts         # Next.js configuration
├── tailwind.config.js     # Tailwind configuration
└── tsconfig.json          # TypeScript configuration
```

## Key Features

### 1. Homepage

**Location**: `src/app/page.tsx`

Features:
- Hero section with search autocomplete
- Featured properties carousel
- Categories (City, Beach, Mountain, Luxury)
- Popular destinations
- Testimonials

### 2. Search & Filters

**Location**: `src/app/properties/page.tsx`

Advanced filtering:
- Location search with autocomplete
- Date range picker
- Guest count
- Price range
- Property type
- Amenities
- Ratings
- Instant booking

Sorting options:
- Price (low to high, high to low)
- Popularity
- Rating
- Recently added

### 3. Property Detail Page

**Location**: `src/app/properties/[id]/page.tsx`

Features:
- Image gallery with lightbox
- Property details in multiple languages
- Availability calendar
- Booking form
- Reviews and ratings
- Location map
- Similar properties
- Host information

### 4. Owner Dashboard

**Location**: `src/app/owner/dashboard/page.tsx`

Features:
- Properties overview
- Bookings management
- Revenue analytics
- Calendar view
- Messages
- Reviews

### 5. Guest Dashboard

**Location**: `src/app/tenant/dashboard/page.tsx`

Features:
- My bookings
- Upcoming trips
- Past trips
- Favorites
- Messages
- Reviews

### 6. Booking Flow

**Location**: `src/app/bookings/new/page.tsx`

Steps:
1. Select dates and guests
2. Review booking details
3. Enter guest information
4. Payment
5. Confirmation

### 7. Real-time Chat

**Location**: `src/app/messages/page.tsx`

Features:
- Real-time messaging
- Conversation list
- Message notifications
- File attachments
- Message templates

### 8. User Profile

**Location**: `src/app/profile/page.tsx`

Sections:
- Personal information
- Profile picture
- Verification status
- Payment methods
- Notifications preferences
- Language and currency

## shadcn/ui Components

### Installation

Components are installed using the shadcn/ui CLI:

```bash
npx shadcn-ui@latest add button
npx shadcn-ui@latest add card
npx shadcn-ui@latest add input
npx shadcn-ui@latest add form
npx shadcn-ui@latest add dialog
npx shadcn-ui@latest add dropdown-menu
```

### Available Components

All components are in `src/components/ui/`:

- **Form Elements**: Button, Input, Textarea, Select, Checkbox, Radio
- **Layout**: Card, Separator, Tabs, Accordion
- **Feedback**: Dialog, Alert, Toast
- **Navigation**: Navigation Menu, Dropdown Menu
- **Data Display**: Avatar, Badge, Table

### Usage Example

```tsx
import { Button } from "@/components/ui/button";
import { Card, CardHeader, CardContent } from "@/components/ui/card";

export default function Example() {
  return (
    <Card>
      <CardHeader>
        <h2>Property Title</h2>
      </CardHeader>
      <CardContent>
        <p>Property description</p>
        <Button>Book Now</Button>
      </CardContent>
    </Card>
  );
}
```

## Multi-Language Support (i18n)

### Configuration

i18n is configured in `src/lib/i18n.ts`:

```typescript
import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

i18n
  .use(initReactI18next)
  .init({
    resources: {
      en: { translation: require('../public/locales/en/common.json') },
      ro: { translation: require('../public/locales/ro/common.json') },
      es: { translation: require('../public/locales/es/common.json') },
      fr: { translation: require('../public/locales/fr/common.json') },
      de: { translation: require('../public/locales/de/common.json') },
    },
    lng: 'en',
    fallbackLng: 'en',
  });
```

### Usage

```tsx
import { useTranslation } from 'react-i18next';

export default function Component() {
  const { t } = useTranslation();
  
  return (
    <h1>{t('welcome')}</h1>
  );
}
```

### Translation Files

Located in `public/locales/{lang}/`:

- `common.json` - Common translations
- `properties.json` - Property-related translations
- `bookings.json` - Booking-related translations
- `messages.json` - Message translations

## Multi-Currency Support

### Currency Context

```tsx
import { useCurrency } from '@/contexts/CurrencyContext';

export default function PriceDisplay({ amount }) {
  const { currency, convertPrice, formatPrice } = useCurrency();
  
  return (
    <span>{formatPrice(convertPrice(amount))}</span>
  );
}
```

### Supported Currencies

- USD (US Dollar)
- EUR (Euro)
- GBP (British Pound)
- RON (Romanian Leu)

Exchange rates are fetched from the backend API.

## API Integration

### API Client

Located in `src/lib/api.ts`:

```typescript
import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Add auth token to requests
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;
```

### React Query Usage

```tsx
import { useQuery } from '@tanstack/react-query';
import api from '@/lib/api';

export function useProperties(filters) {
  return useQuery({
    queryKey: ['properties', filters],
    queryFn: async () => {
      const { data } = await api.get('/properties', { params: filters });
      return data;
    },
  });
}
```

## Authentication

### NextAuth Configuration

Located in `src/app/api/auth/[...nextauth]/route.ts`:

```typescript
import NextAuth from 'next-auth';
import GoogleProvider from 'next-auth/providers/google';
import FacebookProvider from 'next-auth/providers/facebook';
import CredentialsProvider from 'next-auth/providers/credentials';

export const authOptions = {
  providers: [
    GoogleProvider({
      clientId: process.env.GOOGLE_CLIENT_ID!,
      clientSecret: process.env.GOOGLE_CLIENT_SECRET!,
    }),
    FacebookProvider({
      clientId: process.env.FACEBOOK_CLIENT_ID!,
      clientSecret: process.env.FACEBOOK_CLIENT_SECRET!,
    }),
    CredentialsProvider({
      name: 'Credentials',
      credentials: {
        email: { label: "Email", type: "email" },
        password: { label: "Password", type: "password" }
      },
      async authorize(credentials) {
        // Implement authentication logic
      }
    }),
  ],
  // Additional configuration
};
```

### Protected Routes

```tsx
import { useSession } from 'next-auth/react';
import { redirect } from 'next/navigation';

export default function ProtectedPage() {
  const { data: session } = useSession({
    required: true,
    onUnauthenticated() {
      redirect('/auth/login');
    },
  });

  return <div>Protected Content</div>;
}
```

## Forms with React Hook Form

```tsx
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import * as z from 'zod';

const schema = z.object({
  title: z.string().min(5),
  price: z.number().positive(),
});

export default function PropertyForm() {
  const { register, handleSubmit, formState: { errors } } = useForm({
    resolver: zodResolver(schema),
  });

  const onSubmit = (data) => {
    console.log(data);
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)}>
      <input {...register('title')} />
      {errors.title && <span>{errors.title.message}</span>}
      
      <input type="number" {...register('price')} />
      {errors.price && <span>{errors.price.message}</span>}
      
      <button type="submit">Submit</button>
    </form>
  );
}
```

## Real-time Features

### Socket.io Integration

```tsx
import { useEffect } from 'react';
import io from 'socket.io-client';

export default function ChatComponent() {
  useEffect(() => {
    const socket = io(process.env.NEXT_PUBLIC_SOCKET_URL!);
    
    socket.on('message', (data) => {
      console.log('New message:', data);
    });
    
    return () => {
      socket.disconnect();
    };
  }, []);
  
  return <div>Chat Interface</div>;
}
```

## PWA Configuration

PWA is configured in `next.config.ts`:

```typescript
const withPWA = require('next-pwa')({
  dest: 'public',
  disable: process.env.NODE_ENV === 'development',
});

module.exports = withPWA({
  // Next.js config
});
```

## Building for Production

### Build

```bash
npm run build
```

### Start Production Server

```bash
npm start
```

### Static Export (if applicable)

```bash
npm run build
npm run export
```

## Testing

### Linting

```bash
npm run lint
```

### Type Checking

```bash
npx tsc --noEmit
```

## Performance Optimization

### Image Optimization

Use Next.js Image component:

```tsx
import Image from 'next/image';

<Image 
  src="/property.jpg" 
  alt="Property" 
  width={800} 
  height={600}
  priority
/>
```

### Code Splitting

Next.js automatically code-splits by route. For additional splitting:

```tsx
import dynamic from 'next/dynamic';

const DynamicComponent = dynamic(() => import('./HeavyComponent'), {
  loading: () => <p>Loading...</p>,
});
```

### Font Optimization

```tsx
import { Inter } from 'next/font/google';

const inter = Inter({ subsets: ['latin'] });
```

## Deployment

### Vercel (Recommended)

1. Connect GitHub repository to Vercel
2. Set environment variables in Vercel dashboard
3. Deploy automatically on push to main branch

### Docker

```bash
docker build -t renthub-frontend .
docker run -p 3000:3000 renthub-frontend
```

### Static Hosting

For static export:
```bash
npm run build
```

Deploy the `out/` directory to any static hosting service.

## Troubleshooting

### Clear Next.js Cache

```bash
rm -rf .next
npm run dev
```

### Module Not Found

```bash
rm -rf node_modules package-lock.json
npm install
```

### TypeScript Errors

```bash
npx tsc --noEmit
```

## Support

For issues and questions:
- GitHub Issues: https://github.com/anemettemadsen33/RentHub/issues
- Documentation: See docs/ directory

## License

MIT License - see LICENSE file for details.
