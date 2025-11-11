import Link from 'next/link';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Home, Search, ArrowLeft } from 'lucide-react';

export default function NotFound() {
  return (
    <div className="min-h-screen flex items-center justify-center p-4 bg-background">
      <Card className="max-w-md w-full">
        <CardContent className="pt-6 text-center space-y-6">
          <div className="space-y-2">
            <h1 className="text-8xl font-bold bg-gradient-to-r from-primary to-primary/50 bg-clip-text text-transparent">
              404
            </h1>
            <h2 className="text-2xl font-bold tracking-tight">Page Not Found</h2>
            <p className="text-muted-foreground text-sm">
              The page you&apos;re looking for doesn&apos;t exist or has been moved.
            </p>
          </div>

          <div className="flex flex-col sm:flex-row gap-3 justify-center pt-4">
            <Link href="/" className="flex-1 sm:flex-initial">
              <Button className="w-full gap-2">
                <Home className="h-4 w-4" />
                Go Home
              </Button>
            </Link>
            <Link href="/properties" className="flex-1 sm:flex-initial">
              <Button variant="outline" className="w-full gap-2">
                <Search className="h-4 w-4" />
                Browse Properties
              </Button>
            </Link>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
