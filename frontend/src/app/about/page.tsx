import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Users, Globe, Award, TrendingUp } from 'lucide-react';
import Image from 'next/image';
import Link from 'next/link';

export const metadata = {
  title: 'About RentHub | Our Story & Mission',
  description: 'Learn about RentHub, our mission to revolutionize property rentals, and the team behind the platform.',
};

export default function AboutPage() {
  return (
  <TooltipProvider>
    <MainLayout>
      <div className="container mx-auto px-4 py-12">
        {/* Hero Section */}
        <div className="max-w-4xl mx-auto text-center mb-16">
          <Badge className="mb-4">About Us</Badge>
          <h1 className="text-4xl md:text-5xl font-bold mb-6 animate-fade-in" style={{ animationDelay: '0ms' }}>
            Simplifying Property Rentals Worldwide
          </h1>
          <p className="text-xl text-muted-foreground animate-fade-in" style={{ animationDelay: '100ms' }}>
            RentHub is on a mission to make finding and renting properties easier, 
            faster, and more transparent for everyone.
          </p>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-6 mb-16">
          <Card className="animate-fade-in-up" style={{ animationDelay: '0ms' }}>
            <CardContent className="pt-6 text-center">
              <Users className="h-8 w-8 mx-auto mb-2 text-primary" />
              <div className="text-3xl font-bold mb-1">50K+</div>
              <div className="text-sm text-muted-foreground">Active Users</div>
            </CardContent>
          </Card>
          <Card className="animate-fade-in-up" style={{ animationDelay: '80ms' }}>
            <CardContent className="pt-6 text-center">
              <Globe className="h-8 w-8 mx-auto mb-2 text-primary" />
              <div className="text-3xl font-bold mb-1">100+</div>
              <div className="text-sm text-muted-foreground">Countries</div>
            </CardContent>
          </Card>
          <Card className="animate-fade-in-up" style={{ animationDelay: '160ms' }}>
            <CardContent className="pt-6 text-center">
              <Award className="h-8 w-8 mx-auto mb-2 text-primary" />
              <div className="text-3xl font-bold mb-1">10K+</div>
              <div className="text-sm text-muted-foreground">Properties</div>
            </CardContent>
          </Card>
          <Card className="animate-fade-in-up" style={{ animationDelay: '240ms' }}>
            <CardContent className="pt-6 text-center">
              <TrendingUp className="h-8 w-8 mx-auto mb-2 text-primary" />
              <div className="text-3xl font-bold mb-1">98%</div>
              <div className="text-sm text-muted-foreground">Satisfaction</div>
            </CardContent>
          </Card>
        </div>

        {/* Our Story */}
  <div className="max-w-3xl mx-auto mb-16 animate-fade-in-up" style={{ animationDelay: '120ms' }}>
          <h2 className="text-3xl font-bold mb-6">Our Story</h2>
          <div className="prose prose-lg dark:prose-invert">
            <p className="text-muted-foreground">
              Founded in 2024, RentHub was born from a simple idea: renting a property 
              shouldn&apos;t be complicated. Our founders experienced firsthand the frustrations 
              of traditional rental processes and set out to create a better solution.
            </p>
            <p className="text-muted-foreground">
              Today, RentHub connects property owners and renters in over 100 countries, 
              offering a seamless, transparent, and secure platform for all your rental needs. 
              Whether you&apos;re looking for a short-term vacation rental or a long-term home, 
              we&apos;re here to help.
            </p>
          </div>
        </div>

        {/* Mission & Values */}
        <div className="max-w-4xl mx-auto mb-16">
          <h2 className="text-3xl font-bold mb-8 text-center">Our Mission & Values</h2>
          <div className="grid md:grid-cols-3 gap-6">
            <Card className="animate-fade-in-up" style={{ animationDelay: '0ms' }}>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                    <Globe className="h-5 w-5 text-primary" />
                  </div>
                  Accessibility
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">
                  Making property rentals accessible to everyone, everywhere, with transparent 
                  pricing and clear terms.
                </p>
              </CardContent>
            </Card>
            <Card className="animate-fade-in-up" style={{ animationDelay: '120ms' }}>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                    <Award className="h-5 w-5 text-primary" />
                  </div>
                  Trust
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">
                  Building trust through verified listings, secure payments, and honest reviews 
                  from real users.
                </p>
              </CardContent>
            </Card>
            <Card className="animate-fade-in-up" style={{ animationDelay: '240ms' }}>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <div className="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center">
                    <TrendingUp className="h-5 w-5 text-primary" />
                  </div>
                  Innovation
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">
                  Continuously improving our platform with cutting-edge technology and user feedback.
                </p>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Team Section */}
        <div className="max-w-4xl mx-auto text-center">
          <h2 className="text-3xl font-bold mb-4">Join Our Team</h2>
          <p className="text-xl text-muted-foreground mb-6">
            We&apos;re always looking for talented people to join our mission. 
            Check out our open positions and be part of something special.
          </p>
          <Tooltip>
            <TooltipTrigger asChild>
              <Button asChild>
                <Link href="/careers">View Open Positions</Link>
              </Button>
            </TooltipTrigger>
            <TooltipContent>Explore career opportunities</TooltipContent>
          </Tooltip>
        </div>
      </div>
    </MainLayout>
  </TooltipProvider>
  );
}
