import Link from 'next/link';

export function Footer() {
  return (
    <footer className="border-t bg-muted/30" role="contentinfo" aria-label="Site footer">
      <div className="container mx-auto px-4 py-16">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-12 lg:gap-16">
          <div>
            <div className="flex items-center gap-2 font-bold text-xl mb-4">
              <div className="h-8 w-8 rounded-lg bg-primary flex items-center justify-center">
                <span className="text-primary-foreground text-sm">R</span>
              </div>
              <span>RentHub</span>
            </div>
            <p className="text-sm text-muted-foreground leading-relaxed">
              Your trusted platform for finding and listing rental properties worldwide.
            </p>
          </div>
          
          <nav aria-label="Company links">
            <h4 className="font-semibold text-sm mb-4">Company</h4>
            <ul className="space-y-2 text-sm">
              <li><Link href="/about" className="text-muted-foreground hover:text-primary transition-colors">About Us</Link></li>
              <li><Link href="/careers" className="text-muted-foreground hover:text-primary transition-colors">Careers</Link></li>
              <li><Link href="/press" className="text-muted-foreground hover:text-primary transition-colors">Press</Link></li>
            </ul>
          </nav>
          
          <nav aria-label="Support links">
            <h4 className="font-semibold text-sm mb-4">Support</h4>
            <ul className="space-y-2 text-sm">
              <li><Link href="/help" className="text-muted-foreground hover:text-primary transition-colors">Help Center</Link></li>
              <li><Link href="/contact" className="text-muted-foreground hover:text-primary transition-colors">Contact Us</Link></li>
              <li><Link href="/faq" className="text-muted-foreground hover:text-primary transition-colors">FAQ</Link></li>
            </ul>
          </nav>
          
          <nav aria-label="Legal links">
            <h4 className="font-semibold text-sm mb-4">Legal</h4>
            <ul className="space-y-2 text-sm">
              <li><Link href="/terms" className="text-muted-foreground hover:text-primary transition-colors">Terms of Service</Link></li>
              <li><Link href="/privacy" className="text-muted-foreground hover:text-primary transition-colors">Privacy Policy</Link></li>
              <li><Link href="/cookies" className="text-muted-foreground hover:text-primary transition-colors">Cookie Policy</Link></li>
            </ul>
          </nav>
        </div>
        
        <div className="mt-8 pt-8 border-t text-center text-sm text-muted-foreground">
          <div className="flex flex-col md:flex-row items-center justify-between gap-4">
            <p>&copy; {new Date().getFullYear()} RentHub. All rights reserved.</p>
            <div className="flex items-center gap-4">
              <Link href="https://twitter.com" className="hover:text-primary transition-colors">Twitter</Link>
              <Link href="https://github.com" className="hover:text-primary transition-colors">GitHub</Link>
              <Link href="https://linkedin.com" className="hover:text-primary transition-colors">LinkedIn</Link>
            </div>
          </div>
        </div>
      </div>
    </footer>
  );
}
