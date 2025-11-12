"use client";

import Link from 'next/link';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Zap, Accessibility, Gauge, Images, Languages, Shield, Code, Heart } from 'lucide-react';

const demos = [
  {
    icon: Accessibility,
    title: 'Accessibility',
    description: 'Focus management, screen reader content & keyboard navigation',
    href: '/demo/accessibility',
    status: 'Complete'
  },
  {
    icon: Gauge,
    title: 'Performance',
    description: 'Debounce, optimistic UI, favorites & empty states',
    href: '/demo/performance',
    status: 'Complete'
  },
  {
    icon: Images,
    title: 'Image Optimization',
    description: 'Progressive loading & responsive image strategies',
    href: '/demo/image-optimization',
    status: 'Planned'
  },
  {
    icon: Languages,
    title: 'Internationalization',
    description: 'Language switcher & locale detection patterns',
    href: '/demo/i18n',
    status: 'Planned'
  },
  {
    icon: Shield,
    title: 'Error Handling',
    description: 'Boundary patterns & resilient UI fallbacks',
    href: '/demo/_error-handling.disabled',
    status: 'Soon'
  },
  {
    icon: Code,
    title: 'Logger',
    description: 'Client diagnostics & structured event logging',
    href: '/demo/logger',
    status: 'Complete'
  }
];

export default function DemoOverviewPage() {
  return (
    <MainLayout>
      <main id="main-content" role="main" className="container mx-auto p-6 space-y-10">
        <header className="space-y-3" role="banner">
          <h1 className="text-3xl font-bold flex items-center gap-2">
            <Zap className="h-8 w-8 text-primary" aria-hidden="true" />
            Demo / Showcase
          </h1>
          <p className="text-muted-foreground max-w-2xl">
            Explore focused examples of UX, performance and accessibility patterns used across RentHub. Each section isolates a technique for easy reuse & testing.
          </p>
        </header>

        <section aria-labelledby="demo-grid-heading" className="grid gap-4 md:grid-cols-2 lg:grid-cols-3" role="region">
          <h2 id="demo-grid-heading" className="sr-only">Available demos</h2>
          {demos.map((d, i) => (
            <Card key={d.title} className="group focus-within:ring-2 focus-within:ring-primary" style={{ animationDelay: `${i * 50}ms` }}>
              <CardHeader className="flex flex-row items-start justify-between space-y-0 pb-2">
                <div className="flex items-center gap-2">
                  <d.icon className="h-5 w-5 text-primary" aria-hidden="true" />
                  <CardTitle className="text-sm font-medium">{d.title}</CardTitle>
                </div>
                <Badge variant="secondary" aria-label={`${d.title} status: ${d.status}`}>{d.status}</Badge>
              </CardHeader>
              <CardContent className="space-y-4">
                <CardDescription className="text-xs leading-relaxed">{d.description}</CardDescription>
                <Button asChild size="sm" aria-label={`Open ${d.title} demo`}>
                  <Link href={d.href}>Open</Link>
                </Button>
              </CardContent>
            </Card>
          ))}
        </section>

        <section aria-labelledby="future-demos-heading" className="space-y-4" role="region">
          <div className="flex items-center gap-2">
            <Heart className="h-5 w-5 text-pink-600" aria-hidden="true" />
            <h2 id="future-demos-heading" className="text-lg font-semibold">Upcoming Enhancements</h2>
          </div>
          <ul className="grid sm:grid-cols-2 gap-3" role="list" aria-label="Planned demo enhancements">
            <li role="listitem" className="p-3 rounded-md border text-sm flex items-start gap-2">
              <Shield className="h-4 w-4 text-amber-600 mt-0.5" aria-hidden="true" />Advanced security patterns (CSP, Trusted Types)
            </li>
            <li role="listitem" className="p-3 rounded-md border text-sm flex items-start gap-2">
              <Gauge className="h-4 w-4 text-green-600 mt-0.5" aria-hidden="true" />Server rendering performance profiling
            </li>
            <li role="listitem" className="p-3 rounded-md border text-sm flex items-start gap-2">
              <Languages className="h-4 w-4 text-purple-600 mt-0.5" aria-hidden="true" />RTL layout & advanced pluralization
            </li>
            <li role="listitem" className="p-3 rounded-md border text-sm flex items-start gap-2">
              <Images className="h-4 w-4 text-blue-600 mt-0.5" aria-hidden="true" />Responsive picture sets & art direction
            </li>
          </ul>
        </section>
      </main>
    </MainLayout>
  );
}
