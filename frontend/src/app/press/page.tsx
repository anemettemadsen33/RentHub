import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { TooltipProvider } from '@/components/ui/tooltip';
import { Newspaper, Calendar, ExternalLink } from 'lucide-react';
import Link from 'next/link';

export const metadata = {
  title: 'Press & Media | RentHub',
  description: 'Latest news, press releases, and media resources from RentHub.',
};

export default function PressPage() {
  const pressReleases = [
    {
      date: 'October 15, 2025',
      title: 'RentHub Expands to 50 New Cities Across Europe',
      description: 'Platform continues rapid growth with launch in major European markets.',
      category: 'Expansion',
    },
    {
      date: 'September 3, 2025',
      title: 'RentHub Raises $50M Series B Funding',
      description: 'Investment to fuel product development and international expansion.',
      category: 'Funding',
    },
    {
      date: 'August 12, 2025',
      title: 'New AI-Powered Search Features Launched',
      description: 'Advanced matching algorithm helps renters find perfect properties faster.',
      category: 'Product',
    },
    {
      date: 'July 1, 2025',
      title: 'RentHub Reaches 1 Million Active Users',
      description: 'Platform celebrates major milestone in user growth.',
      category: 'Milestone',
    },
  ];

  return (
  <TooltipProvider>
    <MainLayout>
      <div className="container mx-auto px-4 py-12">
        <div className="max-w-4xl mx-auto">
          {/* Hero */}
          <div className="text-center mb-12">
            <Badge className="mb-4">
              <Newspaper className="h-3 w-3 mr-1" />
              Press & Media
            </Badge>
            <h1 className="text-4xl font-bold mb-4 animate-fade-in" style={{ animationDelay: '0ms' }}>RentHub in the News</h1>
            <p className="text-xl text-muted-foreground animate-fade-in" style={{ animationDelay: '100ms' }}>
              Latest news, press releases, and media resources
            </p>
          </div>

          {/* Press Releases */}
          <div className="mb-12">
            <h2 className="text-2xl font-bold mb-6">Recent Press Releases</h2>
            <div className="space-y-4">
              {pressReleases.map((release, index) => (
                <Card key={index} className="hover:border-primary transition-colors cursor-pointer animate-fade-in-up" style={{ animationDelay: `${index * 60}ms` }}>
                  <CardHeader>
                    <div className="flex items-start justify-between mb-2">
                      <Badge variant="secondary">{release.category}</Badge>
                      <div className="flex items-center gap-1 text-sm text-muted-foreground">
                        <Calendar className="h-4 w-4" />
                        {release.date}
                      </div>
                    </div>
                    <CardTitle className="hover:text-primary transition-colors">
                      {release.title}
                      <ExternalLink className="inline h-4 w-4 ml-2" />
                    </CardTitle>
                    <CardDescription>{release.description}</CardDescription>
                  </CardHeader>
                </Card>
              ))}
            </div>
          </div>

          {/* Media Kit */}
          <div className="grid md:grid-cols-2 gap-6 mb-12">
            <Card>
              <CardHeader>
                <CardTitle>Media Kit</CardTitle>
                <CardDescription>
                  Download our logos, brand guidelines, and high-resolution images
                </CardDescription>
              </CardHeader>
              <CardContent>
                <Link href="/contact" className="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                  Download Media Kit
                </Link>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle>Press Contact</CardTitle>
                <CardDescription>
                  Get in touch with our press team
                </CardDescription>
              </CardHeader>
              <CardContent className="space-y-2">
                <p className="text-sm">
                  <strong>Email:</strong> press@renthub.com
                </p>
                <p className="text-sm">
                  <strong>Phone:</strong> +1 (234) 567-890
                </p>
              </CardContent>
            </Card>
          </div>

          {/* Featured In */}
          <div>
            <h2 className="text-2xl font-bold mb-6 text-center">As Featured In</h2>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-8 items-center justify-items-center opacity-60">
              {['TechCrunch', 'Forbes', 'The Guardian', 'Wired'].map((outlet, index) => (
                <div key={index} className="text-2xl font-bold text-muted-foreground">
                  {outlet}
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </MainLayout>
  </TooltipProvider>
  );
}
