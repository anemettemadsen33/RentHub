import { ThemeToggle } from '@/components/ThemeToggle'

export default function Home() {
  return (
    <div className="min-h-screen bg-background text-foreground">
      {/* Header with Theme Toggle */}
      <header className="sticky top-0 z-50 border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div className="container mx-auto px-4 h-16 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="h-10 w-10 rounded-lg bg-gradient-to-br from-primary to-primary/70 flex items-center justify-center text-primary-foreground font-bold shadow-lg shadow-primary/20">R</div>
            <div>
              <span className="font-bold text-xl">RentHub</span>
              <p className="text-xs text-muted-foreground">Shadcn/ui Theme Demo</p>
            </div>
          </div>
          <ThemeToggle />
        </div>
      </header>

      {/* Hero Section */}
      <section className="container mx-auto px-4 py-16 text-center">
        <div className="max-w-4xl mx-auto space-y-6">
          <div className="inline-block rounded-full bg-primary/10 px-4 py-1.5 text-sm font-medium text-primary mb-4">
            Complete shadcn/ui Theme Implementation ✨
          </div>
          <h1 className="text-4xl sm:text-5xl md:text-6xl font-bold tracking-tight">
            Professional Theme System<br />
            <span className="text-primary">Ready for Production</span>
          </h1>
          <p className="text-lg sm:text-xl text-muted-foreground max-w-2xl mx-auto">
            Full implementation of shadcn/ui design system with all color tokens, sidebar support, and seamless light/dark mode switching
          </p>
          <div className="flex flex-wrap justify-center gap-3 pt-4">
            <button className="inline-flex h-11 items-center justify-center rounded-md bg-primary px-6 text-sm font-medium text-primary-foreground shadow-lg shadow-primary/25 transition-all hover:bg-primary/90 hover:shadow-xl hover:shadow-primary/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
              Primary Button
            </button>
            <button className="inline-flex h-11 items-center justify-center rounded-md bg-secondary px-6 text-sm font-medium text-secondary-foreground transition-colors hover:bg-secondary/80 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
              Secondary Button
            </button>
            <button className="inline-flex h-11 items-center justify-center rounded-md border border-input bg-background px-6 text-sm font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
              Outline Button
            </button>
          </div>
        </div>
      </section>

      {/* Color Palette Showcase */}
      <section className="container mx-auto px-4 py-12">
        <div className="max-w-6xl mx-auto">
          <h2 className="text-3xl font-bold mb-8 text-center">Complete Color System</h2>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div className="space-y-3">
              <div className="h-32 rounded-lg bg-primary shadow-lg flex items-center justify-center">
                <span className="text-primary-foreground font-semibold">Primary</span>
              </div>
              <p className="text-sm text-center text-muted-foreground font-medium">Main brand color</p>
            </div>
            <div className="space-y-3">
              <div className="h-32 rounded-lg bg-secondary shadow-lg flex items-center justify-center">
                <span className="text-secondary-foreground font-semibold">Secondary</span>
              </div>
              <p className="text-sm text-center text-muted-foreground font-medium">Secondary actions</p>
            </div>
            <div className="space-y-3">
              <div className="h-32 rounded-lg bg-accent border border-border shadow-lg flex items-center justify-center">
                <span className="text-accent-foreground font-semibold">Accent</span>
              </div>
              <p className="text-sm text-center text-muted-foreground font-medium">Hover states</p>
            </div>
            <div className="space-y-3">
              <div className="h-32 rounded-lg bg-muted border border-border shadow-lg flex items-center justify-center">
                <span className="text-muted-foreground font-semibold">Muted</span>
              </div>
              <p className="text-sm text-center text-muted-foreground font-medium">Subtle backgrounds</p>
            </div>
          </div>
        </div>
      </section>

      {/* Component Showcase */}
      <section className="container mx-auto px-4 py-12">
        <div className="max-w-6xl mx-auto">
          <h2 className="text-3xl font-bold mb-8 text-center">UI Components</h2>
          <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            {/* Card 1 */}
            <div className="rounded-xl border border-border bg-card p-6 shadow-md hover:shadow-xl transition-shadow">
              <div className="flex items-start gap-4">
                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                  <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <div>
                  <h3 className="text-lg font-semibold mb-2 text-card-foreground">Card Component</h3>
                  <p className="text-sm text-muted-foreground">Styled with card background and proper borders</p>
                </div>
              </div>
            </div>

            {/* Card 2 */}
            <div className="rounded-xl border border-border bg-card p-6 shadow-md hover:shadow-xl transition-shadow">
              <div className="flex items-start gap-4">
                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                  <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <h3 className="text-lg font-semibold mb-2 text-card-foreground">Theme Tokens</h3>
                  <p className="text-sm text-muted-foreground">All CSS variables properly configured</p>
                </div>
              </div>
            </div>

            {/* Card 3 */}
            <div className="rounded-xl border border-border bg-card p-6 shadow-md hover:shadow-xl transition-shadow">
              <div className="flex items-start gap-4">
                <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                  <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                  </svg>
                </div>
                <div>
                  <h3 className="text-lg font-semibold mb-2 text-card-foreground">Dark Mode</h3>
                  <p className="text-sm text-muted-foreground">Seamless theme switching support</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Features Grid */}
      <section className="container mx-auto px-4 py-12 bg-muted/30">
        <div className="max-w-5xl mx-auto">
          <h2 className="text-3xl font-bold mb-8 text-center">Theme Features</h2>
          <div className="grid gap-4 md:grid-cols-2">
            <div className="flex items-start gap-4 p-6 rounded-lg bg-card border border-border shadow-sm">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground font-bold">✓</div>
              <div>
                <h3 className="font-semibold text-lg mb-1">Complete CSS Variables</h3>
                <p className="text-sm text-muted-foreground">All shadcn/ui color tokens: background, foreground, card, popover, primary, secondary, muted, accent, destructive, border, input, ring</p>
              </div>
            </div>
            <div className="flex items-start gap-4 p-6 rounded-lg bg-card border border-border shadow-sm">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground font-bold">✓</div>
              <div>
                <h3 className="font-semibold text-lg mb-1">Sidebar Colors (New)</h3>
                <p className="text-sm text-muted-foreground">8 sidebar-specific variables for navigation: sidebar-background, sidebar-foreground, sidebar-primary, sidebar-accent, and more</p>
              </div>
            </div>
            <div className="flex items-start gap-4 p-6 rounded-lg bg-card border border-border shadow-sm">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground font-bold">✓</div>
              <div>
                <h3 className="font-semibold text-lg mb-1">Chart Colors</h3>
                <p className="text-sm text-muted-foreground">5 chart color variables (chart-1 through chart-5) for data visualization and dashboards</p>
              </div>
            </div>
            <div className="flex items-start gap-4 p-6 rounded-lg bg-card border border-border shadow-sm">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground font-bold">✓</div>
              <div>
                <h3 className="font-semibold text-lg mb-1">Dark Mode Support</h3>
                <p className="text-sm text-muted-foreground">All colors properly inverted for dark theme with excellent contrast ratios and readability</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Interactive Demo */}
      <section className="container mx-auto px-4 py-12">
        <div className="max-w-4xl mx-auto">
          <div className="rounded-xl border-2 border-primary/20 bg-card p-8 shadow-2xl">
            <div className="flex items-center gap-3 mb-6">
              <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/70 text-primary-foreground text-2xl shadow-lg">
                🎨
              </div>
              <div>
                <h2 className="text-2xl font-bold">Shadcn/ui Theme System</h2>
                <p className="text-sm text-muted-foreground">Production-ready implementation</p>
              </div>
            </div>
            <p className="text-muted-foreground mb-6 leading-relaxed">
              This is a complete implementation of the shadcn/ui design system with all official theme tokens. Toggle between light and dark modes using the button in the header to see the seamless transitions.
            </p>
            <div className="grid gap-3 sm:grid-cols-2 mb-6">
              <div className="p-4 rounded-lg bg-primary/5 border border-primary/20">
                <p className="text-sm font-medium mb-1">Tailwind Integration</p>
                <p className="text-xs text-muted-foreground">Use classes like bg-primary, text-card-foreground, border-sidebar-border</p>
              </div>
              <div className="p-4 rounded-lg bg-primary/5 border border-primary/20">
                <p className="text-sm font-medium mb-1">HSL Color Format</p>
                <p className="text-xs text-muted-foreground">All colors use HSL for easy customization and manipulation</p>
              </div>
            </div>
            <div className="flex flex-wrap gap-2">
              <span className="inline-flex items-center rounded-full bg-primary px-3 py-1 text-xs font-medium text-primary-foreground">Production Ready</span>
              <span className="inline-flex items-center rounded-full bg-secondary px-3 py-1 text-xs font-medium text-secondary-foreground">Fully Typed</span>
              <span className="inline-flex items-center rounded-full border border-border bg-background px-3 py-1 text-xs font-medium">Accessible</span>
            </div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-border bg-card/80 backdrop-blur mt-12">
        <div className="container mx-auto px-4 py-12">
          <div className="max-w-6xl mx-auto grid gap-8 md:grid-cols-3">
            <div>
              <div className="flex items-center gap-2 mb-4">
                <div className="h-8 w-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold">R</div>
                <span className="font-bold text-lg">RentHub</span>
              </div>
              <p className="text-sm text-muted-foreground">Professional theme implementation showcasing shadcn/ui design system</p>
            </div>
            <div>
              <h4 className="font-semibold mb-4">Theme Info</h4>
              <ul className="space-y-2 text-sm text-muted-foreground">
                <li>• Complete color system</li>
                <li>• 16 sidebar variables</li>
                <li>• 5 chart colors</li>
                <li>• Full dark mode support</li>
              </ul>
            </div>
            <div>
              <h4 className="font-semibold mb-4">Technologies</h4>
              <ul className="space-y-2 text-sm text-muted-foreground">
                <li>• Next.js 16</li>
                <li>• Tailwind CSS 4</li>
                <li>• shadcn/ui tokens</li>
                <li>• HSL color variables</li>
              </ul>
            </div>
          </div>
          <div className="mt-8 pt-8 border-t border-border text-center text-sm text-muted-foreground">
            <p>© 2024 RentHub. Built with Next.js and shadcn/ui complete theme system.</p>
          </div>
        </div>
      </footer>
    </div>
  )
}
