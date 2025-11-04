import { ThemeToggle } from '@/components/ThemeToggle'

export default function Home() {
  return (
    <div className="min-h-screen bg-background">
      <div className="fixed top-4 right-4 z-50">
        <ThemeToggle />
      </div>
      <div className="min-h-screen bg-background">
        <div className="container mx-auto px-4 py-16">
          <div className="text-center">
          <h1 className="text-6xl font-bold text-foreground mb-4">
            Welcome to <span className="text-primary">RentHub</span>
          </h1>
          <p className="text-xl text-muted-foreground mb-8">
            Your perfect property rental platform
          </p>
          
          <div className="flex justify-center gap-4 mb-16">
            <a
              href="/properties"
              className="px-8 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
            >
              Browse Properties
            </a>
            <a
              href="/auth/login"
              className="px-8 py-3 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/80 transition-colors"
            >
              Sign In
            </a>
          </div>

          <div className="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <div className="p-6 bg-card text-card-foreground rounded-lg shadow-sm border border-border">
              <div className="text-4xl mb-4">🏠</div>
              <h3 className="text-xl font-semibold mb-2">Find Properties</h3>
              <p className="text-muted-foreground">
                Browse through thousands of verified rental properties
              </p>
            </div>

            <div className="p-6 bg-card text-card-foreground rounded-lg shadow-sm border border-border">
              <div className="text-4xl mb-4">🔍</div>
              <h3 className="text-xl font-semibold mb-2">Easy Search</h3>
              <p className="text-muted-foreground">
                Filter by location, price, and amenities
              </p>
            </div>

            <div className="p-6 bg-card text-card-foreground rounded-lg shadow-sm border border-border">
              <div className="text-4xl mb-4">⭐</div>
              <h3 className="text-xl font-semibold mb-2">Trusted Reviews</h3>
              <p className="text-muted-foreground">
                Read authentic reviews from real tenants
              </p>
            </div>
          </div>

          <div className="mt-16 p-6 bg-muted rounded-lg max-w-2xl mx-auto border border-border">
            <h2 className="text-2xl font-bold mb-4">🎉 Setup Complete!</h2>
            <div className="text-left space-y-2">
              <p className="flex items-center gap-2">
                <span className="text-green-600 dark:text-green-400">✓</span>
                <span>Backend API running on http://localhost:8000</span>
              </p>
              <p className="flex items-center gap-2">
                <span className="text-green-600 dark:text-green-400">✓</span>
                <span>Frontend running on http://localhost:3000</span>
              </p>
              <p className="flex items-center gap-2">
                <span className="text-primary">→</span>
                <span>Admin Panel: http://localhost:8000/admin</span>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
