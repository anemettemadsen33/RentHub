"use client";

import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Radio, MessageSquare, Activity, Server } from 'lucide-react';

export default function RealtimeIntegrationPage() {
  return (
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto p-6 space-y-8">
        <header className="space-y-2" role="banner">
          <h1 className="text-3xl font-bold flex items-center gap-2">
            <Radio className="h-8 w-8" aria-hidden="true" />
            Realtime Messaging
          </h1>
          <p className="text-muted-foreground max-w-2xl">
            WebSocket/Server-Sent Events setup for chat, notifications and live dashboards.
          </p>
        </header>

        <section className="grid gap-4 md:grid-cols-2 lg:grid-cols-3" role="region" aria-label="Realtime setup status">
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Provider</CardTitle>
              <CardDescription>Pusher / Ably / Laravel Websockets</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <Badge variant="secondary">Pending</Badge>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Channels</CardTitle>
              <CardDescription>Presence & private channels</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <Badge variant="secondary">Planned</Badge>
            </CardContent>
          </Card>
          <Card>
            <CardHeader>
              <CardTitle className="text-sm">Usage</CardTitle>
              <CardDescription>Messages & delivery</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <Badge variant="outline" className="gap-1"><Activity className="h-3 w-3" /> Live metrics</Badge>
            </CardContent>
          </Card>
        </section>

        <section className="space-y-4" role="region" aria-label="Next steps">
          <h2 className="text-lg font-semibold flex items-center gap-2">
            <Server className="h-5 w-5" aria-hidden="true" /> Next Steps
          </h2>
          <ol className="list-decimal pl-5 space-y-2 text-sm">
            <li>Choose provider (Pusher/Ably/Laravel Websockets)</li>
            <li>Configure broadcasting.php and .env keys</li>
            <li>Wire up chat and notification streams</li>
          </ol>
        </section>
      </main>
    </MainLayout>
  );
}
