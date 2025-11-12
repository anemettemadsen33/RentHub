'use client';

import { useState } from 'react';
import { MainLayout } from '@/components/layouts/main-layout';
import { ErrorBoundary } from '@/components/error-boundary';
import { ApiError, InlineError } from '@/components/api-error';
import { SectionErrorBoundary } from '@/components/page-error-boundary';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { AlertTriangle, Bug, Zap } from 'lucide-react';

// Component that throws an error when clicked
function CrashButton() {
  const [shouldCrash, setShouldCrash] = useState(false);

  if (shouldCrash) {
    throw new Error('This is a simulated component crash!');
  }

  return (
    <Button variant="destructive" onClick={() => setShouldCrash(true)}>
      <Bug className="h-4 w-4 mr-2" />
      Crash This Section
    </Button>
  );
}

export default function ErrorDemoPage() {
  const [showNetworkError, setShowNetworkError] = useState(false);
  const [showAuthError, setShowAuthError] = useState(false);
  const [showNotFoundError, setShowNotFoundError] = useState(false);

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 space-y-8 max-w-4xl">
        <div className="space-y-2">
          <h1 className="text-3xl font-bold">Error Handling Demo</h1>
          <p className="text-gray-600">
            This page demonstrates the various error handling mechanisms in RentHub
          </p>
        </div>

        {/* Error Boundary Demo */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <AlertTriangle className="h-5 w-5 text-orange-500" />
              Error Boundary Demo
            </CardTitle>
            <CardDescription>
              Click the button to simulate a component crash. The Error Boundary will catch it.
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <SectionErrorBoundary>
              <div className="p-4 border rounded-lg bg-blue-50">
                <p className="text-sm mb-3 text-blue-900">
                  This section is wrapped in an Error Boundary. When the component crashes,
                  only this section will show an error - the rest of the page continues working.
                </p>
                <CrashButton />
              </div>
            </SectionErrorBoundary>
          </CardContent>
        </Card>

        {/* API Error Types */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Zap className="h-5 w-5 text-blue-500" />
              API Error Components
            </CardTitle>
            <CardDescription>
              Different types of API errors with contextual messages
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4">
              <div>
                <Button
                  onClick={() => setShowNetworkError(!showNetworkError)}
                  variant="outline"
                  className="mb-3"
                >
                  {showNetworkError ? 'Hide' : 'Show'} Network Error
                </Button>
                {showNetworkError && (
                  <ApiError
                    error="Network error: Unable to reach the server"
                    onRetry={() => setShowNetworkError(false)}
                  />
                )}
              </div>

              <div>
                <Button
                  onClick={() => setShowAuthError(!showAuthError)}
                  variant="outline"
                  className="mb-3"
                >
                  {showAuthError ? 'Hide' : 'Show'} Authentication Error
                </Button>
                {showAuthError && (
                  <ApiError error="401 Unauthorized: Please log in again" />
                )}
              </div>

              <div>
                <Button
                  onClick={() => setShowNotFoundError(!showNotFoundError)}
                  variant="outline"
                  className="mb-3"
                >
                  {showNotFoundError ? 'Hide' : 'Show'} Not Found Error
                </Button>
                {showNotFoundError && (
                  <ApiError error="404 Not Found: The requested resource doesn't exist" />
                )}
              </div>

              <div>
                <h3 className="text-sm font-semibold mb-2">Inline Error (for forms)</h3>
                <InlineError message="This field is required and cannot be empty" />
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Retry Mechanism Info */}
        <Card className="border-green-200 bg-green-50">
          <CardHeader>
            <CardTitle className="text-green-900">Automatic Retry Mechanism</CardTitle>
            <CardDescription className="text-green-700">
              All API calls automatically retry on failure
            </CardDescription>
          </CardHeader>
          <CardContent>
            <ul className="space-y-2 text-sm text-green-800">
              <li className="flex items-start gap-2">
                <span className="font-bold">•</span>
                <span>Network errors automatically retry 3 times with exponential backoff</span>
              </li>
              <li className="flex items-start gap-2">
                <span className="font-bold">•</span>
                <span>Server errors (5xx) automatically retry before showing error</span>
              </li>
              <li className="flex items-start gap-2">
                <span className="font-bold">•</span>
                <span>401 errors immediately redirect to login (no retry)</span>
              </li>
              <li className="flex items-start gap-2">
                <span className="font-bold">•</span>
                <span>Retry delay: 1s, 2s, 3s (exponential backoff)</span>
              </li>
            </ul>
          </CardContent>
        </Card>

        {/* Implementation Notes */}
        <Card className="border-purple-200 bg-purple-50">
          <CardHeader>
            <CardTitle className="text-purple-900">Implementation Notes</CardTitle>
          </CardHeader>
          <CardContent className="space-y-3 text-sm text-purple-800">
            <div>
              <p className="font-semibold mb-1">Global Error Boundary:</p>
              <code className="bg-white px-2 py-1 rounded text-xs">
                app/layout.tsx → Wraps entire app
              </code>
            </div>
            <div>
              <p className="font-semibold mb-1">Page Error Boundaries:</p>
              <code className="bg-white px-2 py-1 rounded text-xs">
                PageErrorBoundary → Wraps individual pages
              </code>
            </div>
            <div>
              <p className="font-semibold mb-1">Section Error Boundaries:</p>
              <code className="bg-white px-2 py-1 rounded text-xs">
                SectionErrorBoundary → Wraps risky components
              </code>
            </div>
            <div>
              <p className="font-semibold mb-1">Enhanced API Client:</p>
              <code className="bg-white px-2 py-1 rounded text-xs">
                lib/api-client-enhanced.ts → Auto-retry + error enhancement
              </code>
            </div>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
