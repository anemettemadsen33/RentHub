import { Navbar } from '@/components/navbar';
import { Footer } from '@/components/footer';
import { SkipToContent } from '@/components/skip-to-content';
import { ComparisonBar } from '@/components/comparison-bar';
import { ThemeToggle } from '@/components/theme-toggle';

interface MainLayoutProps {
  children: React.ReactNode;
}

export function MainLayout({ children }: MainLayoutProps) {
  return (
    <div className="flex min-h-screen flex-col pb-0 md:pb-0">
      <SkipToContent />
      <Navbar />
      <div className="absolute right-4 top-3 z-40">
        <ThemeToggle />
      </div>
      <main id="main-content" className="flex-1 pb-20 md:pb-0" role="main">
        {children}
      </main>
      <Footer />
      <ComparisonBar />
    </div>
  );
}
