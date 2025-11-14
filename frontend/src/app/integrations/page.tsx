'use client';

import { useState, useEffect } from 'react';
import { Navbar } from '@/components/navbar';
import { IntegrationCard } from '@/components/integrations/integration-card';
import { IntegrationGridSkeleton, IntegrationStatsSkeleton } from '@/components/integrations/integration-skeleton';
import { useIntegrations } from '@/hooks/use-integrations';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Zap, TrendingUp, Globe, Lock, CheckCircle, AlertCircle, BarChart3 } from 'lucide-react';
import { toast } from 'sonner';

export default function IntegrationsPage() {
  const {
    integrations,
    loading,
    syncing,
    connectIntegration,
    disconnectIntegration,
    syncIntegration,
    isConnected,
  } = useIntegrations();

  const [mounted, setMounted] = useState(false);

  useEffect(() => {
    setMounted(true);
  }, []);

  const handleConnect = async (platform: string) => {
    try {
      await connectIntegration(platform);
    } catch (error) {
      toast.error('Failed to connect integration');
    }
  };

  const handleDisconnect = async (id: string) => {
    try {
      await disconnectIntegration(id);
    } catch (error) {
      toast.error('Failed to disconnect integration');
    }
  };

  const handleSync = async (id: string) => {
    return await syncIntegration(id);
  };

  const platformIntegrations = [
    {
      type: 'airbnb',
      name: 'Airbnb',
      description: 'Sync your properties with Airbnb\'s 5+ million listings worldwide.',
      features: [
        'Two-way calendar synchronization',
        'Automatic pricing and availability updates',
        'Unified messaging and booking management',
      ],
    },
    {
      type: 'booking',
      name: 'Booking.com',
      description: 'Reach 28+ million listings across 154,000+ destinations.',
      features: [
        'Real-time inventory synchronization',
        'Dynamic rate management',
        'Booking confirmation automation',
      ],
    },
    {
      type: 'vrbo',
      name: 'Vrbo (Vacation Rentals by Owner)',
      description: 'Connect with 2+ million vacation rental properties.',
      features: [
        'Automated listing distribution',
        'Payment processing integration',
        'Review management and sync',
      ],
    },
  ];

  const getIntegrationForPlatform = (platformType: string) => {
    return integrations.find(integration => integration.type === platformType);
  };

  if (!mounted) {
    return null;
  }

  return (
    <div className="min-h-screen bg-background">
      {/* Navigation */}
      <Navbar />

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

      {/* Connection Status Alert */}
      {integrations.some(int => int.status === 'error') && (
        <section className="container mx-auto px-4 py-4">
          <Alert variant="destructive">
            <AlertCircle className="h-4 w-4" />
            <AlertDescription>
              Some integrations have connection errors. Please check and reconnect them.
            </AlertDescription>
          </Alert>
        </section>
      )}

      {/* Benefits */}
      <section className="container mx-auto px-4 py-16">
        <h2 className="text-3xl font-bold text-center mb-12">Why Use Integrations?</h2>
        {loading ? (
          <IntegrationStatsSkeleton />
        ) : (
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
                <BarChart3 className="h-12 w-12 text-primary mb-4" />
                <CardTitle>Unified Analytics</CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">
                  Track performance across all platforms from one centralized dashboard.
                </p>
              </CardContent>
            </Card>
          </div>
        )}
      </section>

      {/* Main Integrations */}
      <section className="bg-muted/30 py-16">
        <div className="container mx-auto px-4">
          <div className="flex items-center justify-between mb-12">
            <h2 className="text-3xl font-bold">Featured Integrations</h2>
            <div className="flex items-center gap-2">
              <span className="text-sm text-muted-foreground">Connected:</span>
              <Badge variant="default" className="bg-green-500">
                {integrations.filter(int => int.status === 'connected').length}/{platformIntegrations.length}
              </Badge>
            </div>
          </div>
          
          {loading ? (
            <IntegrationGridSkeleton count={3} />
          ) : (
            <div className="space-y-8 max-w-4xl mx-auto">
              {platformIntegrations.map((platform) => {
                const integration = getIntegrationForPlatform(platform.type);
                return (
                  <IntegrationCard
                    key={platform.type}
                    integration={integration || {
                      id: `mock-${platform.type}`,
                      name: platform.name,
                      type: platform.type as any,
                      status: 'disconnected',
                      settings: {},
                      created_at: new Date().toISOString(),
                      updated_at: new Date().toISOString(),
                    }}
                    onConnect={handleConnect}
                    onDisconnect={handleDisconnect}
                    onSync={handleSync}
                    syncing={!!syncing[platform.type]}
                  />
                );
              })}
            </div>
          )}
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
                <a href="/dashboard/properties">Get Started</a>
              </Button>
              <Button asChild size="lg" variant="outline">
                <a href="/contact">Contact Sales</a>
              </Button>
            </div>
          </CardContent>
        </Card>
      </section>
    </div>
  );
}