import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Search, Book, Home, CreditCard, Shield, MessageCircle } from 'lucide-react';
import Link from 'next/link';

export const metadata = {
  title: 'Help Center | RentHub',
  description: 'Get help with bookings, payments, account settings, and more. Browse our help articles or contact support.',
};

export default function HelpPage() {
  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-12">
        <div className="max-w-4xl mx-auto">
          {/* Header */}
          <div className="text-center mb-12">
            <h1 className="text-4xl font-bold mb-4 animate-fade-in">How can we help you?</h1>
            <p className="text-xl text-muted-foreground mb-8 animate-fade-in" style={{ animationDelay: '100ms' }}>
              Search for answers or browse our help topics below
            </p>
            <div className="relative max-w-2xl mx-auto animate-fade-in-up" style={{ animationDelay: '200ms' }}>
              <Search className="absolute left-3 top-3 h-5 w-5 text-muted-foreground" />
              <Input
                placeholder="Search for help..."
                className="pl-10 h-12 text-lg"
              />
            </div>
          </div>

          {/* Help Categories */}
          <div className="grid md:grid-cols-2 gap-6 mb-12">
            <Card className="cursor-pointer hover:border-primary transition-colors animate-fade-in-up" style={{ animationDelay: '300ms' }}>
              <CardHeader>
                <CardTitle className="flex items-center gap-3">
                  <div className="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <Book className="h-6 w-6 text-primary" />
                  </div>
                  Booking & Reservations
                </CardTitle>
                <CardDescription>
                  Learn how to search, book, and manage your reservations
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ul className="space-y-2 text-sm text-muted-foreground">
                  <li>• How to book a property</li>
                  <li>• Modifying your reservation</li>
                  <li>• Cancellation policies</li>
                  <li>• Booking confirmation</li>
                </ul>
              </CardContent>
            </Card>

            <Card className="cursor-pointer hover:border-primary transition-colors animate-fade-in-up" style={{ animationDelay: '360ms' }}>
              <CardHeader>
                <CardTitle className="flex items-center gap-3">
                  <div className="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <CreditCard className="h-6 w-6 text-primary" />
                  </div>
                  Payments & Refunds
                </CardTitle>
                <CardDescription>
                  Understand our payment process and refund policies
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ul className="space-y-2 text-sm text-muted-foreground">
                  <li>• Payment methods accepted</li>
                  <li>• When you&apos;ll be charged</li>
                  <li>• Refund process</li>
                  <li>• Service fees explained</li>
                </ul>
              </CardContent>
            </Card>

            <Card className="cursor-pointer hover:border-primary transition-colors animate-fade-in-up" style={{ animationDelay: '420ms' }}>
              <CardHeader>
                <CardTitle className="flex items-center gap-3">
                  <div className="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <Home className="h-6 w-6 text-primary" />
                  </div>
                  Hosting Your Property
                </CardTitle>
                <CardDescription>
                  Everything you need to know about listing your property
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ul className="space-y-2 text-sm text-muted-foreground">
                  <li>• Creating your first listing</li>
                  <li>• Pricing strategies</li>
                  <li>• Managing bookings</li>
                  <li>• Host best practices</li>
                </ul>
              </CardContent>
            </Card>

            <Card className="cursor-pointer hover:border-primary transition-colors animate-fade-in-up" style={{ animationDelay: '480ms' }}>
              <CardHeader>
                <CardTitle className="flex items-center gap-3">
                  <div className="h-12 w-12 rounded-full bg-primary/10 flex items-center justify-center">
                    <Shield className="h-6 w-6 text-primary" />
                  </div>
                  Safety & Trust
                </CardTitle>
                <CardDescription>
                  Learn about our safety features and verification process
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ul className="space-y-2 text-sm text-muted-foreground">
                  <li>• Identity verification</li>
                  <li>• Secure payments</li>
                  <li>• Guest and host reviews</li>
                  <li>• Reporting issues</li>
                </ul>
              </CardContent>
            </Card>
          </div>

          {/* Popular Articles */}
          <div className="mb-12 animate-fade-in-up" style={{ animationDelay: '540ms' }}>
            <h2 className="text-2xl font-bold mb-6">Popular Help Articles</h2>
            <div className="space-y-3">
              {[
                'How to book a property on RentHub',
                'What happens if I need to cancel?',
                'How do I contact a property owner?',
                'Understanding service fees',
                'How to leave a review',
                'Setting up your profile',
                'Payment methods and currency',
                'How to list your property',
              ].map((article, index) => (
                <div key={index} className="p-4 border rounded-lg hover:border-primary transition-colors cursor-pointer animate-fade-in-up" style={{ animationDelay: `${600 + index * 40}ms` }}>
                  <p className="font-medium">{article}</p>
                </div>
              ))}
            </div>
          </div>

          {/* Contact Support */}
          <Card className="bg-primary/5 border-primary/20 animate-fade-in-up" style={{ animationDelay: '920ms' }}>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <MessageCircle className="h-5 w-5" />
                Can&apos;t find what you&apos;re looking for?
              </CardTitle>
              <CardDescription>
                Our support team is available 24/7 to help you
              </CardDescription>
            </CardHeader>
            <CardContent className="flex gap-4">
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button asChild>
                      <Link href="/contact">Contact Support</Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Send us a message</TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" asChild>
                      <Link href="/faq">View FAQ</Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Browse frequently asked questions</TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </CardContent>
          </Card>
        </div>
      </div>
    </MainLayout>
  );
}
