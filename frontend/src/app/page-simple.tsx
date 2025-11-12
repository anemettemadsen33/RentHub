import { Metadata } from 'next';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Building2, Users, MapPin, Shield } from 'lucide-react';
import Link from 'next/link';

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
      <footer className="border-t mt-20">
        <div className="container mx-auto px-4 py-8 text-center text-sm text-muted-foreground">
          <p>© 2025 RentHub. All rights reserved.</p>
        </div>
      </footer>
    </div>
  );
}
