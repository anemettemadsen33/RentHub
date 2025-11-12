"use client";

import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Shield, CreditCard, RefreshCcw, Webhook, CheckCircle2, AlertTriangle } from 'lucide-react';
import Link from 'next/link';

export default function StripeIntegrationPage() {
  return (
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto p-6 space-y-8">
        <header className="space-y-2" role="banner">
          <h1 className="text-3xl font-bold flex items-center gap-2">
            <CreditCard className="h-8 w-8" aria-hidden="true" />
            Stripe Payments
          </h1>
          <p className="text-muted-foreground max-w-2xl">
            Secure card payments, refunds, and webhooks for booking confirmations.
          </p>
        </header>

        <section className="grid gap-4 md:grid-cols-2 lg:grid-cols-3" role="region" aria-label="Stripe setup status">
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">API Keys</CardTitle>
              <CardDescription>Publishable / Secret keys</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <Badge variant="secondary" aria-label="Keys status: Missing">Missing</Badge>
              <Button asChild size="sm" aria-label="Open integrations settings">
                <Link href="/integrations">Open Settings</Link>
              </Button>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Webhook</CardTitle>
              <CardDescription>Payment succeeded/failed</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <Badge variant="secondary" aria-label="Webhook status: Pending">Pending</Badge>
              <div className="text-xs text-muted-foreground">Endpoint: /api/payments/stripe/webhook</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">PCI & Security</CardTitle>
              <CardDescription>Secure capture via Stripe Elements</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <Badge variant="outline" className="gap-1"><Shield className="h-3 w-3" /> Uses Stripe-hosted fields</Badge>
            </CardContent>
          </Card>
        </section>

        <section className="space-y-4" role="region" aria-label="Next steps">
          <h2 className="text-lg font-semibold flex items-center gap-2">
            <RefreshCcw className="h-5 w-5" aria-hidden="true" /> Next Steps
          </h2>
          <ol className="list-decimal pl-5 space-y-2 text-sm">
            <li>Add STRIPE_SECRET and STRIPE_PUBLIC to environment</li>
            <li>Configure webhook on Stripe Dashboard to /api/payments/stripe/webhook</li>
            <li>Create Checkout Session on booking, handle success/cancel redirects</li>
          </ol>
        </section>
      </main>
    </MainLayout>
  );
}
