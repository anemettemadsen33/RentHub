# Design System Audit (shadcn/ui + Tailwind)

Date: 2025-11-09
Scope: Frontend `RentHub` Next.js App Router implementation using shadcn/ui (style: new-york) and Tailwind tokens.

## 1. Core Foundations

### 1.1 Color System
- Uses CSS variables via `:root` (configured through shadcn preset) mapping to semantic tokens: `--background`, `--foreground`, `--primary`, `--secondary`, `--accent`, `--muted`, `--destructive`, etc.
- Tailwind extension maps semantic colors to `hsl(var(--token))` ensuring theme switch works (dark mode via `class`).
- Recommendation:
  - Add explicit success / warning tokens (e.g. `--success`, `--warning`) to avoid ad‑hoc green/amber classes.
  - Centralize status colors (booking status, chat presence) instead of inline `text-green-500`, etc.

### 1.2 Spacing & Layout
- Container padding: `2rem`; max width @ `2xl: 1400px` (good for content pages, might be wide for dense dashboards).
- Observed patterns: Cards often use `p-4` or `p-6`; forms vary between `space-y-4` and `space-y-6`.
- Recommendation: Define spacing tiers: `compact (p-3)`, `standard (p-4)`, `comfortable (p-6)` and document usage (lists vs detail pages vs dashboards) to reduce inconsistency.

### 1.3 Radius
- Tokens: `lg = var(--radius)`, `md = radius - 2px`, `sm = radius - 4px`.
- Recommendation: Adopt usage rule: `lg` for surfaces (Card, Dialog), `md` for interactive inputs (Button, Input), `sm` for pills / badges only.

### 1.4 Typography
- Not explicitly customized beyond default. Frequent usage of `text-sm`, `text-xs` for metadata; headings manually sized.
- Recommendation: Introduce semantic utilities or class composition (e.g. `.heading-1`, `.heading-2`, `.body-sm`) via `@layer components` for consistency.

### 1.5 Elevation / Shadows
- Minimal explicit shadow usage (mostly relying on neutral, flat design). If shadows are added later, codify tokens: `--shadow-sm`, `--shadow-md` to keep theme consistency.

## 2. Component Inventory & Patterns

| Component Category | Status | Notes |
| ------------------ | ------ | ----- |
| Button | Implemented | Consistent variants; sometimes raw `<button>` appears (offline page pre-refactor). Use `<Button>` uniformly. |
| Card | Implemented | Proper `CardHeader`, `CardContent`; ensure headings always in `CardHeader`. |
| Dialog / Sheet | Present | Validate accessibility of triggers (aria labels). |
| Form (Input, Textarea, Select, Switch, Radio, Checkbox) | Implemented | Consider shared form field wrapper for labels, descriptions, errors. |
| Table | Implemented | For large data (invoices, bookings), consider responsive pattern (stack rows on small screens). |
| Tabs | Implemented | Good for detail sub-sections if needed (property analytics). |
| Pagination | Implemented | Standardize its placement & spacing (always below lists, `mt-6`). |
| Toast / Toaster | Present (`toast`, `sonner`) | Unify on a single toast system to avoid divergence. |
| Tooltip | Implemented | Ensure used for icon-only buttons (archive, call, video). |
| Skeleton | Present | Booking, messages use; unify height / radius guidelines. |
| ScrollArea | Present | Chat & conversation list employ native scroll; consider ScrollArea for consistency. |
| Badge | Present | Unify badge color for unread counts (currently default). |
| Avatar | Present | Initial fallback pattern correct. |
| Progress | Present | Not widely used yet; can show loading states (invoice generation). |
| Separator | Present | Use instead of manual borders where semantic separation. |
| Resizable | Present | Not widely used; remove if not needed to reduce bundle. |
| Accordion | (Keyframes exist) | If unused, optionally prune to reduce CSS. |

## 3. Consistency Findings

| Issue | Example | Recommendation |
| ----- | ------- | ------------- |
| Mixed button usage (native vs shadcn) | (Earlier offline page) | Always use `<Button>` for theming & variants. |
| Inline arbitrary color classes | `text-green-500` for online status | Add semantic utility e.g. `.status-online` mapping to token or extend theme (`online: { DEFAULT: ... }`). |
| Variable paddings in cards | Payment summary vs property details | Adopt spacing tiers; document defaults (cards: `p-6`, nested groups: `p-4`). |
| Mixed toast systems | `useToast` vs `sonner` | Choose one (likely shadcn toast) and wrap for domain events. |
| Hard-coded locale formatting | `toLocaleDateString('ro-RO')` | Use centralized date formatter + i18n for locale detection. |
| Duplicate JSON-LD adapter ad‑hoc shapes | Adapter added (fixed) | Keep adapter file for shape transforms if more SEO endpoints arrive. |

## 4. Accessibility Enhancements
- Chat: Good use of `role="log"` and `aria-live="polite"`. Add `aria-label` to file attach button referencing count when files selected.
- Buttons with only icons: Ensure `aria-label` present (some archive/call/video already ok; verify all). 
- Form fields: Introduce shared `<FormField>` composition with `aria-describedby` linking error/helper text.
- Color contrast: Confirm primary on background meets 4.5:1 in both light and dark (slate base generally ok; verify destructive + amber warnings).
- Focus outlines: Rely on Tailwind ring tokens; ensure `focus-visible` states applied to interactive items in conversation list.

## 5. Performance & DX
- Consider tree-shaking unused components by removing unreferenced UI wrappers (Resizable, Accordion if not used).
- Memoize heavy lists (conversation list) or window virtualization if message volumes scale.
- Provide `index.ts` barrel in `ui` for curated exports (optional; weigh against treeshake purity).

## 6. Theming & Dark Mode
- Dark mode ready via `class` toggle; verify root layout toggles theme (if not, add simple ThemeProvider + toggle button in settings/profile).
- Add documentation snippet on extending palette (how to safely introduce `success` color without breaking contrast).

## 7. Missing / Future Components
- Breadcrumb: Reusable `<Breadcrumb>` for property + nested pages (smart locks, reviews) rather than manual headings.
- DataTable abstraction: For bookings, invoices; wrap table with sorting, empty state, pagination.
- EmptyState component: Standardize icon + title + description + primary action.
- Inline Status Pill: For booking/payment statuses with semantic mapping (pending, confirmed, paid, cancelled) using consistent color token mapping.
- DateRangePicker wrapper: unify date picking experience across booking search and host dashboards.

## 8. Proposed Action Plan
| Priority | Action | Effort | Impact |
| -------- | ------ | ------ | ------ |
| High | Introduce spacing + radius usage guidelines in code (add docs + examples) | Low | High consistency |
| High | Unify toast system | Low | Medium clarity |
| Medium | Add semantic status tokens (success, warning, info) | Low | Medium visual coherence |
| Medium | Create EmptyState + StatusPill components | Medium | High reuse |
| Medium | Add Breadcrumb component | Medium | UX clarity |
| Low | Prune unused UI components | Low | Small bundle savings |
| Low | Add ThemeProvider & dark toggle | Medium | UX / accessibility |

## 9. Code Snippets (Examples)

Semantic status utilities (`globals.css`):
```css
@layer utilities {
  .status-online { @apply text-emerald-500; }
  .status-offline { @apply text-gray-400; }
  .status-pending { @apply bg-amber-100 text-amber-800; }
  .status-confirmed { @apply bg-primary/10 text-primary; }
}
```

Spacing tokens (documentation only):
```
Surface Card: p-6
Nested Section / Sidebar: p-4
Compact Inline Blocks: p-3
Page Vertical Rhythm: py-8 md:py-10
```

EmptyState skeleton pattern:
```tsx
export function EmptyState({ icon: Icon, title, description, action }: { icon: React.ComponentType<any>; title: string; description: string; action?: React.ReactNode }) {
  return (
    <div className="flex flex-col items-center text-center gap-3 py-12">
      <Icon className="h-12 w-12 text-muted-foreground" />
      <h3 className="text-lg font-semibold">{title}</h3>
      <p className="text-sm text-muted-foreground max-w-md">{description}</p>
      {action}
    </div>
  );
}
```

StatusPill example:
```tsx
export function StatusPill({ status }: { status: 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'paid' }) {
  const map: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-800',
    confirmed: 'bg-blue-100 text-blue-700',
    cancelled: 'bg-red-100 text-red-700',
    completed: 'bg-emerald-100 text-emerald-700',
    paid: 'bg-emerald-100 text-emerald-700'
  };
  return <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${map[status]}`}>{status}</span>;
}
```

## 10. Summary
The design system foundation is solid: semantic color tokens, consistent component library (shadcn/ui), and i18n integration. Main opportunities are codifying spacing, status semantics, and consolidating feedback mechanisms. Implementing the proposed action plan will reduce visual drift, ease onboarding, and prepare for higher-scale feature additions.

---
Generated as part of the frontend completion initiative.
