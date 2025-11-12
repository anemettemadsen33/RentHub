'use client';

import { useState } from 'react';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Mail, MapPin, Phone, Clock, Loader2 } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import Link from 'next/link';

export default function ContactPage() {
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitStatus, setSubmitStatus] = useState<'idle' | 'success' | 'error'>('idle');

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    setSubmitStatus('idle');

    // Simulate form submission
    await new Promise(resolve => setTimeout(resolve, 1500));
    
    setIsSubmitting(false);
    setSubmitStatus('success');
    
    // Reset form after success
    setTimeout(() => {
      setSubmitStatus('idle');
      (e.target as HTMLFormElement).reset();
    }, 3000);
  };

  return (
    <TooltipProvider>
      <MainLayout>
        <div className="container mx-auto px-4 py-12">
          <div className="max-w-4xl mx-auto">
            <div className="text-center mb-12">
              <h1 className="text-4xl font-bold tracking-tight mb-4">
                Contact Us
              </h1>
              <p className="text-lg text-muted-foreground">
                Have a question? We&apos;d love to hear from you. Send us a message and we&apos;ll respond as soon as possible.
              </p>
            </div>

          <div className="grid md:grid-cols-2 gap-8">
            {/* Contact Form */}
            <Card>
              <CardHeader>
                <CardTitle>Send us a message</CardTitle>
                <CardDescription>Fill out the form and we&apos;ll get back to you within 24 hours</CardDescription>
              </CardHeader>
              <CardContent>
                <form className="space-y-4" onSubmit={handleSubmit}>
                  <div>
                    <Label htmlFor="name">Name</Label>
                    <Input id="name" placeholder="Your name" required disabled={isSubmitting} />
                  </div>
                  <div>
                    <Label htmlFor="email">Email</Label>
                    <Input id="email" type="email" placeholder="your@email.com" required disabled={isSubmitting} />
                  </div>
                  <div>
                    <Label htmlFor="subject">Subject</Label>
                    <Input id="subject" placeholder="What&apos;s this about?" required disabled={isSubmitting} />
                  </div>
                  <div>
                    <Label htmlFor="message">Message</Label>
                    <Textarea id="message" placeholder="Your message..." rows={5} required disabled={isSubmitting} />
                  </div>
                  
                  {submitStatus === 'success' && (
                    <p className="text-sm text-green-600" role="alert" aria-live="polite">
                      Message sent successfully! We&apos;ll get back to you soon.
                    </p>
                  )}
                  
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <Button className="w-full" type="submit" disabled={isSubmitting}>
                        {isSubmitting ? (
                          <>
                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                            Sending...
                          </>
                        ) : (
                          'Send Message'
                        )}
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>Submit your inquiry</TooltipContent>
                  </Tooltip>
                </form>
              </CardContent>
            </Card>

            {/* Contact Information */}
            <div className="space-y-6">
              <Card>
                <CardHeader>
                  <CardTitle>Contact Information</CardTitle>
                </CardHeader>
                <CardContent className="space-y-4">
                  <div className="flex items-start gap-3">
                    <Mail className="h-5 w-5 text-primary mt-0.5" />
                    <div>
                      <p className="font-medium">Email</p>
                      <a href="mailto:support@renthub.com" className="text-muted-foreground hover:text-primary">
                        support@renthub.com
                      </a>
                    </div>
                  </div>
                  <div className="flex items-start gap-3">
                    <Phone className="h-5 w-5 text-primary mt-0.5" />
                    <div>
                      <p className="font-medium">Phone</p>
                      <a href="tel:+1234567890" className="text-muted-foreground hover:text-primary">
                        +1 (234) 567-890
                      </a>
                    </div>
                  </div>
                  <div className="flex items-start gap-3">
                    <MapPin className="h-5 w-5 text-primary mt-0.5" />
                    <div>
                      <p className="font-medium">Address</p>
                      <p className="text-muted-foreground">
                        123 Property Street<br />
                        London, UK EC1A 1BB
                      </p>
                    </div>
                  </div>
                  <div className="flex items-start gap-3">
                    <Clock className="h-5 w-5 text-primary mt-0.5" />
                    <div>
                      <p className="font-medium">Business Hours</p>
                      <p className="text-muted-foreground">
                        Monday - Friday: 9:00 AM - 6:00 PM<br />
                        Saturday - Sunday: 10:00 AM - 4:00 PM
                      </p>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card className="animate-fade-in-up" style={{ animationDelay: '240ms' }}>
                <CardHeader>
                  <CardTitle>Quick Links</CardTitle>
                </CardHeader>
                <CardContent className="space-y-2">
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <Button variant="outline" className="w-full justify-start" asChild>
                        <Link href="/help">Help Center</Link>
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>Browse help articles</TooltipContent>
                  </Tooltip>
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <Button variant="outline" className="w-full justify-start" asChild>
                        <Link href="/faq">FAQ</Link>
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>Frequently asked questions</TooltipContent>
                  </Tooltip>
                  <Tooltip>
                    <TooltipTrigger asChild>
                      <Button variant="outline" className="w-full justify-start" asChild>
                        <Link href="/properties">Browse Properties</Link>
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>Search available properties</TooltipContent>
                  </Tooltip>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>
      </div>
    </MainLayout>
    </TooltipProvider>
  );
}
