import React from 'react';
import { XCircleIcon, ExclamationTriangleIcon } from '@heroicons/react/24/outline';

interface ErrorMessageProps {
  title?: string;
  message: string;
  onRetry?: () => void;
  variant?: 'error' | 'warning';
}

export const ErrorMessage: React.FC<ErrorMessageProps> = ({
  title = 'Error',
  message,
  onRetry,
  variant = 'error',
}) => {
  const Icon = variant === 'error' ? XCircleIcon : ExclamationTriangleIcon;
  const bgColor = variant === 'error' ? 'bg-red-50' : 'bg-yellow-50';
  const iconColor = variant === 'error' ? 'text-red-600' : 'text-yellow-600';
  const textColor = variant === 'error' ? 'text-red-800' : 'text-yellow-800';
  const buttonColor = variant === 'error' ? 'bg-red-600 hover:bg-red-700' : 'bg-yellow-600 hover:bg-yellow-700';

  return (
    <div className={`${bgColor} border-l-4 ${variant === 'error' ? 'border-red-600' : 'border-yellow-600'} p-4 rounded-md`}>
      <div className="flex items-start">
        <Icon className={`h-5 w-5 ${iconColor} mt-0.5`} />
        <div className="ml-3 flex-1">
          <h3 className={`text-sm font-medium ${textColor}`}>{title}</h3>
          <p className={`text-sm mt-1 ${textColor} opacity-90`}>{message}</p>
          {onRetry && (
            <button
              onClick={onRetry}
              className={`mt-3 text-sm font-medium ${buttonColor} text-white px-4 py-2 rounded-md transition-colors`}
            >
              Try Again
            </button>
          )}
        </div>
      </div>
    </div>
  );
};

export const ErrorBoundaryFallback: React.FC<{ 
  error: Error; 
  resetErrorBoundary: () => void 
}> = ({ error, resetErrorBoundary }) => {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 px-4">
      <div className="max-w-md w-full bg-white shadow-lg rounded-lg p-6">
        <div className="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full">
          <XCircleIcon className="h-6 w-6 text-red-600" />
        </div>
        <h2 className="mt-4 text-center text-2xl font-bold text-gray-900">
          Oops! Something went wrong
        </h2>
        <p className="mt-2 text-center text-sm text-gray-600">
          We're sorry for the inconvenience. Please try again.
        </p>
        {process.env.NODE_ENV === 'development' && (
          <div className="mt-4 p-3 bg-gray-100 rounded text-xs text-gray-700 overflow-auto max-h-40">
            <pre>{error.message}</pre>
          </div>
        )}
        <button
          onClick={resetErrorBoundary}
          className="mt-6 w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors"
        >
          Go Back Home
        </button>
      </div>
    </div>
  );
};

export const NotFound: React.FC<{ 
  title?: string;
  message?: string;
  actionLabel?: string;
  onAction?: () => void;
}> = ({
  title = '404 - Page Not Found',
  message = 'The page you are looking for does not exist.',
  actionLabel = 'Go Home',
  onAction,
}) => {
  return (
    <div className="min-h-screen flex items-center justify-center bg-gray-50 px-4">
      <div className="text-center">
        <h1 className="text-9xl font-bold text-gray-300">404</h1>
        <h2 className="mt-4 text-3xl font-bold text-gray-900">{title}</h2>
        <p className="mt-2 text-lg text-gray-600">{message}</p>
        {onAction && (
          <button
            onClick={onAction}
            className="mt-6 bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-md transition-colors"
          >
            {actionLabel}
          </button>
        )}
      </div>
    </div>
  );
};

export const EmptyState: React.FC<{
  icon?: React.ReactNode;
  title: string;
  message: string;
  actionLabel?: string;
  onAction?: () => void;
}> = ({ icon, title, message, actionLabel, onAction }) => {
  return (
    <div className="text-center py-12">
      {icon && <div className="flex justify-center mb-4">{icon}</div>}
      <h3 className="text-lg font-medium text-gray-900">{title}</h3>
      <p className="mt-2 text-sm text-gray-500">{message}</p>
      {onAction && actionLabel && (
        <button
          onClick={onAction}
          className="mt-6 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors"
        >
          {actionLabel}
        </button>
      )}
    </div>
  );
};
