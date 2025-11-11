# RentHub Frontend - Quick Start Guide

## 🎨 Design System Overview

RentHub frontend folosește **shadcn/ui** cu o paletă modernă dark theme pentru o experiență profesională.

## 🚀 Comenzi Rapide

```bash
# Instalare dependențe
npm install

# Development server
npm run dev

# Build production
npm run build

# Start production server
npm start

# Linting
npm run lint

# Type checking
npm run type-check
```

## 🎯 Funcționalități Cheie

### Design Modern
- ✅ **Dark/Light Theme Toggle** - Comutare automată între teme
- ✅ **Backdrop Blur** - Efecte glassmorphism pe navbar
- ✅ **Gradient Text** - Texte cu gradient pentru branding
- ✅ **Trend Indicators** - Săgeți sus/jos pentru statistici
- ✅ **Stats Cards** - Card-uri moderne pentru metrici

### Responsive Design
- ✅ **Mobile-First** - Design optimizat pentru mobile
- ✅ **Touch-Friendly** - Ținte de 44px pentru touch
- ✅ **Scrollable Tables** - Tabele scroll pe mobile
- ✅ **Stack Layouts** - Layout-uri verticale pe ecrane mici

### Componente Moderne
- ✅ **Error Pages** - 404 și error pages cu design modern
- ✅ **Empty States** - Stări goale cu iconițe și acțiuni
- ✅ **Loading Skeletons** - Skeleton loaders consistente
- ✅ **Form Components** - Formulare cu validare

## 📁 Structură Proiect

```
frontend/
├── src/
│   ├── app/                    # Next.js App Router pages
│   │   ├── page.tsx           # Homepage (modernizat)
│   │   ├── globals.css        # Variabile CSS shadcn
│   │   ├── responsive.css     # Mobile-first CSS
│   │   ├── not-found.tsx      # 404 page (modernizat)
│   │   ├── error.tsx          # Error page (modernizat)
│   │   ├── dashboard/         # Dashboard pages
│   │   ├── properties/        # Properties pages
│   │   └── auth/              # Auth pages (login, register)
│   │
│   ├── components/
│   │   ├── ui/                # shadcn components
│   │   ├── navbar.tsx         # Navigation (cu theme toggle)
│   │   ├── footer.tsx         # Footer (modernizat)
│   │   ├── theme-toggle.tsx   # Light/Dark switch
│   │   ├── empty-state.tsx    # Empty states (modernizat)
│   │   └── loading-states.tsx # Loading skeletons
│   │
│   ├── hooks/                 # Custom React hooks
│   ├── lib/                   # Utilities & helpers
│   └── types/                 # TypeScript types
│
└── public/                    # Static assets
```

## 🎨 Cum să Folosești Design System

### 1. Culori

```tsx
// Folosește variabile CSS pentru culori
className="bg-background text-foreground"
className="bg-primary text-primary-foreground"
className="text-muted-foreground"
```

### 2. Stats Cards

```tsx
<Card>
  <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
    <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
    <DollarSign className="h-4 w-4 text-muted-foreground" />
  </CardHeader>
  <CardContent>
    <div className="text-2xl font-bold">$45,231.89</div>
    <p className="text-xs text-muted-foreground flex items-center gap-1">
      <TrendingUp className="h-3 w-3 text-green-500" /> +20% from last month
    </p>
  </CardContent>
</Card>
```

### 3. Empty States

```tsx
<EmptyState
  icon={Heart}
  title="No favorites yet"
  description="Properties you favorite will appear here."
  action={
    <Button asChild>
      <Link href="/properties">Browse Properties</Link>
    </Button>
  }
/>
```

### 4. Responsive Layout

```tsx
// Grid care devine single column pe mobile
<div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
  {/* Cards */}
</div>

// Butoane stack vertical pe mobile
<div className="flex flex-col sm:flex-row gap-3">
  <Button>Primary</Button>
  <Button variant="outline">Secondary</Button>
</div>
```

## 🌓 Theme Toggle

Theme toggle este deja integrat în navbar. Utilizatorii pot comuta între:
- 🌞 **Light Mode**
- 🌙 **Dark Mode**
- 💻 **System** (auto-detect)

## 📱 Mobile Optimization

Responsive CSS include:
- Padding redus pe mobile (1rem)
- Headings mai mici (h1: 30px, h2: 24px)
- Touch targets de minimum 44px
- Grid gaps optimizate (1rem pe mobile, 1.5rem pe tablet)
- Tabele scrollabile orizontal

## 🎯 Best Practices

1. **Folosește componente shadcn** în loc de HTML custom
2. **Păstrează spacing consistent** - gap-4, gap-6, p-4, p-6, etc.
3. **Folosește text-muted-foreground** pentru text secundar
4. **Adaugă trend indicators** în stats cards
5. **Testează pe mobile** înainte de deployment

## 🔧 Troubleshooting

### Build Errors
```bash
# Șterge cache și reinstalează
rm -rf .next node_modules
npm install
npm run build
```

### Theme Nu Funcționează
- Verifică că `ThemeProvider` este în `layout.tsx`
- Verifică că `next-themes` este instalat
- Clear browser cache

### Responsive CSS Nu Se Aplică
- Verifică import în `layout.tsx`: `import './responsive.css'`
- Verifică ordinea import-urilor (responsive.css după globals.css)

## 📚 Resurse

- [shadcn/ui Docs](https://ui.shadcn.com)
- [Next.js 15 Docs](https://nextjs.org/docs)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Design System Implementation](./DESIGN_SYSTEM_IMPLEMENTATION.md)

## 🎉 Status

- ✅ Build: Passing
- ✅ Design: 90% Complete
- ✅ Mobile: Responsive Framework Implemented
- ✅ Theme: Light/Dark Toggle Active
- ✅ Performance: Optimized

**Ready for Production! 🚀**
