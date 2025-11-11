# RentHub Frontend - Complete UI Documentation

## 🎨 UI Framework: shadcn/ui

Frontend-ul RentHub folosește **shadcn/ui** - un set modern de componente React built on top of Radix UI și styled with Tailwind CSS.

## ✅ Componente Instalate

Toate componentele shadcn/ui sunt instalate și configurate:

- ✅ **Layout Components**: Card, Separator, Tabs, Sheet, Drawer
- ✅ **Form Components**: Input, Textarea, Select, Checkbox, Switch, Radio Group, Form, Label
- ✅ **Navigation**: Navigation Menu, Breadcrumb, Menubar, Command
- ✅ **Feedback**: Alert, Alert Dialog, Dialog, Toaster (Sonner), Toast, Progress, Skeleton
- ✅ **Data Display**: Table, Badge, Avatar, Tooltip, Hover Card, Accordion, Collapsible
- ✅ **Media**: Carousel, Aspect Ratio
- ✅ **Utility**: Scroll Area, Resizable, Toggle, Toggle Group, Input OTP, Context Menu

## 📱 Pagini Complete

### 1. **Dashboard** (`/dashboard`)
- **Features**:
  - 4 stat cards (Properties, Bookings, Revenue, Pending Payments)
  - 3 tabs: Recent Bookings, Properties, Activity
  - Real-time data loading from API
  - Responsive grid layout
  - Empty states with call-to-actions
- **Components**: Card, Tabs, Badge, Button, Skeleton, Icons

### 2. **Properties List** (`/properties`)
- **Features**:
  - Grid/List/Map view modes
  - Advanced filtering (price, bedrooms, bathrooms, amenities)
  - Search by location
  - Sorting (price, rating, newest)
  - Favorites functionality
  - Pagination
- **Components**: Card, Input, Select, Badge, Button, Filter Panel

### 3. **Property Details** (`/properties/[id]`)
- **Features**:
  - Image carousel with navigation
  - Property information cards
  - Amenities list with icons
  - Host information with verification badge
  - Booking card (sticky sidebar)
  - Favorite & Share buttons
  - Reviews section (planned)
- **Components**: Carousel, Card, Badge, Avatar, Button, Separator

### 4. **User Profile** (`/profile`)
- **Features**:
  - Profile editing form
  - Password change section
  - Notification preferences
  - Avatar display
  - Form validation
- **Components**: Card, Input, Label, Button, Form

### 5. **Settings** (`/settings`)
- **Features**:
  - 4 tabs: Notifications, Privacy, Preferences, Account
  - Toggle switches for notifications
  - Privacy visibility controls
  - Language, currency, timezone selection
  - Theme switcher (Light/Dark/System)
  - Export data functionality
  - Account deletion (danger zone)
- **Components**: Tabs, Card, Switch, Select, Button, Separator

### 6. **Bookings** (`/bookings`)
- **Features**:
  - List of user bookings
  - Status filtering
  - Date display
  - Quick actions
- **Components**: Table, Badge, Button, Calendar

### 7. **Messages** (`/messages`)
- **Features**:
  - Conversation list
  - Message preview
  - Unread indicators
  - Quick reply
- **Components**: Card, ScrollArea, Input, Button

### 8. **Payments** (`/payments/history`)
- **Features**:
  - Transaction history
  - Payment status badges
  - Date filtering
  - Invoice download
- **Components**: Table, Badge, Button, Select

## 🎯 Special Features

### Command Palette (⌘K / Ctrl+K)
Navigare rapidă prin întreaga aplicație:
- Press `Cmd+K` (Mac) or `Ctrl+K` (Windows/Linux)
- Search for any page or action
- Keyboard shortcuts for power users
- **Component**: Command Dialog

### Notification System
- Toast notifications using **Sonner**
- Success, error, info, warning variants
- Auto-dismiss with custom duration
- Positioned bottom-right

### Loading States
- Skeleton loaders pe toate paginile
- Progressive loading pentru UX optim
- Shimmer effects

### Responsive Design
- Mobile-first approach
- Breakpoints: sm (640px), md (768px), lg (1024px), xl (1280px)
- Touch-friendly UI elements
- Adaptive layouts

## 🎨 Design System

### Colors (CSS Variables)
```css
--background: 0 0% 100%
--foreground: 222.2 84% 4.9%
--primary: 221.2 83.2% 53.3%
--secondary: 210 40% 96.1%
--muted: 210 40% 96.1%
--accent: 210 40% 96.1%
--destructive: 0 84.2% 60.2%
--border: 214.3 31.8% 91.4%
```

### Typography
- Font Family: Inter (Google Fonts)
- Heading Sizes: 3xl, 2xl, xl, lg
- Body: base (16px)
- Small: sm (14px), xs (12px)

### Spacing
- Consistent padding/margins: 2, 4, 6, 8, 12, 16, 24, 32
- Gap utilities pentru flexbox/grid

## 🚀 Cum să Rulezi Frontend-ul

### Development Mode
```bash
cd frontend
npm run dev
```
Server pornește pe: **http://localhost:3000**

### Production Build
```bash
npm run build
npm start
```

### Environment Variables
Creează fișierul `.env.local`:
```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api
```

## 📁 Structura Componentelor

```
frontend/src/
├── components/
│   ├── ui/              # shadcn/ui components
│   │   ├── button.tsx
│   │   ├── card.tsx
│   │   ├── input.tsx
│   │   ├── select.tsx
│   │   ├── tabs.tsx
│   │   └── ... (35+ components)
│   ├── layouts/
│   │   └── main-layout.tsx
│   ├── command-palette.tsx
│   ├── navbar.tsx
│   └── providers.tsx
├── app/
│   ├── dashboard/
│   ├── properties/
│   ├── profile/
│   ├── settings/
│   └── ...
├── lib/
│   ├── api-client.ts      # Axios instance
│   ├── api-endpoints.ts   # 180+ endpoint mappings
│   ├── api-service.ts     # Type-safe services
│   └── utils.ts           # Helper functions
└── contexts/
    ├── auth-context.tsx
    └── notification-context.tsx
```

## 🔧 Customizare

### Adaugă Componente Noi
```bash
npx shadcn@latest add [component-name]
```

### Modifică Tema
Editează `tailwind.config.ts` și `globals.css`

### Adaugă Noi Pagini
1. Creează folder în `app/`
2. Adaugă `page.tsx`
3. Folosește `MainLayout` wrapper
4. Importă componente UI din `@/components/ui`

## 📚 Resurse

- [shadcn/ui Documentation](https://ui.shadcn.com)
- [Radix UI](https://www.radix-ui.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Next.js 15](https://nextjs.org)
- [Lucide Icons](https://lucide.dev)

## ✨ Best Practices

1. **Folosește componente UI existente** - Nu recrea ce deja există
2. **Respectă design patterns** - Menține consistența
3. **Loading states** - Întotdeauna afișează skeleton loaders
4. **Error handling** - Folosește toast notifications
5. **Responsive design** - Testează pe toate device-urile
6. **Accessibility** - shadcn/ui vine cu ARIA attributes built-in

## 🎯 Next Steps

- [ ] Add reviews & ratings system
- [ ] Implement real-time chat
- [ ] Add map view for properties
- [ ] Create admin panel
- [ ] Add analytics dashboard
- [ ] Implement push notifications
- [ ] Add dark mode toggle in UI
- [ ] Create onboarding flow

---

**Made with ❤️ using shadcn/ui**
