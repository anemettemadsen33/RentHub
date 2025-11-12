import { Metadata } from 'next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import Link from 'next/link';
import { CheckCircle, Zap, TrendingUp, Globe, BarChart, Lock } from 'lucide-react';

export const metadata: Metadata = {
  title: 'Integrations - RentHub',
  description: 'Connect RentHub with your favorite platforms',
};

export default function IntegrationsPage() {
  return (
    <div className="min-h-screen bg-background">
      {/* Hero Section */}
      <section className="bg-gradient-to-b from-primary/10 to-background py-20">
        <div className="container mx-auto px-4">
          <div className="max-w-3xl mx-auto text-center">
            <h1 className="text-4xl md:text-5xl font-bold mb-6">Powerful Integrations</h1>
            <p className="text-xl text-muted-foreground">
              Connect RentHub with the world's leading platforms to streamline your property management
            </p>
          </div>
        </div>
      </section>

      {/* Benefits */}
      <section className="container mx-auto px-4 py-16">
        <h2 className="text-3xl font-bold text-center mb-12">Why Use Integrations?</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <Card>
            <CardHeader>
              <Zap className="h-12 w-12 text-primary mb-4" />
              <CardTitle>Save Time</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">
                Sync listings across multiple platforms with one click. No more manual updates.
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <TrendingUp className="h-12 w-12 text-primary mb-4" />
              <CardTitle>Increase Visibility</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">
                Reach millions more potential tenants by listing on Airbnb, Booking.com, and Vrbo.
              </p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <BarChart className="h-12 w-12 text-primary mb-4" />
              <CardTitle>Unified Analytics</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">
                Track performance across all platforms from one centralized dashboard.
              </p>
            </CardContent>
          </Card>
        </div>
      </section>

      {/* Main Integrations */}
      <section className="bg-muted/30 py-16">
        <div className="container mx-auto px-4">
          <h2 className="text-3xl font-bold text-center mb-12">Featured Integrations</h2>
          
          <div className="space-y-8 max-w-4xl mx-auto">
            {/* Airbnb */}
            <Card>
              <CardContent className="p-8">
                <div className="flex flex-col md:flex-row gap-6">
                  <div className="flex-shrink-0">
                    <div className="w-24 h-24 bg-gradient-to-br from-red-500 to-pink-500 rounded-lg flex items-center justify-center">
                      <svg className="h-16 w-16 text-white" viewBox="0 0 1000 1000" fill="currentColor">
                        <path d="M499.3 736.7c-51-64-81-120.1-91-168.1-10-39-6-70 11-93 18-27 45-40 80-40s62 13 80 40c17 23 21 54 11 93-11 49-41 105-91 168.1zm362.2 43c-7 47-39 86-83 105-85 37-169.1-22-241.1-102 119.1-149.1 141.1-265.1 90-340.2-30-43-73-64-128.1-64-111 0-172.1 94-148.1 203.1 14 59 51 126.1 110 201.1-37 41-72 70-103 88-24 13-47 21-69 23-101 15-180.1-83-144.1-184.1 5-13 15-37 32-74l1-2c55-120.1 122.1-256.1 199.1-407.2l2-5 22-42c17-31 24-45 51-62 13-8 29-12 47-12 36 0 64 21 76 38 6 9 13 21 22 36l21 41 3 6c77 151.1 144.1 287.1 199.1 407.2l1 1 20 46 12 29c9.2 23.1 11.2 46.1 8.2 70.1z"/>
                      </svg>
                    </div>
                  </div>
                  <div className="flex-grow">
                    <h3 className="text-2xl font-bold mb-2">Airbnb</h3>
                    <p className="text-muted-foreground mb-4">
                      Sync your properties with Airbnb's 5+ million listings worldwide. 
                      Automatic calendar sync, pricing updates, and booking management.
                    </p>
                    <ul className="space-y-2 mb-6">
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-5 w-5 text-green-500 mt-0.5" />
                        <span className="text-sm">Two-way calendar synchronization</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-5 w-5 text-green-500 mt-0.5" />
                        <span className="text-sm">Automatic pricing and availability updates</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-5 w-5 text-green-500 mt-0.5" />
                        <span className="text-sm">Unified messaging and booking management</span>
                      </li>
                    </ul>
                    <Button>Connect Airbnb</Button>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Booking.com */}
            <Card>
              <CardContent className="p-8">
                <div className="flex flex-col md:flex-row gap-6">
                  <div className="flex-shrink-0">
                    <div className="w-24 h-24 bg-gradient-to-br from-blue-600 to-blue-400 rounded-lg flex items-center justify-center">
                      <span className="text-2xl font-bold text-white">B.com</span>
                    </div>
                  </div>
                  <div className="flex-grow">
                    <h3 className="text-2xl font-bold mb-2">Booking.com</h3>
                    <p className="text-muted-foreground mb-4">
                      Reach 28+ million listings across 154,000+ destinations. 
                      Perfect for long-term and short-term rentals.
                    </p>
                    <ul className="space-y-2 mb-6">
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-5 w-5 text-green-500 mt-0.5" />
                        <span className="text-sm">Real-time inventory synchronization</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-5 w-5 text-green-500 mt-0.5" />
                        <span className="text-sm">Dynamic rate management</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-5 w-5 text-green-500 mt-0.5" />
                        <span className="text-sm">Booking confirmation automation</span>
                      </li>
                    </ul>
                    <Button>Connect Booking.com</Button>
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Vrbo */}
            <Card>
              <CardContent className="p-8">
                <div className="flex flex-col md:flex-row gap-6">
                  <div className="flex-shrink-0">
                    <div className="w-24 h-24 bg-gradient-to-br from-blue-700 to-blue-500 rounded-lg flex items-center justify-center">
                      <span className="text-2xl font-bold text-white">Vrbo</span>
                    </div>
                  </div>
                  <div className="flex-grow">
                    <h3 className="text-2xl font-bold mb-2">Vrbo (Vacation Rentals by Owner)</h3>
                    <p className="text-muted-foreground mb-4">
                      Connect with 2+ million vacation rental properties. 
                      Ideal for family-friendly whole-home rentals.
                    </p>
                    <ul className="space-y-2 mb-6">
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-5 w-5 text-green-500 mt-0.5" />
                        <span className="text-sm">Automated listing distribution</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-5 w-5 text-green-500 mt-0.5" />
                        <span className="text-sm">Payment processing integration</span>
                      </li>
                      <li className="flex items-start gap-2">
                        <CheckCircle className="h-5 w-5 text-green-500 mt-0.5" />
                        <span className="text-sm">Review management and sync</span>
                      </li>
                    </ul>
                    <Button>Connect Vrbo</Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </section>

      {/* How It Works */}
      <section className="container mx-auto px-4 py-16">
        <h2 className="text-3xl font-bold text-center mb-12">How Integration Works</h2>
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
          <div className="text-center">
            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <span className="text-2xl font-bold text-primary">1</span>
            </div>
            <h3 className="font-semibold mb-2">Choose Platform</h3>
            <p className="text-sm text-muted-foreground">
              Select the platforms you want to connect
            </p>
          </div>
          <div className="text-center">
            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <span className="text-2xl font-bold text-primary">2</span>
            </div>
            <h3 className="font-semibold mb-2">Authorize Access</h3>
            <p className="text-sm text-muted-foreground">
              Securely connect your accounts with OAuth
            </p>
          </div>
          <div className="text-center">
            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <span className="text-2xl font-bold text-primary">3</span>
            </div>
            <h3 className="font-semibold mb-2">Sync Properties</h3>
            <p className="text-sm text-muted-foreground">
              Import existing or push new listings
            </p>
          </div>
          <div className="text-center">
            <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <span className="text-2xl font-bold text-primary">4</span>
            </div>
            <h3 className="font-semibold mb-2">Manage All</h3>
            <p className="text-sm text-muted-foreground">
              Control everything from one dashboard
            </p>
          </div>
        </div>
      </section>

      {/* Security */}
      <section className="bg-muted/30 py-16">
        <div className="container mx-auto px-4">
          <div className="max-w-3xl mx-auto text-center">
            <Lock className="h-16 w-16 text-primary mx-auto mb-6" />
            <h2 className="text-3xl font-bold mb-4">Secure & Reliable</h2>
            <p className="text-muted-foreground mb-8">
              All integrations use industry-standard OAuth 2.0 authentication. 
              We never store your platform credentials and all data is encrypted in transit and at rest.
            </p>
            <div className="flex flex-wrap gap-4 justify-center">
              <div className="flex items-center gap-2 text-sm">
                <CheckCircle className="h-5 w-5 text-green-500" />
                <span>256-bit SSL Encryption</span>
              </div>
              <div className="flex items-center gap-2 text-sm">
                <CheckCircle className="h-5 w-5 text-green-500" />
                <span>GDPR Compliant</span>
              </div>
              <div className="flex items-center gap-2 text-sm">
                <CheckCircle className="h-5 w-5 text-green-500" />
                <span>SOC 2 Type II Certified</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="container mx-auto px-4 py-16">
        <Card>
          <CardContent className="p-12 text-center">
            <h2 className="text-3xl font-bold mb-4">Ready to Connect Your Platforms?</h2>
            <p className="text-muted-foreground mb-8 max-w-2xl mx-auto">
              Start syncing your properties across all major platforms today. 
              Free for all RentHub users.
            </p>
            <div className="flex gap-4 justify-center">
              <Button asChild size="lg">
                <Link href="/dashboard/properties">Get Started</Link>
              </Button>
              <Button asChild size="lg" variant="outline">
                <Link href="/contact">Contact Sales</Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </section>
    </div>
  );
}
