import { MainLayout } from '@/components/layouts/main-layout';
import {
  Accordion,
  AccordionContent,
  AccordionItem,
  AccordionTrigger,
} from '@/components/ui/accordion';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { MessageCircle } from 'lucide-react';
import Link from 'next/link';

export const metadata = {
  title: 'FAQ - Frequently Asked Questions | RentHub',
  description: 'Find answers to common questions about RentHub, booking properties, payments, and more.',
};

export default function FAQPage() {
  return (
    <TooltipProvider>
      <MainLayout>
        <div className="container mx-auto px-4 py-12">
          <div className="max-w-3xl mx-auto">
            <div className="text-center mb-12">
              <Badge className="mb-4">FAQ</Badge>
              <h1 className="text-4xl font-bold mb-4 animate-fade-in" style={{ animationDelay: '0ms' }}>
                Frequently Asked Questions
              </h1>
              <p className="text-xl text-muted-foreground animate-fade-in" style={{ animationDelay: '100ms' }}>
                Find answers to common questions about using RentHub
              </p>
            </div>

          <div className="space-y-8">
            {/* General Questions */}
            <section className="animate-fade-in-up" style={{ animationDelay: '0ms' }}>
              <h2 className="text-2xl font-bold mb-4">General</h2>
              <Accordion type="single" collapsible className="w-full">
                <AccordionItem value="item-1">
                  <AccordionTrigger>What is RentHub?</AccordionTrigger>
                  <AccordionContent>
                    RentHub is a comprehensive property rental platform that connects property owners 
                    with renters worldwide. We offer both short-term and long-term rental options with 
                    secure payments, verified listings, and 24/7 customer support.
                  </AccordionContent>
                </AccordionItem>
                <AccordionItem value="item-2">
                  <AccordionTrigger>Is RentHub free to use?</AccordionTrigger>
                  <AccordionContent>
                    Browsing and searching for properties is completely free. When you book a property, 
                    a service fee is added to your total. Property owners also pay a small commission 
                    on successful bookings.
                  </AccordionContent>
                </AccordionItem>
                <AccordionItem value="item-3">
                  <AccordionTrigger>How do I create an account?</AccordionTrigger>
                  <AccordionContent>
                    Click the &quot;Sign Up&quot; button in the top right corner, fill in your details, and 
                    verify your email address. You can also sign up using your Google or Facebook account.
                  </AccordionContent>
                </AccordionItem>
              </Accordion>
            </section>

            {/* Booking Questions */}
            <section className="animate-fade-in-up" style={{ animationDelay: '120ms' }}>
              <h2 className="text-2xl font-bold mb-4">Booking</h2>
              <Accordion type="single" collapsible className="w-full">
                <AccordionItem value="booking-1">
                  <AccordionTrigger>How do I book a property?</AccordionTrigger>
                  <AccordionContent>
                    Find a property you like, select your dates, review the total price, and click &quot;Book Now&quot;. 
                    You&apos;ll need to provide payment information and agree to the property&apos;s terms and conditions.
                  </AccordionContent>
                </AccordionItem>
                <AccordionItem value="booking-2">
                  <AccordionTrigger>Can I cancel my booking?</AccordionTrigger>
                  <AccordionContent>
                    Cancellation policies vary by property. Check the specific cancellation policy before booking. 
                    Most properties offer free cancellation up to 48 hours before check-in, but some may have 
                    stricter policies.
                  </AccordionContent>
                </AccordionItem>
                <AccordionItem value="booking-3">
                  <AccordionTrigger>What payment methods do you accept?</AccordionTrigger>
                  <AccordionContent>
                    We accept major credit cards (Visa, Mastercard, American Express), debit cards, and PayPal. 
                    All payments are processed securely through our encrypted payment system.
                  </AccordionContent>
                </AccordionItem>
                <AccordionItem value="booking-4">
                  <AccordionTrigger>When will I be charged?</AccordionTrigger>
                  <AccordionContent>
                    For instant bookings, you&apos;ll be charged immediately after confirmation. For booking requests, 
                    you&apos;ll only be charged once the property owner accepts your request.
                  </AccordionContent>
                </AccordionItem>
              </Accordion>
            </section>

            {/* Property Owners */}
            <section className="animate-fade-in-up" style={{ animationDelay: '240ms' }}>
              <h2 className="text-2xl font-bold mb-4">For Property Owners</h2>
              <Accordion type="single" collapsible className="w-full">
                <AccordionItem value="owner-1">
                  <AccordionTrigger>How do I list my property?</AccordionTrigger>
                  <AccordionContent>
                    Click &quot;List Your Property&quot; in the navigation menu, fill out the property details form, 
                    upload high-quality photos, set your pricing and availability, and submit for review. 
                    Our team typically reviews listings within 24 hours.
                  </AccordionContent>
                </AccordionItem>
                <AccordionItem value="owner-2">
                  <AccordionTrigger>What fees do property owners pay?</AccordionTrigger>
                  <AccordionContent>
                    Property owners pay a 10% service fee on successful bookings. There are no upfront costs 
                    or subscription fees - you only pay when you get bookings.
                  </AccordionContent>
                </AccordionItem>
                <AccordionItem value="owner-3">
                  <AccordionTrigger>How and when do I get paid?</AccordionTrigger>
                  <AccordionContent>
                    Payments are released 24 hours after the guest checks in. You can choose to receive payments 
                    via bank transfer or PayPal. Set up your payout method in your account settings.
                  </AccordionContent>
                </AccordionItem>
              </Accordion>
            </section>

            {/* Safety & Trust */}
            <section className="animate-fade-in-up" style={{ animationDelay: '360ms' }}>
              <h2 className="text-2xl font-bold mb-4">Safety & Trust</h2>
              <Accordion type="single" collapsible className="w-full">
                <AccordionItem value="safety-1">
                  <AccordionTrigger>Are all properties verified?</AccordionTrigger>
                  <AccordionContent>
                    Yes, we verify all property listings before they go live. This includes checking property 
                    ownership documents, reviewing photos, and confirming the property details. We also encourage 
                    guests to leave honest reviews after their stay.
                  </AccordionContent>
                </AccordionItem>
                <AccordionItem value="safety-2">
                  <AccordionTrigger>Is my payment information secure?</AccordionTrigger>
                  <AccordionContent>
                    Absolutely. We use bank-level encryption and never share your payment information with property 
                    owners. All transactions are processed through PCI-compliant payment processors.
                  </AccordionContent>
                </AccordionItem>
                <AccordionItem value="safety-3">
                  <AccordionTrigger>What if something goes wrong during my stay?</AccordionTrigger>
                  <AccordionContent>
                    Contact our 24/7 customer support team immediately. We&apos;ll work with you and the property owner 
                    to resolve any issues. In serious cases, we offer rebooking assistance or refunds according to 
                    our Guest Refund Policy.
                  </AccordionContent>
                </AccordionItem>
              </Accordion>
            </section>
          </div>

          {/* Still have questions? */}
          <Card className="mt-12 animate-fade-in-up" style={{ animationDelay: '480ms' }}>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <MessageCircle className="h-5 w-5" />
                Still have questions?
              </CardTitle>
              <CardDescription>
                Our support team is here to help 24/7
              </CardDescription>
            </CardHeader>
            <CardContent className="flex gap-4">
              <Tooltip>
                <TooltipTrigger asChild>
                  <Link href="/contact" className="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                    Contact Support
                  </Link>
                </TooltipTrigger>
                <TooltipContent>Get help from our support team</TooltipContent>
              </Tooltip>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Link href="/help" className="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                    Visit Help Center
                  </Link>
                </TooltipTrigger>
                <TooltipContent>Browse help articles</TooltipContent>
              </Tooltip>
            </CardContent>
          </Card>
        </div>
      </div>
    </MainLayout>
    </TooltipProvider>
  );
}
