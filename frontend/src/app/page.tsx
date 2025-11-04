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
      <section className="relative overflow-hidden border-b">
        <div className="absolute inset-0 bg-gradient-to-br from-primary/10 via-background to-background" />
        <div className="container relative mx-auto px-4 py-20 md:py-32">
          <div className="flex flex-col items-center text-center space-y-8">
            <Badge variant="secondary" className="px-4 py-1.5">
              <Sparkles className="w-3 h-3 mr-1" />
              Trusted by 50,000+ users
            </Badge>
            
            <h1 className="text-4xl md:text-6xl lg:text-7xl font-bold tracking-tight">
              Find Your Perfect
              <span className="block mt-2 bg-gradient-to-r from-primary via-blue-600 to-primary bg-clip-text text-transparent">
                Rental Home
              </span>
            </h1>
            
            <p className="text-xl md:text-2xl text-muted-foreground max-w-2xl">
              Discover verified properties, connect with trusted landlords, and find your ideal rental in minutes.
            </p>

            <div className="flex flex-col sm:flex-row gap-4 pt-4">
              <Link href="/properties">
                <Button size="lg" className="text-lg px-8">
                  Browse Properties
                  <ArrowRight className="ml-2 h-5 w-5" />
                </Button>
              </Link>
              <Link href="/auth/register">
                <Button size="lg" variant="outline" className="text-lg px-8">
                  Get Started Free
                </Button>
              </Link>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-8 pt-12 w-full max-w-3xl">
              {stats.map((stat, index) => (
                <div key={index} className="space-y-1">
                  <div className="text-3xl md:text-4xl font-bold text-primary">{stat.value}</div>
                  <div className="text-sm text-muted-foreground">{stat.label}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20 md:py-32">
        <div className="container mx-auto px-4">
          <div className="text-center space-y-4 mb-16">
            <h2 className="text-3xl md:text-5xl font-bold">
              Why Choose RentHub?
            </h2>
            <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
              Everything you need to find and rent your perfect property
            </p>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {features.map((feature, index) => (
              <Card key={index} className="border-2 hover:border-primary/50 transition-all hover:shadow-lg">
                <CardHeader>
                  <div className="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center mb-4">
                    <feature.icon className="w-6 h-6 text-primary" />
                  </div>
                  <CardTitle>{feature.title}</CardTitle>
                  <CardDescription className="text-base">
                    {feature.description}
                  </CardDescription>
                </CardHeader>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="py-20 md:py-32 border-t bg-muted/50">
        <div className="container mx-auto px-4">
          <Card className="border-2 bg-gradient-to-br from-card to-card/50">
            <CardContent className="p-8 md:p-12 text-center space-y-6">
              <h2 className="text-3xl md:text-5xl font-bold">
                Ready to Find Your New Home?
              </h2>
              <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
                Join thousands of satisfied tenants who found their perfect rental on RentHub
              </p>
              
              <div className="flex flex-col sm:flex-row gap-4 justify-center pt-4">
                <Link href="/auth/register">
                  <Button size="lg" className="text-lg px-8">
                    <CheckCircle className="mr-2 h-5 w-5" />
                    Sign Up Now
                  </Button>
                </Link>
                <Link href="/properties">
                  <Button size="lg" variant="outline" className="text-lg px-8">
                    View Properties
                  </Button>
                </Link>
              </div>
            </CardContent>
          </Card>
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
