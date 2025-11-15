import { Metadata } from 'next';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Building2, Users, MapPin, Shield, Search, Star, TrendingUp, CheckCircle } from 'lucide-react';
import Link from 'next/link';
import Image from 'next/image';
import { Footer } from '@/components/footer';

export const metadata: Metadata = {
  title: 'RentHub - Modern Property Rental Platform',
  description: 'Find and book your perfect rental property',
};

export default function HomePage() {
  return (
    <div className="min-h-screen bg-gradient-to-b from-background to-muted/20">
      {/* Simple Header */}
      <header className="border-b bg-background/95 backdrop-blur">
        <div className="container mx-auto px-4 py-4 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Building2 className="h-6 w-6 text-primary" />
            <span className="font-bold text-xl">RentHub</span>
          </div>
          <nav className="flex items-center gap-4">
            <Link href="/about" className="text-sm hover:text-primary">About</Link>
            <Link href="/contact" className="text-sm hover:text-primary">Contact</Link>
            <Button asChild size="sm">
              <Link href="/auth/login">Login</Link>
            </Button>
          </nav>
        </div>
      </header>

      {/* Hero Section */}
      <section className="container mx-auto px-4 py-20 text-center">
        <h1 className="text-4xl md:text-6xl font-bold mb-6">
          Find Your Perfect Rental
        </h1>
        <p className="text-xl text-muted-foreground mb-8 max-w-2xl mx-auto">
          Discover thousands of verified properties in cities around the world
        </p>
        <div className="flex flex-col sm:flex-row gap-4 justify-center">
          <Button asChild size="lg">
            <Link href="/properties">Browse Properties</Link>
          </Button>
          <Button asChild size="lg" variant="outline">
            <Link href="/auth/register">Get Started</Link>
          </Button>
        </div>
      </section>

      {/* Stats Section */}
      <section className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
          <Card>
            <CardContent className="p-6 text-center">
              <Building2 className="h-8 w-8 mx-auto mb-2 text-primary" />
              <div className="text-2xl font-bold">12,345</div>
              <div className="text-sm text-muted-foreground">Properties</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6 text-center">
              <Users className="h-8 w-8 mx-auto mb-2 text-primary" />
              <div className="text-2xl font-bold">45,678</div>
              <div className="text-sm text-muted-foreground">Happy Tenants</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6 text-center">
              <MapPin className="h-8 w-8 mx-auto mb-2 text-primary" />
              <div className="text-2xl font-bold">150+</div>
              <div className="text-sm text-muted-foreground">Cities</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6 text-center">
              <Shield className="h-8 w-8 mx-auto mb-2 text-primary" />
              <div className="text-2xl font-bold">8,920</div>
              <div className="text-sm text-muted-foreground">Verified Hosts</div>
            </CardContent>
          </Card>
        </div>
      </section>

      {/* Features Section */}
      <section className="container mx-auto px-4 py-16">
        <h2 className="text-3xl font-bold text-center mb-12">Why Choose RentHub?</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <Card>
            <CardContent className="p-6">
              <Search className="h-12 w-12 text-primary mb-4" />
              <h3 className="text-xl font-semibold mb-2">Easy Search</h3>
              <p className="text-muted-foreground">
                Advanced filters to find exactly what you're looking for. Search by location, price, amenities, and more.
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <Shield className="h-12 w-12 text-primary mb-4" />
              <h3 className="text-xl font-semibold mb-2">Verified Listings</h3>
              <p className="text-muted-foreground">
                All properties are verified by our team. Photos, descriptions, and reviews you can trust.
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <Star className="h-12 w-12 text-primary mb-4" />
              <h3 className="text-xl font-semibold mb-2">Real Reviews</h3>
              <p className="text-muted-foreground">
                Honest reviews from real tenants. Make informed decisions based on actual experiences.
              </p>
            </CardContent>
          </Card>
        </div>
      </section>

      {/* Partnerships Section */}
      <section className="container mx-auto px-4 py-16 bg-muted/30 rounded-lg">
        <div className="text-center mb-12">
          <h2 className="text-3xl font-bold mb-4">Our Global Partners</h2>
          <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
            We partner with the world's leading platforms to bring you the best rental options
          </p>
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
          {/* Airbnb Partnership */}
          <Card className="overflow-hidden hover:shadow-lg transition-shadow">
            <CardContent className="p-8 text-center">
              <div className="h-20 flex items-center justify-center mb-4">
                <svg className="h-12 w-auto" viewBox="0 0 1000 1000" fill="currentColor">
                  <path d="M499.3 736.7c-51-64-81-120.1-91-168.1-10-39-6-70 11-93 18-27 45-40 80-40s62 13 80 40c17 23 21 54 11 93-11 49-41 105-91 168.1zm362.2 43c-7 47-39 86-83 105-85 37-169.1-22-241.1-102 119.1-149.1 141.1-265.1 90-340.2-30-43-73-64-128.1-64-111 0-172.1 94-148.1 203.1 14 59 51 126.1 110 201.1-37 41-72 70-103 88-24 13-47 21-69 23-101 15-180.1-83-144.1-184.1 5-13 15-37 32-74l1-2c55-120.1 122.1-256.1 199.1-407.2l2-5 22-42c17-31 24-45 51-62 13-8 29-12 47-12 36 0 64 21 76 38 6 9 13 21 22 36l21 41 3 6c77 151.1 144.1 287.1 199.1 407.2l1 1 20 46 12 29c9.2 23.1 11.2 46.1 8.2 70.1zm46-90.1c-7-22-19-48-34-79v-1c-71-151.1-137.1-287.1-200.1-409.2l-4-6c-45-92-77-147.1-170.1-147.1-92 0-131.1 64-171.1 147.1l-3 6c-63 122.1-129.1 258.1-200.1 409.2v2l-21 46c-8 19-12 29-13 32-51 140.1 54 263.1 181.1 263.1 1 0 5 0 10-1h14c66-8 134.1-50 203.1-125.1 69 75 137.1 117.1 203.1 125.1h14c5 1 9 1 10 1 127.1 0 232.1-123 181.1-263.1z"/>
                </svg>
              </div>
              <h3 className="text-xl font-semibold mb-2">Airbnb</h3>
              <p className="text-sm text-muted-foreground mb-4">
                Access millions of unique homes and experiences worldwide
              </p>
              <div className="flex items-center justify-center gap-2 text-sm">
                <CheckCircle className="h-4 w-4 text-green-500" />
                <span>5M+ Properties</span>
              </div>
            </CardContent>
          </Card>

          {/* Booking.com Partnership */}
          <Card className="overflow-hidden hover:shadow-lg transition-shadow">
            <CardContent className="p-8 text-center">
              <div className="h-20 flex items-center justify-center mb-4">
                <div className="text-3xl font-bold text-blue-600">Booking.com</div>
              </div>
              <h3 className="text-xl font-semibold mb-2">Booking.com</h3>
              <p className="text-sm text-muted-foreground mb-4">
                Trusted accommodation booking platform for travelers
              </p>
              <div className="flex items-center justify-center gap-2 text-sm">
                <CheckCircle className="h-4 w-4 text-green-500" />
                <span>28M+ Listings</span>
              </div>
            </CardContent>
          </Card>

          {/* VRBO Partnership */}
          <Card className="overflow-hidden hover:shadow-lg transition-shadow">
            <CardContent className="p-8 text-center">
              <div className="h-20 flex items-center justify-center mb-4">
                <div className="text-3xl font-bold text-blue-700">VRBO</div>
              </div>
              <h3 className="text-xl font-semibold mb-2">Vrbo</h3>
              <p className="text-sm text-muted-foreground mb-4">
                Vacation rentals with whole homes for the whole family
              </p>
              <div className="flex items-center justify-center gap-2 text-sm">
                <CheckCircle className="h-4 w-4 text-green-500" />
                <span>2M+ Properties</span>
              </div>
            </CardContent>
          </Card>
        </div>

        <div className="text-center mt-12">
          <p className="text-muted-foreground mb-4">
            Seamlessly sync your listings across all platforms from one dashboard
          </p>
          <Button asChild variant="outline">
            <Link href="/integrations">Learn About Integrations</Link>
          </Button>
        </div>
      </section>

      {/* How It Works */}
      <section className="container mx-auto px-4 py-16">
        <h2 className="text-3xl font-bold text-center mb-12">How It Works</h2>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div className="text-center">
            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <span className="text-2xl font-bold text-primary">1</span>
            </div>
            <h3 className="font-semibold mb-2">Create Account</h3>
            <p className="text-sm text-muted-foreground">Sign up for free in under 2 minutes</p>
          </div>
          <div className="text-center">
            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <span className="text-2xl font-bold text-primary">2</span>
            </div>
            <h3 className="font-semibold mb-2">Search Properties</h3>
            <p className="text-sm text-muted-foreground">Browse thousands of verified listings</p>
          </div>
          <div className="text-center">
            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <span className="text-2xl font-bold text-primary">3</span>
            </div>
            <h3 className="font-semibold mb-2">Book Online</h3>
            <p className="text-sm text-muted-foreground">Secure booking with instant confirmation</p>
          </div>
          <div className="text-center">
            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <span className="text-2xl font-bold text-primary">4</span>
            </div>
            <h3 className="font-semibold mb-2">Move In</h3>
            <p className="text-sm text-muted-foreground">Get keys and enjoy your new home</p>
          </div>
        </div>
      </section>

      {/* Testimonials */}
      <section className="container mx-auto px-4 py-16 bg-muted/30 rounded-lg">
        <h2 className="text-3xl font-bold text-center mb-12">What Our Users Say</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <Card>
            <CardContent className="p-6">
              <div className="flex mb-4">
                {[...Array(5)].map((_, i) => (
                  <Star key={i} className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                ))}
              </div>
              <p className="text-muted-foreground mb-4">
                "RentHub made finding my apartment so easy! The search filters are amazing and all listings are verified."
              </p>
              <div className="font-semibold">Sarah Johnson</div>
              <div className="text-sm text-muted-foreground">New York, NY</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <div className="flex mb-4">
                {[...Array(5)].map((_, i) => (
                  <Star key={i} className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                ))}
              </div>
              <p className="text-muted-foreground mb-4">
                "As a property owner, the multi-platform sync saves me hours every week. Highly recommended!"
              </p>
              <div className="font-semibold">Michael Chen</div>
              <div className="text-sm text-muted-foreground">San Francisco, CA</div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="p-6">
              <div className="flex mb-4">
                {[...Array(5)].map((_, i) => (
                  <Star key={i} className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                ))}
              </div>
              <p className="text-muted-foreground mb-4">
                "Best rental platform I've used. The booking process is seamless and customer support is excellent."
              </p>
              <div className="font-semibold">Emma Davis</div>
              <div className="text-sm text-muted-foreground">Austin, TX</div>
            </CardContent>
          </Card>
        </div>
      </section>

      {/* CTA Section */}
      <section className="container mx-auto px-4 py-20">
        <Card className="overflow-hidden">
          <CardContent className="p-12 text-center space-y-6">
            <h2 className="text-3xl font-bold">Ready to Get Started?</h2>
            <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
              Join thousands of satisfied users and find your perfect rental today.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center pt-4">
              <Button asChild size="lg">
                <Link href="/auth/register">Create Free Account</Link>
              </Button>
              <Button asChild size="lg" variant="outline">
                <Link href="/contact">Contact Sales</Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </section>

      {/* Simple Footer */}
      <Footer />
    </div>
  );
}
