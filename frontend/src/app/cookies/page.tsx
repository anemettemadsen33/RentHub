import { MainLayout } from '@/components/layouts/main-layout';
import { TooltipProvider } from '@/components/ui/tooltip';

export const metadata = {
  title: 'Cookie Policy | RentHub',
  description: 'Learn about how RentHub uses cookies and how you can control them.',
};

export default function CookiesPage() {
  return (
  <TooltipProvider>
    <MainLayout>
      <div className="container mx-auto px-4 py-12">
        <div className="max-w-4xl mx-auto animate-fade-in-up" style={{ animationDelay: '0ms' }}>
          <h1 className="text-4xl font-bold mb-4 animate-fade-in" style={{ animationDelay: '0ms' }}>Cookie Policy</h1>
          <p className="text-muted-foreground mb-8 animate-fade-in" style={{ animationDelay: '100ms' }}>Last updated: November 7, 2025</p>

          <div className="space-y-8">
            <section>
              <h2 className="text-2xl font-bold mb-4">1. What Are Cookies?</h2>
              <p className="text-muted-foreground">
                Cookies are small text files that are placed on your device when you visit our website. They help us provide you with a better experience by remembering your preferences and understanding how you use our service.
              </p>
            </section>

            <section>
              <h2 className="text-2xl font-bold mb-4">2. How We Use Cookies</h2>
              <p className="text-muted-foreground mb-4">We use cookies for several purposes:</p>

              <div className="space-y-4">
                <div>
                  <h3 className="text-xl font-semibold mb-2">2.1 Essential Cookies</h3>
                  <p className="text-muted-foreground mb-2">
                    These cookies are necessary for the website to function properly. They enable core functionality such as security, network management, and accessibility.
                  </p>
                  <ul className="list-disc list-inside space-y-1 text-muted-foreground ml-4">
                    <li>Authentication cookies to keep you logged in</li>
                    <li>Security cookies to protect your data</li>
                    <li>Session cookies to remember your preferences</li>
                  </ul>
                </div>

                <div>
                  <h3 className="text-xl font-semibold mb-2">2.2 Performance Cookies</h3>
                  <p className="text-muted-foreground mb-2">
                    These cookies collect information about how you use our website, helping us improve its performance.
                  </p>
                  <ul className="list-disc list-inside space-y-1 text-muted-foreground ml-4">
                    <li>Analytics cookies to understand user behavior</li>
                    <li>Page load time tracking</li>
                    <li>Error reporting</li>
                  </ul>
                </div>

                <div>
                  <h3 className="text-xl font-semibold mb-2">2.3 Functionality Cookies</h3>
                  <p className="text-muted-foreground mb-2">
                    These cookies remember your choices and preferences to provide a more personalized experience.
                  </p>
                  <ul className="list-disc list-inside space-y-1 text-muted-foreground ml-4">
                    <li>Language preferences</li>
                    <li>Display preferences (dark/light mode)</li>
                    <li>Recently viewed properties</li>
                  </ul>
                </div>

                <div>
                  <h3 className="text-xl font-semibold mb-2">2.4 Marketing Cookies</h3>
                  <p className="text-muted-foreground mb-2">
                    These cookies track your online activity to help deliver more relevant advertising.
                  </p>
                  <ul className="list-disc list-inside space-y-1 text-muted-foreground ml-4">
                    <li>Advertising cookies from our partners</li>
                    <li>Retargeting cookies</li>
                    <li>Social media integration cookies</li>
                  </ul>
                </div>
              </div>
            </section>

            <section>
              <h2 className="text-2xl font-bold mb-4">3. Third-Party Cookies</h2>
              <p className="text-muted-foreground mb-2">
                We use services from third-party providers who may also set cookies on your device:
              </p>
              <ul className="list-disc list-inside space-y-1 text-muted-foreground ml-4">
                <li>Google Analytics for website analytics</li>
                <li>Payment processors for secure transactions</li>
                <li>Social media platforms for sharing features</li>
                <li>Customer support tools</li>
              </ul>
            </section>

            <section>
              <h2 className="text-2xl font-bold mb-4">4. Managing Cookies</h2>
              <p className="text-muted-foreground mb-4">
                You can control and manage cookies in various ways:
              </p>

              <div className="space-y-4">
                <div>
                  <h3 className="text-xl font-semibold mb-2">4.1 Browser Settings</h3>
                  <p className="text-muted-foreground mb-2">Most browsers allow you to:</p>
                  <ul className="list-disc list-inside space-y-1 text-muted-foreground ml-4">
                    <li>View and delete cookies</li>
                    <li>Block third-party cookies</li>
                    <li>Block cookies from specific websites</li>
                    <li>Block all cookies</li>
                    <li>Delete all cookies when you close the browser</li>
                  </ul>
                </div>

                <div>
                  <h3 className="text-xl font-semibold mb-2">4.2 Cookie Consent Tool</h3>
                  <p className="text-muted-foreground">
                    When you first visit our website, we&apos;ll show you a cookie banner where you can choose which types of cookies to accept.
                  </p>
                </div>

                <div>
                  <h3 className="text-xl font-semibold mb-2">4.3 Opt-Out Links</h3>
                  <p className="text-muted-foreground mb-2">
                    You can opt out of interest-based advertising through:
                  </p>
                  <ul className="list-disc list-inside space-y-1 text-muted-foreground ml-4">
                    <li>Network Advertising Initiative (NAI): <a href="http://www.networkadvertising.org/choices/" className="text-primary hover:underline">http://www.networkadvertising.org/choices/</a></li>
                    <li>Digital Advertising Alliance (DAA): <a href="http://www.aboutads.info/choices/" className="text-primary hover:underline">http://www.aboutads.info/choices/</a></li>
                    <li>European Interactive Digital Advertising Alliance (EDAA): <a href="http://www.youronlinechoices.eu/" className="text-primary hover:underline">http://www.youronlinechoices.eu/</a></li>
                  </ul>
                </div>
              </div>
            </section>

            <section>
              <h2 className="text-2xl font-bold mb-4">5. Impact of Disabling Cookies</h2>
              <p className="text-muted-foreground mb-2">
                While you can disable cookies, please note that doing so may affect your experience:
              </p>
              <ul className="list-disc list-inside space-y-1 text-muted-foreground ml-4">
                <li>You may need to log in each time you visit</li>
                <li>Some features may not work properly</li>
                <li>Your preferences won&apos;t be saved</li>
                <li>You may see less relevant content</li>
              </ul>
            </section>

            <section>
              <h2 className="text-2xl font-bold mb-4">6. Cookie Duration</h2>
              <p className="text-muted-foreground mb-2">
                Cookies can be &quot;session cookies&quot; or &quot;persistent cookies&quot;:
              </p>
              <ul className="list-disc list-inside space-y-1 text-muted-foreground ml-4">
                <li><strong>Session cookies:</strong> Deleted when you close your browser</li>
                <li><strong>Persistent cookies:</strong> Remain on your device for a set period or until you delete them</li>
              </ul>
            </section>

            <section>
              <h2 className="text-2xl font-bold mb-4">7. Updates to This Policy</h2>
              <p className="text-muted-foreground">
                We may update this Cookie Policy from time to time. Any changes will be posted on this page with an updated revision date.
              </p>
            </section>

            <section>
              <h2 className="text-2xl font-bold mb-4">8. Contact Us</h2>
              <p className="text-muted-foreground">
                If you have any questions about our use of cookies, please contact us:
              </p>
              <p className="text-muted-foreground mt-2">
                <strong>Email:</strong> privacy@renthub.com<br />
                <strong>Address:</strong> 123 Property Street, London, UK EC1A 1BB
              </p>
            </section>
          </div>
        </div>
      </div>
    </MainLayout>
  </TooltipProvider>
  );
}
