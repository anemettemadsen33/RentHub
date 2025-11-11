import { Suspense, ReactNode } from 'react';

interface SuspenseWrapperProps {
  fallback: ReactNode;
  children: ReactNode;
}

export function SuspenseWrapper({ fallback, children }: SuspenseWrapperProps) {
  return <Suspense fallback={fallback}>{children}</Suspense>;
}

// Re-export Suspense for convenience
export { Suspense };
