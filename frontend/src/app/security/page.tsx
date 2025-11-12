"use client";

import Link from 'next/link';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Shield, Lock, Key, FileWarning, Activity, Terminal, Eye, AlertTriangle } from 'lucide-react';

export default function SecurityOverviewPage() {
  const sections = [
    {
      icon: Shield,
      title: 'Audit & Logs',
      description: 'Review security events, admin actions and automated alerts',
      href: '/security/audit',
      badge: 'Live'
    },
    {
      icon: Lock,
      title: 'Authentication',
      description: 'Password policy, 2FA status, session management',
      href: '/settings',
      badge: 'Configured'
    },
    {
      icon: Key,
      title: 'Access & Roles',
      description: 'User roles, permissions matrix and privilege boundaries',
      href: '/admin/roles',
      badge: 'Pending'
    },
    {
      icon: FileWarning,
      title: 'Vulnerabilities',
      description: 'Dependency scan results & remediation status',
      href: '/admin/security',
      badge: 'OK'
    },
    {
      icon: Activity,
      title: 'Runtime Monitoring',
      description: 'Performance, error rates & anomaly detection hooks',
      href: '/admin/monitoring',
      badge: 'Soon'
    },
    {
      icon: Terminal,
      title: 'API Keys & Integrations',
      description: 'Third‑party credentials, rotation schedule & scopes',
      href: '/integrations',
      badge: 'Review'
    }
  ];

  return (
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto p-6 space-y-8">
        <header className="space-y-2" role="banner">
          <h1 className="text-3xl font-bold flex items-center gap-2">
            <Shield className="h-8 w-8" aria-hidden="true" />
            Security Center
          </h1>
          <p className="text-muted-foreground max-w-2xl">
            Centralized overview of platform security posture. Use the cards below to drill into detailed reports & configuration areas.
          </p>
        </header>

        <section aria-labelledby="quick-status-heading" className="grid gap-4 md:grid-cols-2 lg:grid-cols-3" role="region">
          <h2 id="quick-status-heading" className="sr-only">Security modules</h2>
          {sections.map((s, i) => (
            <Card key={s.title} className="group focus-within:ring-2 focus-within:ring-primary" style={{ animationDelay: `${i * 40}ms` }}>
              <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
                <div className="flex items-center gap-2">
                  <s.icon className="h-5 w-5 text-primary" aria-hidden="true" />
                  <CardTitle className="text-sm font-semibold">{s.title}</CardTitle>
                </div>
                <Badge variant="secondary" aria-label={`${s.title} status: ${s.badge}`}>{s.badge}</Badge>
              </CardHeader>
              <CardContent className="space-y-4">
                <CardDescription className="text-xs leading-relaxed">{s.description}</CardDescription>
                <Button asChild size="sm" aria-label={`Open ${s.title} section`}>
                  <Link href={s.href}>Open</Link>
                </Button>
              </CardContent>
            </Card>
          ))}
        </section>

        <section aria-labelledby="quick-alerts-heading" className="space-y-4" role="region">
          <div className="flex items-center gap-2">
            <AlertTriangle className="h-5 w-5 text-amber-600" aria-hidden="true" />
            <h2 id="quick-alerts-heading" className="text-lg font-semibold">Recent Alerts</h2>
          </div>
          <div className="rounded-md border p-4 space-y-3" role="list" aria-label="Recent security alerts">
            <div role="listitem" className="flex items-start gap-3">
              <Eye className="h-4 w-4 text-red-600 mt-0.5" aria-hidden="true" />
              <p className="text-sm"><strong>Brute force attempt blocked</strong> • 6 IP addresses temporarily banned (rate limiting)</p>
            </div>
            <div role="listitem" className="flex items-start gap-3">
              <FileWarning className="h-4 w-4 text-yellow-600 mt-0.5" aria-hidden="true" />
              <p className="text-sm"><strong>Dependency scan</strong> • 0 critical, 1 moderate (upgrade scheduled)</p>
            </div>
            <div role="listitem" className="flex items-start gap-3">
              <Activity className="h-4 w-4 text-blue-600 mt-0.5" aria-hidden="true" />
              <p className="text-sm"><strong>API latency spike normalized</strong> • Autoscaling handled increased traffic</p>
            </div>
          </div>
        </section>
      </main>
    </MainLayout>
  );
}
