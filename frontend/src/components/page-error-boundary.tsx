'use client';

import React, { useState } from 'react';
import { ErrorBoundary } from '@/components/error-boundary';
import { ApiError } from '@/components/api-error';

interface PageErrorBoundaryProps {
  children: React.ReactNode;
  fallbackTitle?: string;
  fallbackDescription?: string;
}

export function PageErrorBoundary({ 
  children, 
  fallbackTitle = "This page encountered an error",
  fallbackDescription = "We're working to fix this issue. Please try refreshing the page."
}: PageErrorBoundaryProps) {
  const [key, setKey] = useState(0);

  const handleReset = () => {
    setKey(prev => prev + 1);
  };

  return (
    <ErrorBoundary 
      key={key}
      onReset={handleReset}
      fallback={
        <div className="container mx-auto px-4 py-16">
          <div className="max-w-2xl mx-auto">
            <ApiError 
              error={fallbackTitle}
              onRetry={handleReset}
            />
          </div>
        </div>
      }
    >
      {children}
    </ErrorBoundary>
  );
}

// Section Error Boundary for smaller components
export function SectionErrorBoundary({ 
  children,
  fallback
}: {
  children: React.ReactNode;
  fallback?: React.ReactNode;
}) {
  return (
    <ErrorBoundary
      fallback={fallback || (
        <div className="p-4 border border-red-200 rounded-lg bg-red-50">
          <p className="text-sm text-red-800">
            This section failed to load. Please refresh the page.
          </p>
        </div>
      )}
    >
      {children}
    </ErrorBoundary>
  );
}
