import { Metadata } from 'next';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Building2, TrendingUp, Users, Shield, ArrowRight, Star, MapPin, Calendar } from 'lucide-react';
import Link from 'next/link';
import { getTranslations } from 'next-intl/server';
import { generateHomeMetaTags } from '@/lib/meta-tags';
import { JsonLd, generateOrganizationJsonLd, generateWebsiteJsonLd } from '@/lib/seo';
import PartnerLogos from '@/components/partnerships/PartnerLogos';
import PropertyImportFeature from '@/components/partnerships/PropertyImportFeature';
import RecommendedProperties from '../components/recommended-properties';

export const metadata: Metadata = generateHomeMetaTags();

export default async function HomePage() {
  const t = await getTranslations('home');
  const baseUrl = process.env.NEXT_PUBLIC_APP_URL || 'https://renthub.com';
  
  const stats = [
    { label: 'Active Properties', value: '12,345', icon: Building2, trend: '+20.1%', trendUp: true },
    { label: 'Happy Tenants', value: '45,678', icon: Users, trend: '+15.3%', trendUp: true },
    { label: 'Cities Covered', value: '150+', icon: MapPin, trend: '+12.5%', trendUp: true },
    { label: 'Verified Hosts', value: '8,920', icon: Shield, trend: '+18.2%', trendUp: true },
  ];

  const features = [
    {
      title: 'Verified Properties',
      description: 'Every property is thoroughly verified and inspected for quality and safety.',
      icon: Shield,
    },
    {
      title: 'Instant Booking',
      description: 'Book your next rental instantly with our streamlined booking process.',
      icon: Calendar,
    },
    {
      title: 'Best Prices',
      description: 'Competitive pricing with transparent fees and no hidden charges.',
      icon: TrendingUp,
    },
    {
      title: 'Top Locations',
      description: 'Properties in prime locations across 150+ cities worldwide.',
      icon: MapPin,
    },
  ];

  return (
    <MainLayout>
      <JsonLd
        data={[
          generateOrganizationJsonLd(baseUrl),
          generateWebsiteJsonLd(baseUrl),
        ]}
      />
      
      {/* Hero Section */}
      <section className="relative py-20 md:py-32 overflow-hidden">
        <div className="absolute inset-0 bg-gradient-to-b from-muted/50 to-background" />
        <div className="container relative mx-auto px-4">
          <div className="max-w-3xl mx-auto text-center space-y-8 animate-fade-in-up">
            <Badge variant="secondary" className="mb-4">
              <Star className="h-3 w-3 mr-1 fill-current" />
              Trusted by 45,000+ users worldwide
            </Badge>
            <h1 className="text-4xl md:text-6xl font-bold tracking-tight">
              Find Your Perfect
              <span className="block bg-gradient-to-r from-primary to-primary/60 bg-clip-text text-transparent">
                Rental Property
              </span>
            </h1>
            <p className="text-xl text-muted-foreground max-w-2xl mx-auto">
              Discover verified properties, connect with trusted hosts, and book your next home in minutes.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center pt-4">
              <Button asChild size="lg" className="group">
                <Link href="/properties">
                  Browse Properties
                  <ArrowRight className="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform" />
                </Link>
              </Button>
              <Button asChild size="lg" variant="outline">
                <Link href="/about">Learn More</Link>
              </Button>
            </div>
          </div>
        </div>
      </section>

      {/* Stats Section */}
      <section className="py-16 border-y">
        <div className="container mx-auto px-4">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {stats.map((stat, index) => (
              <Card 
                key={stat.label} 
                className="animate-fade-in-up hover:shadow-lg transition-shadow"
                style={{ animationDelay: `${index * 100}ms` }}
              >
                <CardContent className="pt-6">
                  <div className="flex items-center justify-between">
                    <div className="space-y-2">
                      <p className="text-sm font-medium text-muted-foreground">{stat.label}</p>
                      <p className="text-3xl font-bold">{stat.value}</p>
                      <p className={`text-sm font-medium ${stat.trendUp ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'}`}>
                        {stat.trend} from last month
                      </p>
                    </div>
                    <div className="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                      <stat.icon className="h-6 w-6 text-primary" />
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>
      </section>

      {/* Features Section */}
      <section className="py-20">
        <div className="container mx-auto px-4">
          <div className="text-center mb-12">
            <Badge variant="outline" className="mb-4">Features</Badge>
            <h2 className="text-3xl md:text-4xl font-bold mb-4">Why Choose RentHub?</h2>
            <p className="text-muted-foreground max-w-2xl mx-auto">
              Everything you need to find, book, and manage your perfect rental property.
            </p>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {features.map((feature, index) => (
              <Card 
                key={feature.title} 
                className="group hover:shadow-lg transition-all hover:scale-105 animate-fade-in-up"
                style={{ animationDelay: `${index * 100}ms` }}
              >
                <CardHeader>
                  <div className="h-12 w-12 rounded-lg bg-primary/10 flex items-center justify-center mb-4 group-hover:bg-primary/20 transition-colors">
                    <feature.icon className="h-6 w-6 text-primary" />
                  </div>
                  <CardTitle className="text-xl">{feature.title}</CardTitle>
                  <CardDescription>{feature.description}</CardDescription>
                </CardHeader>
              </Card>
            ))}
          </div>
        </div>
      </section>

  {/* Partner Logos Section */}
  <PartnerLogos />

  {/* Property Import Feature Section */}
  <PropertyImportFeature />

  {/* Recommended Properties Section */}
  <RecommendedProperties />

      {/* CTA Section */}
      <section className="py-20 bg-muted/50">
        <div className="container mx-auto px-4">
          <Card className="border-2 overflow-hidden">
            <CardContent className="p-12 text-center space-y-6">
              <h2 className="text-3xl md:text-4xl font-bold">Ready to Get Started?</h2>
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
        </div>
      </section>
    </MainLayout>
  );
}
