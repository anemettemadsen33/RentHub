import Link from 'next/link'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Header } from '@/components/layout/Header'
import { 
  Home, 
  Search, 
  Shield, 
  Star, 
  MapPin, 
  TrendingUp, 
  Users,
  CheckCircle,
  Sparkles,
  ArrowRight
} from 'lucide-react'

export default function HomePage() {
  const features = [
    {
      icon: Search,
      title: 'Smart Search',
      description: 'Advanced filters to find your perfect property in seconds'
    },
    {
      icon: Shield,
      title: 'Verified Listings',
      description: 'All properties are verified for authenticity and quality'
    },
    {
      icon: Star,
      title: 'Trusted Reviews',
      description: 'Real reviews from real tenants you can trust'
    },
    {
      icon: MapPin,
      title: 'Prime Locations',
      description: 'Properties in the best neighborhoods and areas'
    },
    {
      icon: TrendingUp,
      title: 'Best Prices',
      description: 'Competitive pricing with transparent costs'
    },
    {
      icon: Users,
      title: 'Community',
      description: 'Join thousands of happy tenants and landlords'
    }
  ]

  const stats = [
    { value: '10k+', label: 'Properties' },
    { value: '50k+', label: 'Happy Tenants' },
    { value: '4.9', label: 'Average Rating' },
    { value: '24/7', label: 'Support' }
  ]

  return (
    <div className="min-h-screen bg-background">
      <Header />
      
      {/* Hero Section */}
      <section className="relative overflow-hidden border-b bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-background dark:to-gray-900">
        <div className="absolute inset-0 bg-[linear-gradient(to_right,#8080800a_1px,transparent_1px),linear-gradient(to_bottom,#8080800a_1px,transparent_1px)] bg-[size:14px_24px]" />
        <div className="container relative mx-auto px-4 py-20 md:py-32">
          <div className="flex flex-col items-center text-center space-y-8">
            <Badge variant="secondary" className="px-4 py-2 text-sm font-semibold shadow-sm">
              <Sparkles className="w-4 h-4 mr-1.5" />
              Trusted by 50,000+ users
            </Badge>
            
            <h1 className="text-5xl md:text-6xl lg:text-7xl font-extrabold tracking-tight">
              Find Your Perfect
              <span className="block mt-3 bg-gradient-to-r from-blue-600 via-purple-600 to-blue-600 bg-clip-text text-transparent animate-gradient">
                Rental Home
              </span>
            </h1>
            
            <p className="text-xl md:text-2xl text-muted-foreground max-w-2xl">
              Discover verified properties, connect with trusted landlords, and find your ideal rental in minutes.
            </p>

            <div className="flex flex-col sm:flex-row gap-4 pt-6">
              <Link href="/properties">
                <Button size="lg" className="text-lg px-10 py-6 shadow-lg hover:shadow-xl transition-shadow">
                  Browse Properties
                  <ArrowRight className="ml-2 h-5 w-5" />
                </Button>
              </Link>
              <Link href="/auth/register">
                <Button size="lg" variant="outline" className="text-lg px-10 py-6 border-2 hover:bg-accent">
                  Get Started Free
                </Button>
              </Link>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-8 pt-16 w-full max-w-4xl">
              {stats.map((stat, index) => (
                <div key={index} className="space-y-2">
                  <div className="text-4xl md:text-5xl font-extrabold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">{stat.value}</div>
                  <div className="text-sm font-medium text-muted-foreground uppercase tracking-wide">{stat.label}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-24 md:py-32 bg-gradient-to-b from-white to-gray-50 dark:from-background dark:to-gray-900">
        <div className="container mx-auto px-4">
          <div className="text-center space-y-4 mb-20">
            <h2 className="text-4xl md:text-5xl font-extrabold">
              Why Choose RentHub?
            </h2>
            <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
              Everything you need to find and rent your perfect property
            </p>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {features.map((feature, index) => (
              <Card key={index} className="group relative border-2 hover:border-primary transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 bg-white dark:bg-card">
                <CardHeader className="p-8">
                  <div className="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg">
                    <feature.icon className="w-7 h-7 text-white" />
                  </div>
                  <CardTitle className="text-2xl mb-3">{feature.title}</CardTitle>
                  <CardDescription className="text-base leading-relaxed">
                    {feature.description}
                  </CardDescription>
                </CardHeader>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-24 md:py-32 bg-gradient-to-br from-blue-600 via-purple-600 to-blue-700 dark:from-blue-900 dark:via-purple-900 dark:to-blue-950">
        <div className="container mx-auto px-4">
          <div className="text-center space-y-8 text-white">
            <h2 className="text-4xl md:text-6xl font-extrabold">
              Ready to Find Your New Home?
            </h2>
            <p className="text-xl md:text-2xl opacity-90 max-w-2xl mx-auto leading-relaxed">
              Join thousands of satisfied tenants who found their perfect rental on RentHub
            </p>
            
            <div className="flex flex-col sm:flex-row gap-4 justify-center pt-6">
              <Link href="/auth/register">
                <Button size="lg" variant="secondary" className="text-lg px-10 py-6 bg-white text-blue-600 hover:bg-gray-100 shadow-xl">
                  <CheckCircle className="mr-2 h-5 w-5" />
                  Sign Up Now
                </Button>
              </Link>
              <Link href="/properties">
                <Button size="lg" variant="outline" className="text-lg px-10 py-6 border-2 border-white text-white hover:bg-white/10">
                  View Properties
                </Button>
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t py-12">
        <div className="container mx-auto px-4">
          <div className="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <div className="flex items-center space-x-2">
              <Home className="h-5 w-5 text-primary" />
              <span className="font-bold text-lg">RentHub</span>
            </div>
            <p className="text-sm text-muted-foreground">
              © 2024 RentHub. All rights reserved.
            </p>
          </div>
        </div>
      </footer>
    </div>
  )
}
