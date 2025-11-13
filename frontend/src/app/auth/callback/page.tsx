'use client';

import { useEffect, Suspense } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { authService } from '@/lib/api-service';

function OAuthCallbackContent() {
  const router = useRouter();
  const params = useSearchParams();

  useEffect(() => {
    const token = params.get('token');
    const returnTo = params.get('returnTo');
    if (!token) {
      // No token provided - redirect to login
      router.replace('/auth/login');
      return;
    }

    const finalize = async () => {
      try {
        // Store token, fetch user, store user, then redirect
        localStorage.setItem('auth_token', token);
        const user = await authService.me();
        localStorage.setItem('user', JSON.stringify(user));
        router.replace(returnTo || '/dashboard');
      } catch (e) {
        // On error, clear token and go to login
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        router.replace('/auth/login');
      }
    };

    finalize();
  }, [params, router]);

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-12">
        <Card className="max-w-md mx-auto">
          <CardHeader>
            <CardTitle>Signing you in…</CardTitle>
          </CardHeader>
          <CardContent>
            <p className="text-muted-foreground">Completing social login. Please wait…</p>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}

export default function OAuthCallbackPage() {
  return (
    <Suspense fallback={
      <MainLayout>
        <div className="container mx-auto px-4 py-12">
          <Card className="max-w-md mx-auto">
            <CardHeader>
              <CardTitle>Loading…</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-muted-foreground">Please wait…</p>
            </CardContent>
          </Card>
        </div>
      </MainLayout>
    }>
      <OAuthCallbackContent />
    </Suspense>
  );
}
