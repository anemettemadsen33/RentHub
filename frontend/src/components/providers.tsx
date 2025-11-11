'use client';

import { Toaster } from '@/components/ui/toaster';
import { AuthProvider, useAuth } from '@/contexts/auth-context';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { NotificationProvider } from '@/contexts/notification-context';
import { ErrorBoundary } from '@/components/error-boundary';
import { useEffect } from 'react';
import { initAnalyticsConsentFromStorage, setAnalyticsUserContext } from '@/lib/analytics-client';

export function Providers({ children }: { children: React.ReactNode }) {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        retry: 1,
        refetchOnWindowFocus: false,
      },
    },
  });
  return (
    <ErrorBoundary>
      <AuthProvider>
        <AnalyticsUserInitializer>
          <QueryClientProvider client={queryClient}>
            <NotificationProvider>
              {children}
              <Toaster />
            </NotificationProvider>
          </QueryClientProvider>
        </AnalyticsUserInitializer>
      </AuthProvider>
    </ErrorBoundary>
  );
}

function AnalyticsUserInitializer({ children }: { children: React.ReactNode }) {
  const { user } = useAuth();
  useEffect(() => {
    // Initialize consent from storage/cookie on first mount
    initAnalyticsConsentFromStorage();
    if (user) {
      setAnalyticsUserContext({ id: user.id, role: (user as any)?.role });
    } else {
      setAnalyticsUserContext(null);
    }
  }, [user]);
  return <>{children}</>;
}
