"use client";

import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Link from 'next/link';

export default function StripeIntegrationPage() {
  return (
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto p-6 space-y-8">
        <header className="space-y-2" role="banner">
          <h1 className="text-3xl font-bold">Card Payments (Disabled)</h1>
          <p className="text-muted-foreground max-w-2xl">
            This deployment uses bank transfer with automatic invoicing and proof-of-payment. Stripe is disabled.
          </p>
        </header>

        <section>
          <Card>
            <CardHeader>
              <CardTitle>How to accept payments</CardTitle>
            </CardHeader>
            <CardContent className="space-y-3">
              <ol className="list-decimal pl-5 space-y-2 text-sm">
                <li>Guests receive bank transfer instructions on booking.</li>
                <li>They upload a payment proof for verification.</li>
                <li>On approval, the system marks the payment complete and emails the invoice.</li>
              </ol>
              <div className="pt-4">
                <Link href="/payments" className="underline">Go to Payments</Link>
              </div>
            </CardContent>
          </Card>
        </section>
      </main>
    </MainLayout>
  );
}
