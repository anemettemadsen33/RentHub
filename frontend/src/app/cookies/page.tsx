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
        <div className="max-w-4xl mx-auto prose prose-lg dark:prose-invert animate-fade-in-up" style={{ animationDelay: '0ms' }}>
          <h1 className="animate-fade-in" style={{ animationDelay: '0ms' }}>Cookie Policy</h1>
          <p className="text-muted-foreground animate-fade-in" style={{ animationDelay: '100ms' }}>Last updated: November 7, 2025</p>

          <h2>1. What Are Cookies?</h2>
          <p>
            Cookies are small text files that are placed on your device when you visit our website. They help us provide you with a better experience by remembering your preferences and understanding how you use our service.
          </p>

          <h2>2. How We Use Cookies</h2>
          <p>We use cookies for several purposes:</p>

          <h3>2.1 Essential Cookies</h3>
          <p>
            These cookies are necessary for the website to function properly. They enable core functionality such as security, network management, and accessibility.
          </p>
          <ul>
            <li>Authentication cookies to keep you logged in</li>
            <li>Security cookies to protect your data</li>
            <li>Session cookies to remember your preferences</li>
          </ul>

          <h3>2.2 Performance Cookies</h3>
          <p>
            These cookies collect information about how you use our website, helping us improve its performance.
          </p>
          <ul>
            <li>Analytics cookies to understand user behavior</li>
            <li>Page load time tracking</li>
            <li>Error reporting</li>
          </ul>

          <h3>2.3 Functionality Cookies</h3>
          <p>
            These cookies remember your choices and preferences to provide a more personalized experience.
          </p>
          <ul>
            <li>Language preferences</li>
            <li>Display preferences (dark/light mode)</li>
            <li>Recently viewed properties</li>
          </ul>

          <h3>2.4 Marketing Cookies</h3>
          <p>
            These cookies track your online activity to help deliver more relevant advertising.
          </p>
          <ul>
            <li>Advertising cookies from our partners</li>
            <li>Retargeting cookies</li>
            <li>Social media integration cookies</li>
          </ul>

          <h2>3. Third-Party Cookies</h2>
          <p>
            We use services from third-party providers who may also set cookies on your device:
          </p>
          <ul>
            <li>Google Analytics for website analytics</li>
            <li>Payment processors for secure transactions</li>
            <li>Social media platforms for sharing features</li>
            <li>Customer support tools</li>
          </ul>

          <h2>4. Managing Cookies</h2>
          <p>
            You can control and manage cookies in various ways:
          </p>

          <h3>4.1 Browser Settings</h3>
          <p>
            Most browsers allow you to:
          </p>
          <ul>
            <li>View and delete cookies</li>
            <li>Block third-party cookies</li>
            <li>Block cookies from specific websites</li>
            <li>Block all cookies</li>
            <li>Delete all cookies when you close the browser</li>
          </ul>

          <h3>4.2 Cookie Consent Tool</h3>
          <p>
            When you first visit our website, we&apos;ll show you a cookie banner where you can choose which types of cookies to accept.
          </p>

          <h3>4.3 Opt-Out Links</h3>
          <p>
            You can opt out of interest-based advertising through:
          </p>
          <ul>
            <li>Network Advertising Initiative (NAI): <a href="http://www.networkadvertising.org/choices/">http://www.networkadvertising.org/choices/</a></li>
            <li>Digital Advertising Alliance (DAA): <a href="http://www.aboutads.info/choices/">http://www.aboutads.info/choices/</a></li>
            <li>European Interactive Digital Advertising Alliance (EDAA): <a href="http://www.youronlinechoices.eu/">http://www.youronlinechoices.eu/</a></li>
          </ul>

          <h2>5. Impact of Disabling Cookies</h2>
          <p>
            While you can disable cookies, please note that doing so may affect your experience:
          </p>
          <ul>
            <li>You may need to log in each time you visit</li>
            <li>Some features may not work properly</li>
            <li>Your preferences won&apos;t be saved</li>
            <li>You may see less relevant content</li>
          </ul>

          <h2>6. Cookie Duration</h2>
          <p>
            Cookies can be &quot;session cookies&quot; or &quot;persistent cookies&quot;:
          </p>
          <ul>
            <li><strong>Session cookies:</strong> Deleted when you close your browser</li>
            <li><strong>Persistent cookies:</strong> Remain on your device for a set period or until you delete them</li>
          </ul>

          <h2>7. Updates to This Policy</h2>
          <p>
            We may update this Cookie Policy from time to time. Any changes will be posted on this page with an updated revision date.
          </p>

          <h2>8. Contact Us</h2>
          <p>
            If you have any questions about our use of cookies, please contact us:
            <br />
            Email: privacy@renthub.com
            <br />
            Address: 123 Property Street, London, UK EC1A 1BB
          </p>
        </div>
      </div>
    </MainLayout>
  </TooltipProvider>
  );
}
