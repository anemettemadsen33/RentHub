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
  ArrowRight,
  Building2
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
        <div className="container relative mx-auto px-4 py-16 md:py-24 lg:py-32">
          <div className="flex flex-col items-center text-center space-y-6 max-w-5xl mx-auto">
            <Badge variant="outline" className="px-3 py-1">
              <Sparkles className="w-3 h-3 mr-1" />
              Trusted by 50,000+ users
            </Badge>
            
            <h1 className="text-4xl md:text-5xl lg:text-6xl font-bold tracking-tight">
              Find Your Perfect
              <br />
              <span className="text-primary">
                Rental Home
              </span>
            </h1>
            
            <p className="text-lg md:text-xl text-muted-foreground max-w-2xl leading-relaxed">
              Discover verified properties, connect with trusted landlords, and find your ideal rental in minutes.
            </p>

            <div className="flex flex-col sm:flex-row gap-3 pt-4">
              <Link href="/properties">
                <Button size="lg" className="min-w-[200px]">
                  Browse Properties
                  <ArrowRight className="ml-2 h-4 w-4" />
                </Button>
              </Link>
              <Link href="/auth/register">
                <Button size="lg" variant="outline" className="min-w-[200px]">
                  Get Started Free
                </Button>
              </Link>
            </div>

            {/* Stats */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-8 pt-12 w-full border-t mt-12">
              {stats.map((stat, index) => (
                <div key={index} className="space-y-1">
                  <div className="text-3xl md:text-4xl font-bold">{stat.value}</div>
                  <div className="text-sm text-muted-foreground">{stat.label}</div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-16 md:py-24">
        <div className="container mx-auto px-4">
          <div className="text-center space-y-3 mb-12">
            <h2 className="text-3xl md:text-4xl font-bold">
              Why Choose RentHub?
            </h2>
            <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
              Everything you need to find and rent your perfect property
            </p>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {features.map((feature, index) => (
              <Card key={index} className="relative hover:shadow-md transition-shadow">
                <CardHeader>
                  <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center mb-3">
                    <feature.icon className="w-5 h-5 text-primary" />
                  </div>
                  <CardTitle className="text-lg">{feature.title}</CardTitle>
                  <CardDescription className="text-sm">
                    {feature.description}
                  </CardDescription>
                </CardHeader>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* CTA Section */}
      <section className="border-t py-16 md:py-24">
        <div className="container mx-auto px-4">
          <Card className="border-2">
            <CardContent className="p-8 md:p-12 text-center space-y-6">
              <h2 className="text-3xl md:text-4xl font-bold">
                Ready to Find Your New Home?
              </h2>
              <p className="text-lg text-muted-foreground max-w-2xl mx-auto">
                Join thousands of satisfied tenants who found their perfect rental on RentHub
              </p>
              
              <div className="flex flex-col sm:flex-row gap-3 justify-center pt-4">
                <Link href="/auth/register">
                  <Button size="lg" className="min-w-[200px]">
                    <CheckCircle className="mr-2 h-4 w-4" />
                    Sign Up Now
                  </Button>
                </Link>
                <Link href="/properties">
                  <Button size="lg" variant="outline" className="min-w-[200px]">
                    View Properties
                  </Button>
                </Link>
              </div>
            </CardContent>
          </Card>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t py-8">
        <div className="container mx-auto px-4">
          <div className="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <div className="flex items-center space-x-2">
              <Building2 className="h-5 w-5" />
              <span className="font-semibold">RentHub</span>
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
