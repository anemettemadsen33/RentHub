
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { TooltipProvider } from '@/components/ui/tooltip';

export const metadata = {
  title: 'Privacy Policy | RentHub',
  description: 'Learn how RentHub collects, uses, and protects your personal information.',
};

export default function PrivacyPage() {
  return (
  <TooltipProvider>
    <MainLayout>
      <div className="container mx-auto px-4 py-12">
  <Card className="max-w-4xl mx-auto animate-fade-in-up" style={{ animationDelay: '0ms' }}>
          <CardHeader>
            <CardTitle className="text-3xl animate-fade-in" style={{ animationDelay: '0ms' }}>Privacy Policy</CardTitle>
            <p className="text-muted-foreground text-sm animate-fade-in" style={{ animationDelay: '100ms' }}>Last updated: November 7, 2025</p>
          </CardHeader>
          <CardContent className="prose prose-lg dark:prose-invert">
            <h2>1. Introduction</h2>
            <p>
              RentHub (&quot;we&quot;, &quot;our&quot;, or &quot;us&quot;) is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our platform.
            </p>
            <h2>2. Information We Collect</h2>
            <h3>2.1 Personal Information</h3>
            <p>We may collect personal information that you provide directly to us, including:</p>
            <ul>
              <li>Name and contact information (email, phone number)</li>
              <li>Account credentials</li>
              <li>Payment information</li>
              <li>Profile information and photos</li>
              <li>Government-issued ID for verification</li>
            </ul>
            <h3>2.2 Usage Information</h3>
            <p>We automatically collect certain information about your device and how you interact with our service:</p>
            <ul>
              <li>IP address and browser type</li>
              <li>Pages visited and time spent</li>
              <li>Search queries and preferences</li>
              <li>Device information</li>
            </ul>

          <h3>2.3 Cookies and Tracking</h3>
          <p>
            We use cookies and similar tracking technologies to track activity on our service and hold certain information. You can control cookies through your browser settings.
          </p>

          <h2>3. How We Use Your Information</h2>
          <p>We use the collected information for various purposes:</p>
          <ul>
            <li>To provide and maintain our service</li>
            <li>To process your bookings and payments</li>
            <li>To verify your identity</li>
            <li>To send you notifications and updates</li>
            <li>To improve our service and develop new features</li>
            <li>To detect and prevent fraud</li>
            <li>To comply with legal obligations</li>
          </ul>

          <h2>4. How We Share Your Information</h2>
          <p>We may share your information in the following circumstances:</p>
          <ul>
            <li><strong>With Property Owners:</strong> When you make a booking</li>
            <li><strong>With Service Providers:</strong> Who help us operate our platform</li>
            <li><strong>For Legal Reasons:</strong> When required by law or to protect rights</li>
            <li><strong>With Your Consent:</strong> When you explicitly agree</li>
          </ul>

          <h2>5. Data Security</h2>
          <p>
            We implement appropriate technical and organizational measures to protect your personal information. However, no method of transmission over the Internet is 100% secure.
          </p>

          <h2>6. Your Rights</h2>
          <p>You have the right to:</p>
          <ul>
            <li>Access your personal information</li>
            <li>Correct inaccurate information</li>
            <li>Request deletion of your information</li>
            <li>Object to processing of your information</li>
            <li>Export your data</li>
            <li>Withdraw consent</li>
          </ul>

          <h2>7. Data Retention</h2>
          <p>
            We retain your personal information for as long as necessary to provide our services and comply with legal obligations.
          </p>

          <h2>8. International Data Transfers</h2>
          <p>
            Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place for such transfers.
          </p>

          <h2>9. Children&apos;s Privacy</h2>
          <p>
            Our service is not directed to individuals under the age of 18. We do not knowingly collect personal information from children.
          </p>

          <h2>10. Changes to This Privacy Policy</h2>
          <p>
            We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the &quot;Last updated&quot; date.
          </p>

          <h2>11. Contact Us</h2>
          <p>
            If you have any questions about this Privacy Policy, please contact us:
            <br />
            Email: privacy@renthub.com
            <br />
            Address: 123 Property Street, London, UK EC1A 1BB
          </p>

          <h2>12. GDPR Compliance</h2>
          <p>
            For users in the European Union, we comply with the General Data Protection Regulation (GDPR). You have additional rights under GDPR, including the right to lodge a complaint with a supervisory authority.
          </p>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  </TooltipProvider>
  );
}
