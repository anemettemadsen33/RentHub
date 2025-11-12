
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';

export const metadata = {
  title: 'Terms of Service | RentHub',
  description: 'Read our terms of service and understand the rules and regulations for using RentHub.',
};

export default function TermsPage() {
  return (
  <TooltipProvider>
    <MainLayout>
      <div className="container mx-auto px-4 py-12">
  <Card className="max-w-4xl mx-auto animate-fade-in-up" style={{ animationDelay: '0ms' }}>
          <CardHeader>
            <CardTitle className="text-3xl animate-fade-in" style={{ animationDelay: '0ms' }}>Terms of Service</CardTitle>
            <p className="text-muted-foreground text-sm animate-fade-in" style={{ animationDelay: '100ms' }}>Last updated: November 7, 2025</p>
          </CardHeader>
          <CardContent className="prose prose-lg dark:prose-invert">
            <h2>1. Acceptance of Terms</h2>
            <p>
              By accessing and using RentHub, you accept and agree to be bound by the terms and provision of this agreement.
            </p>
            <h2>2. Use of Service</h2>
            <p>
              RentHub provides a platform for property owners and renters to connect. You agree to use our service only for lawful purposes and in accordance with these Terms.
            </p>
            <h3>2.1 Account Registration</h3>
            <p>
              To access certain features, you must register for an account. You agree to:
            </p>
            <ul>
              <li>Provide accurate, current, and complete information</li>
              <li>Maintain and update your information</li>
              <li>Maintain the security of your password</li>
              <li>Accept responsibility for all activities under your account</li>
            </ul>
            <h2>3. Property Listings</h2>
            <p>
              Property owners who list on RentHub agree to:
            </p>
            <ul>
              <li>Provide accurate property information and photos</li>
              <li>Maintain the property in good condition</li>
              <li>Honor confirmed bookings</li>
              <li>Comply with all applicable laws and regulations</li>
            </ul>
            <h2>4. Bookings and Payments</h2>
            <h3>4.1 Booking Process</h3>
            <p>
              When you book a property through RentHub, you enter into a contract directly with the property owner.
            </p>
            <h3>4.2 Payment Terms</h3>
            <p>
              All payments are processed through our secure payment system. Service fees apply to all bookings.
            </p>
            <h3>4.3 Cancellations</h3>
            <p>
              Cancellation policies are set by individual property owners. Review the cancellation policy before booking.
            </p>
            <h2>5. User Conduct</h2>
            <p>You agree not to:</p>
            <ul>
              <li>Use the service for any illegal purpose</li>
              <li>Post false, inaccurate, or misleading information</li>
              <li>Harass, abuse, or harm other users</li>
              <li>Violate any applicable laws or regulations</li>
              <li>Attempt to gain unauthorized access to the system</li>
            </ul>
            <h2>6. Intellectual Property</h2>
            <p>
              The service and its original content, features, and functionality are owned by RentHub and are protected by international copyright, trademark, and other intellectual property laws.
            </p>
            <h2>7. Limitation of Liability</h2>
            <p>
              RentHub shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of or inability to use the service.
            </p>
            <h2>8. Dispute Resolution</h2>
            <p>
              Any disputes arising from these terms shall be resolved through binding arbitration in accordance with the rules of the American Arbitration Association.
            </p>
            <h2>9. Changes to Terms</h2>
            <p>
              We reserve the right to modify these terms at any time. We will notify users of any material changes via email or through the service.
            </p>
            <h2>10. Contact Information</h2>
            <p>
              Dacă ai întrebări despre acești Termeni, contactează-ne:
              <br />
              <strong>Email:</strong> legal@renthub.com
              <br />
              <strong>Address:</strong> 123 Property Street, London, UK EC1A 1BB
            </p>
            <div className="mt-6 flex justify-center">
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button asChild size="lg">
                    <a href="mailto:legal@renthub.com">Contact Legal Team</a>
                  </Button>
                </TooltipTrigger>
                <TooltipContent>Send email to our legal department</TooltipContent>
              </Tooltip>
            </div>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  </TooltipProvider>
  );
}
