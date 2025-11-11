'use client';

import React from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { AlertCircle, RefreshCw, ChevronRight } from 'lucide-react';

interface ApiErrorProps {
  error: Error | string;
  onRetry?: () => void;
  showDetails?: boolean;
}

export function ApiError({ error, onRetry, showDetails = false }: ApiErrorProps) {
  const errorMessage = typeof error === 'string' ? error : error.message;
  const isNetworkError = errorMessage.toLowerCase().includes('network') || 
                         errorMessage.toLowerCase().includes('fetch');
  const isAuthError = errorMessage.toLowerCase().includes('401') || 
                      errorMessage.toLowerCase().includes('unauthorized');
  const isNotFoundError = errorMessage.toLowerCase().includes('404') || 
                          errorMessage.toLowerCase().includes('not found');

  const getErrorTitle = () => {
    if (isNetworkError) return 'Connection Error';
    if (isAuthError) return 'Authentication Required';
    if (isNotFoundError) return 'Not Found';
    return 'Something Went Wrong';
  };

  const getErrorDescription = () => {
    if (isNetworkError) return 'Unable to connect to the server. Please check your internet connection.';
    if (isAuthError) return 'Your session has expired. Please log in again.';
    if (isNotFoundError) return 'The requested resource could not be found.';
    return 'An unexpected error occurred. Please try again.';
  };

  return (
    <Card className="border-red-200 bg-red-50">
      <CardHeader>
        <div className="flex items-start gap-3">
          <AlertCircle className="h-5 w-5 text-red-600 mt-0.5 flex-shrink-0" />
          <div className="flex-1">
            <CardTitle className="text-red-900 text-lg">{getErrorTitle()}</CardTitle>
            <CardDescription className="text-red-700 mt-1">
              {getErrorDescription()}
            </CardDescription>
          </div>
        </div>
      </CardHeader>
      {(showDetails || process.env.NODE_ENV === 'development') && (
        <CardContent>
          <div className="bg-white border border-red-200 rounded-md p-3 text-sm">
            <p className="font-semibold text-red-800 mb-1">Error Details:</p>
            <p className="text-red-600 break-words">{errorMessage}</p>
          </div>
        </CardContent>
      )}
      {onRetry && (
        <CardContent className="pt-0">
          <Button onClick={onRetry} variant="outline" className="w-full border-red-300 text-red-700 hover:bg-red-100">
            <RefreshCw className="h-4 w-4 mr-2" />
            Try Again
          </Button>
        </CardContent>
      )}
      {isAuthError && (
        <CardContent className="pt-0">
          <Button 
            onClick={() => window.location.href = '/auth/login'} 
            className="w-full"
          >
            Go to Login
            <ChevronRight className="h-4 w-4 ml-2" />
          </Button>
        </CardContent>
      )}
    </Card>
  );
}

// Inline error display for forms and smaller components
export function InlineError({ message }: { message: string }) {
  return (
    <div className="flex items-center gap-2 text-sm text-red-600 bg-red-50 border border-red-200 rounded-md p-3">
      <AlertCircle className="h-4 w-4 flex-shrink-0" />
      <p>{message}</p>
    </div>
  );
}
