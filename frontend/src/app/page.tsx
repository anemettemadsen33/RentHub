import Link from 'next/link'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Header } from '@/components/layout/Header'
import { 
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
    <div className="min-h-screen">
      <Header />
      
      {/* Hero Section - Modern with gradient background */}
      <section className="relative overflow-hidden bg-gradient-to-b from-muted/50 via-muted/30 to-background">
        {/* Background decoration */}
        <div className="absolute inset-0 bg-grid-slate-100 [mask-image:linear-gradient(0deg,transparent,black)] dark:bg-grid-slate-700/25" />
        
        <div className="container relative mx-auto px-4 py-24 md:py-32 lg:py-40">
          <div className="flex flex-col items-center text-center space-y-8 max-w-4xl mx-auto">
            {/* Badge */}
            <Badge variant="secondary" className="px-4 py-1.5 text-sm shadow-sm">
              <Sparkles className="w-3.5 h-3.5 mr-1.5 inline" />
              Trusted by 50,000+ users
            </Badge>
            
            {/* Heading with gradient */}
            <h1 className="text-5xl sm:text-6xl md:text-7xl font-extrabold tracking-tight">
              Find Your Perfect
              <br />
              <span className="bg-gradient-to-r from-primary via-blue-600 to-violet-600 bg-clip-text text-transparent">
                Rental Home
              </span>
            </h1>
            
            {/* Subheading */}
            <p className="text-xl md:text-2xl text-muted-foreground max-w-2xl">
              Discover verified properties, connect with trusted landlords, and find your ideal rental in minutes.
            </p>
            
            {/* CTA Buttons */}
            <div className="flex flex-col sm:flex-row gap-4 pt-4">
              <Link href="/properties">
                <Button size="lg" className="h-12 px-8 text-base shadow-lg hover:shadow-xl transition-all">
                  Browse Properties
                  <ArrowRight className="ml-2 h-5 w-5" />
                </Button>
              </Link>
              <Link href="/auth/register">
                <Button size="lg" variant="outline" className="h-12 px-8 text-base border-2">
                  Get Started Free
                </Button>
              </Link>
            </div>
            
            {/* Stats - Improved with gradient text */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-12 pt-16 w-full">
              {stats.map((stat, index) => (
                <div key={index} className="space-y-2">
                  <div className="text-4xl md:text-5xl font-extrabold bg-gradient-to-br from-primary to-violet-600 bg-clip-text text-transparent">
                    {stat.value}
                  </div>
                  <div className="text-sm font-medium text-muted-foreground uppercase tracking-wide">
                    {stat.label}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </div>
      </section>

      {/* Features Section - Enhanced cards with better shadows */}
      <section className="py-24 md:py-32 bg-background">
        <div className="container mx-auto px-4">
          {/* Section Header */}
          <div className="text-center space-y-4 mb-16">
            <h2 className="text-3xl md:text-5xl font-bold tracking-tight">
              Why Choose RentHub?
            </h2>
            <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
              Everything you need to find and rent your perfect property
            </p>
          </div>

          {/* Feature Cards - Enhanced with better visual hierarchy */}
          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            {features.map((feature, index) => {
              const Icon = feature.icon
              return (
                <Card key={index} className="p-6 hover:shadow-lg transition-all duration-300 border-2 hover:border-primary/50">
                  <div className="space-y-4">
                    {/* Icon with gradient background */}
                    <div className="w-12 h-12 rounded-xl bg-gradient-to-br from-primary/20 to-violet-500/20 flex items-center justify-center">
                      <Icon className="w-6 h-6 text-primary" />
                    </div>
                    
                    {/* Title and Description */}
                    <div className="space-y-2">
                      <h3 className="text-xl font-semibold">{feature.title}</h3>
                      <p className="text-muted-foreground leading-relaxed">
                        {feature.description}
                      </p>
                    </div>
                  </div>
                </Card>
              )
            })}
          </div>
        </div>
      </section>

      {/* CTA Section - Enhanced with gradient border */}
      <section className="py-24 md:py-32 bg-muted/50">
        <div className="container mx-auto px-4">
          <Card className="border-2 shadow-xl overflow-hidden">
            <div className="p-12 md:p-16 text-center space-y-6 bg-gradient-to-br from-background via-muted/30 to-background">
              <h2 className="text-3xl md:text-5xl font-bold tracking-tight">
                Ready to Find Your New Home?
              </h2>
              <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
                Join thousands of satisfied tenants who found their perfect rental on RentHub
              </p>
              
              <div className="flex flex-col sm:flex-row gap-4 justify-center pt-6">
                <Link href="/auth/register">
                  <Button size="lg" className="h-12 px-8 text-base shadow-lg hover:shadow-xl transition-all">
                    <CheckCircle className="mr-2 h-5 w-5" />
                    Sign Up Now
                  </Button>
                </Link>
                <Link href="/properties">
                  <Button size="lg" variant="outline" className="h-12 px-8 text-base border-2">
                    View Properties
                  </Button>
                </Link>
              </div>
            </div>
          </Card>
        </div>
      </section>

      {/* Footer - Clean and simple */}
      <footer className="border-t bg-muted/30">
        <div className="container mx-auto px-4 py-8">
          <div className="flex flex-col md:flex-row justify-between items-center gap-4">
            <div className="flex items-center gap-2">
              <Building2 className="h-5 w-5 text-primary" />
              <span className="font-semibold text-lg">RentHub</span>
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
