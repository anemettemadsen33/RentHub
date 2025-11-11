# Frontend Pages Audit & Implementation Guide

## Overview
This document tracks the completeness status of all frontend pages and provides implementation guidelines for maintaining consistency.

**Last Updated:** November 9, 2025

---

## Page Status Matrix

| Page | i18n | A11y | UI Consistency | Loading States | Performance | Notes |
|------|------|------|----------------|----------------|-------------|-------|
| **Marketing** |
| `/` (Home) | ✅ | ✅ | ✅ | ✅ | ✅ | Hero with tooltips, animations |
| `/about` | ✅ | ✅ | ✅ | N/A | ✅ | Static content, optimized |
| `/press` | ✅ | ✅ | ✅ | N/A | ✅ | Staggered animations |
| `/careers` | ✅ | ✅ | ✅ | N/A | ✅ | Job cards with tooltips |
| **Utility** |
| `/help` | ✅ | ✅ | ✅ | N/A | ✅ | Accordion sections |
| `/faq` | ✅ | ✅ | ✅ | N/A | ✅ | Staggered cards |
| `/contact` | ✅ | ✅ | ✅ | ✅ | ✅ | Client form with validation |
| **Legal** |
| `/terms` | ✅ | ✅ | ✅ | N/A | ✅ | Semantic headings |
| `/privacy` | ✅ | ✅ | ✅ | N/A | ✅ | Card sections |
| `/cookies` | ✅ | ✅ | ✅ | N/A | ✅ | Animated header |
| **Transactional** |
| `/properties` | ✅ | ✅ | ✅ | ✅ | ⚠️ | Needs virtualization for 100+ items |
| `/properties/[id]` | ⚠️ | ✅ | ✅ | ✅ | ✅ | Image gallery could use SmartImage |
| `/bookings` | ✅ | ✅ | ✅ | ✅ | ✅ | Full i18n, accessibility complete |
| `/bookings/[id]` | ⚠️ | ✅ | ✅ | ✅ | ✅ | Needs i18n namespace |
| `/messages` | ⚠️ | ✅ | ✅ | ✅ | ⚠️ | TODO: Migrate to Laravel Echo, add virtualization |
| `/notifications` | ✅ | ✅ | ✅ | ✅ | ✅ | Real-time updates working |
| **Dashboards** |
| `/dashboard` | ⚠️ | ✅ | ✅ | ✅ | ✅ | Needs i18n |
| `/dashboard/properties` | ⚠️ | ✅ | ✅ | ✅ | ✅ | Needs i18n |
| `/dashboard/bookings` | ⚠️ | ✅ | ✅ | ✅ | ✅ | Needs i18n |
| **User** |
| `/profile` | ⚠️ | ✅ | ✅ | ✅ | ✅ | Needs i18n |
| `/settings` | ⚠️ | ✅ | ✅ | ✅ | ✅ | Needs i18n |
| `/auth/login` | ⚠️ | ✅ | ✅ | ✅ | ✅ | Needs i18n |
| `/auth/register` | ⚠️ | ✅ | ✅ | ✅ | ✅ | Needs i18n |
| **Features** |
| `/property-comparison` | ✅ | ✅ | ✅ | ✅ | ✅ | Responsive table |
| `/wishlists` | ✅ | ✅ | ✅ | ✅ | ✅ | Animated cards |
| `/screening` | ✅ | ✅ | ✅ | ✅ | ✅ | Form validation |
| `/verification` | ✅ | ✅ | ✅ | ✅ | ✅ | Multi-step process |
| `/insurance` | ✅ | ✅ | ✅ | N/A | ✅ | Static info page |
| `/loyalty` | ⚠️ | ✅ | ✅ | ✅ | ✅ | Needs i18n |
| `/referrals` | ⚠️ | ✅ | ✅ | ✅ | ✅ | Needs i18n |

**Legend:**
- ✅ Complete
- ⚠️ Partial/Needs Work
- ❌ Not Started
- N/A Not Applicable

---

## Implementation Standards

### 1. Internationalization (i18n)

**Pattern:**
```tsx
import { useTranslations } from 'next-intl';

export default function MyPage() {
  const t = useTranslations('myPageNamespace');
  
  return <h1>{t('title')}</h1>;
}
```

**Requirements:**
- ✅ All user-facing text uses `t()` translation function
- ✅ Namespace per page or feature area
- ✅ Keys added to both runtime (`src/i18n/messages/en.json`) and static (`frontend/messages/en.json`)
- ✅ Sync translations to all supported locales (en, ro, fr, es, de)

**Common namespaces:**
- `home`, `properties`, `bookingsPage`, `comparison`, `navigation`, `notify`

---

### 2. Accessibility (A11y)

**Checklist:**
- ✅ Semantic HTML (`<main>`, `<nav>`, `<header>`, `<footer>`)
- ✅ ARIA labels for icon buttons and interactive elements
- ✅ Alt text for all images (descriptive, not "image")
- ✅ Focus states visible (use `keyboard-nav` class from accessibility.css)
- ✅ Skip link present (global in layout)
- ✅ Live regions for dynamic updates (`aria-live="polite"`)
- ✅ Keyboard navigation (Tab order logical)
- ✅ Color contrast WCAG AA minimum

**Example:**
```tsx
<button aria-label="Delete property" onClick={handleDelete}>
  <Trash2 className="h-4 w-4" aria-hidden="true" />
</button>
```

---

### 3. UI Consistency

**Layout:**
- Use `<MainLayout>` wrapper for standard pages
- Container: `<div className="container mx-auto px-4 py-8">`
- Headings: `<h1 className="text-3xl font-bold mb-2">`
- Subtitle: `<p className="text-gray-600 mb-6">`

**Components:**
- Cards: shadcn/ui `<Card>`
- Buttons: `<Button variant="..." size="...">`
- Forms: `<Input>`, `<Textarea>`, `<Select>` from shadcn
- Tooltips: Wrap icon buttons in `<TooltipProvider>` + `<Tooltip>`

**Animations:**
- Fade in: `className="animate-fade-in"`
- Fade in up: `className="animate-fade-in-up" style={{ animationDelay: '100ms' }}`
- Stagger: Multiply index by 40-100ms delay

---

### 4. Loading & Error States

**Loading:**
```tsx
import { ListSkeleton } from '@/components/loading-states';

if (loading) return <ListSkeleton count={5} />;
```

**Error:**
```tsx
import { ErrorFallback } from '@/components/loading-states';

if (error) return <ErrorFallback error={error} resetErrorBoundary={refetch} />;
```

**Available skeletons:**
- `PageLoadingSkeleton`
- `PropertyCardSkeleton`
- `ListSkeleton`
- `TableSkeleton`
- `StatsCardSkeleton`

---

### 5. Performance

**Image Optimization:**
```tsx
import { SmartImage, PropertyImage, HeroImage } from '@/components/ui/smart-image';

<PropertyImage
  src={property.image_url}
  alt={property.title}
  fill
  isFirstCard={index === 0}
/>
```

**List Optimization:**
```tsx
import { useDebounce, useFilteredList } from '@/hooks/use-performance';

const debouncedSearch = useDebounce((query) => {
  // Search logic
}, 300);

const { sorted } = useFilteredList(items, filterFn, sortFn);
```

**Virtualization:**
For lists > 50 items, use `useVirtualList` hook.

---

### 6. Testing

**Component test template:**
```tsx
import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';

describe('MyComponent', () => {
  it('renders correctly', () => {
    render(<MyComponent />);
    expect(screen.getByText('Expected Text')).toBeInTheDocument();
  });
});
```

**Run tests:**
```bash
npm run test
```

---

## Pending Work

### High Priority
1. **i18n for dashboards & auth pages** - Add namespaces for `/dashboard/*`, `/auth/*`, `/profile`, `/settings`
2. **Messages page Laravel Echo migration** - Replace socket.io with Laravel Echo/Reverb
3. **Properties virtualization** - Implement for 100+ item lists

### Medium Priority
4. **Migrate remaining Image → SmartImage** - Property detail page, comparison page
5. **E2E test coverage** - Add Playwright tests for critical flows
6. **Performance monitoring** - Add Web Vitals tracking alerts

### Low Priority
7. **Dark mode refinements** - Ensure all new components respect theme
8. **PWA offline states** - Enhance offline experience for data pages

---

## Contributing Guidelines

### Adding a New Page

1. **Create page file** in `src/app/[route]/page.tsx`
2. **Add i18n namespace** to `messages/en.json` and `src/i18n/messages/en.json`
3. **Use `<MainLayout>`** wrapper
4. **Implement accessibility**: semantic HTML, ARIA labels, alt text
5. **Add loading skeleton** from `loading-states.tsx`
6. **Add animations** using Tailwind utilities
7. **Use `SmartImage`** for images
8. **Write component test** in `__tests__/` directory
9. **Update this audit doc** with status

### Code Review Checklist

- [ ] No hardcoded strings (all use `t()`)
- [ ] All images have descriptive alt text
- [ ] Buttons have aria-labels if icon-only
- [ ] Focus states visible
- [ ] Loading/error states handled
- [ ] Responsive design tested (mobile, tablet, desktop)
- [ ] TypeScript errors resolved
- [ ] Component test added/updated

---

## Architecture Decisions

### Why SmartImage?
Prevents Next.js "priority + loading" conflict, adds fallback support, centralizes image optimization logic.

### Why separate static messages?
RootLayout fallback ensures Next-Intl doesn't crash under Turbopack or test scenarios when runtime messages unavailable.

### Why FocusManager?
Centralizes focus trap logic for modals, provides programmatic focus utilities, enhances keyboard navigation UX.

### Why virtualization for messages?
Large conversation histories cause render slowdown; virtual scrolling maintains 60fps.

---

## Quick Reference

**Animation classes:**
- `animate-fade-in`
- `animate-fade-in-up`
- `animate-scale-in`
- `animate-skeleton-pulse`

**Spacing:**
- Container: `container mx-auto px-4`
- Section: `py-8` (mobile), `py-12` (desktop)
- Card gap: `space-y-4` or `gap-6`

**Breakpoints:**
- `sm`: 640px
- `md`: 768px
- `lg`: 1024px
- `xl`: 1280px

---

## Contact & Support

For questions or contributions, see:
- **Next Steps:** `NEXT_STEPS.md`
- **Testing Guide:** `TESTING.md` (if exists)
- **Deployment:** `DEPLOYMENT-CHECKLIST.md`
