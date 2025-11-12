"use client";

import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Calendar, Link as LinkIcon, RefreshCcw, ShieldCheck } from 'lucide-react';
import Link from 'next/link';

export default function GoogleCalendarIntegrationPage() {
  return (
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto p-6 space-y-8">
        <header className="space-y-2" role="banner">
          <h1 className="text-3xl font-bold flex items-center gap-2">
            <Calendar className="h-8 w-8" aria-hidden="true" />
            Google Calendar Sync
          </h1>
          <p className="text-muted-foreground max-w-2xl">
            Automatically synchronize booking dates with your external Google Calendars.
          </p>
        </header>

        <section className="grid gap-4 md:grid-cols-2 lg:grid-cols-3" role="region" aria-label="Google Calendar setup status">
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">OAuth Credentials</CardTitle>
              <CardDescription>Client ID / Secret</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <Badge variant="secondary" aria-label="OAuth status: Missing">Missing</Badge>
              <Button asChild size="sm" aria-label="Open integrations settings">
                <Link href="/integrations">Open Settings</Link>
              </Button>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Webhook Renewal</CardTitle>
              <CardDescription>Push channel refresh</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <Badge variant="secondary" aria-label="Webhook renewal status: Scheduled">Scheduled</Badge>
              <div className="text-xs text-muted-foreground">Command: renew-google-calendar-webhooks</div>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Security</CardTitle>
              <CardDescription>Token storage & scopes</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <Badge variant="outline" className="gap-1"><ShieldCheck className="h-3 w-3" /> Encrypted tokens</Badge>
            </CardContent>
          </Card>
        </section>

        <section className="space-y-4" role="region" aria-label="Next steps">
          <h2 className="text-lg font-semibold flex items-center gap-2">
            <RefreshCcw className="h-5 w-5" aria-hidden="true" /> Next Steps
          </h2>
          <ol className="list-decimal pl-5 space-y-2 text-sm">
            <li>Create Google Cloud project & enable Calendar API</li>
            <li>Add GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET to environment</li>
            <li>Implement OAuth consent flow and store refresh tokens</li>
            <li>Schedule sync + listen for push notifications (webhooks)</li>
          </ol>
        </section>
      </main>
    </MainLayout>
  );
}
