import { ThemeToggle } from '@/components/ThemeToggle'

export default function Home() {
  return (
    <div className="min-h-screen bg-background">
      {/* Header with Theme Toggle */}
      <header className="border-b border-border bg-card/50 backdrop-blur supports-[backdrop-filter]:bg-card/60">
        <div className="container mx-auto px-4 h-16 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="h-8 w-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold">R</div>
            <span className="font-bold text-xl">RentHub</span>
          </div>
          <ThemeToggle />
        </div>
      </header>

      {/* Hero Section */}
      <section className="container mx-auto px-4 py-20 text-center">
        <div className="max-w-3xl mx-auto space-y-6">
          <h1 className="text-5xl sm:text-6xl md:text-7xl font-bold tracking-tight">
            Welcome to{' '}
            <span className="text-primary bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">
              RentHub
            </span>
          </h1>
          <p className="text-xl sm:text-2xl text-muted-foreground">
            Your perfect property rental platform with modern design
          </p>
          <div className="flex flex-wrap justify-center gap-4 pt-4">
            <a
              href="/properties"
              className="inline-flex h-11 items-center justify-center rounded-md bg-primary px-8 text-sm font-medium text-primary-foreground shadow transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
            >
              Browse Properties
            </a>
            <a
              href="/auth/login"
              className="inline-flex h-11 items-center justify-center rounded-md border border-input bg-background px-8 text-sm font-medium shadow-sm transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50"
            >
              Sign In
            </a>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="container mx-auto px-4 py-16">
        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3 max-w-5xl mx-auto">
          {/* Feature Card 1 */}
          <div className="group relative overflow-hidden rounded-lg border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
            <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary mb-4">
              <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </div>
            <h3 className="text-xl font-semibold mb-2">Find Properties</h3>
            <p className="text-muted-foreground text-sm leading-relaxed">
              Browse through thousands of verified rental properties with detailed information and high-quality photos.
            </p>
          </div>

          {/* Feature Card 2 */}
          <div className="group relative overflow-hidden rounded-lg border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
            <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary mb-4">
              <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <h3 className="text-xl font-semibold mb-2">Easy Search</h3>
            <p className="text-muted-foreground text-sm leading-relaxed">
              Filter properties by location, price range, number of rooms, and amenities to find your perfect match.
            </p>
          </div>

          {/* Feature Card 3 */}
          <div className="group relative overflow-hidden rounded-lg border border-border bg-card p-6 shadow-sm transition-all hover:shadow-md">
            <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10 text-primary mb-4">
              <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
              </svg>
            </div>
            <h3 className="text-xl font-semibold mb-2">Trusted Reviews</h3>
            <p className="text-muted-foreground text-sm leading-relaxed">
              Read authentic reviews from real tenants and make informed decisions about your next rental property.
            </p>
          </div>
        </div>
      </section>

      {/* Theme Demo Section */}
      <section className="container mx-auto px-4 py-16">
        <div className="max-w-3xl mx-auto">
          <div className="rounded-lg border border-border bg-card p-8 shadow-lg">
            <div className="flex items-center gap-3 mb-6">
              <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary text-2xl">
                🎨
              </div>
              <h2 className="text-3xl font-bold">Shadcn/ui Blue Theme</h2>
            </div>
            <p className="text-muted-foreground mb-6 leading-relaxed">
              This page showcases the official shadcn/ui blue theme with proper styling, shadows, and modern design elements. Toggle between light and dark modes using the button in the header.
            </p>
            <div className="space-y-3">
              <div className="flex items-start gap-3 p-3 rounded-md bg-primary/5">
                <div className="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs">✓</div>
                <div>
                  <p className="font-medium">CSS Variables Configured</p>
                  <p className="text-sm text-muted-foreground">Full theme system with HSL color variables</p>
                </div>
              </div>
              <div className="flex items-start gap-3 p-3 rounded-md bg-primary/5">
                <div className="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs">✓</div>
                <div>
                  <p className="font-medium">Light & Dark Mode Support</p>
                  <p className="text-sm text-muted-foreground">Seamless theme switching with localStorage persistence</p>
                </div>
              </div>
              <div className="flex items-start gap-3 p-3 rounded-md bg-primary/5">
                <div className="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-primary-foreground text-xs">✓</div>
                <div>
                  <p className="font-medium">Modern UI Components</p>
                  <p className="text-sm text-muted-foreground">Cards, buttons, and layouts styled with theme colors</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t border-border bg-card/50 mt-16">
        <div className="container mx-auto px-4 py-8 text-center text-sm text-muted-foreground">
          <p>© 2024 RentHub. Built with Next.js and shadcn/ui blue theme.</p>
        </div>
      </footer>
    </div>
  )
}
