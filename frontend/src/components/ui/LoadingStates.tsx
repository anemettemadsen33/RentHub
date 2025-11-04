import React from 'react';

// Spinner Loading Component
export const Spinner: React.FC<{ size?: 'sm' | 'md' | 'lg' }> = ({ size = 'md' }) => {
  const sizeClasses = {
    sm: 'w-4 h-4',
    md: 'w-8 h-8',
    lg: 'w-12 h-12'
  };

  return (
    <div className={`${sizeClasses[size]} animate-spin rounded-full border-4 border-gray-200 border-t-blue-600`} />
  );
};

// Skeleton Screen Component
export const Skeleton: React.FC<{ className?: string }> = ({ className = '' }) => {
  return (
    <div className={`animate-pulse bg-gray-200 rounded ${className}`} />
  );
};

// Property Card Skeleton
export const PropertyCardSkeleton: React.FC = () => {
  return (
    <div className="border rounded-lg p-4 space-y-4">
      <Skeleton className="w-full h-48" />
      <Skeleton className="w-3/4 h-6" />
      <Skeleton className="w-1/2 h-4" />
      <div className="flex space-x-2">
        <Skeleton className="w-20 h-4" />
        <Skeleton className="w-20 h-4" />
        <Skeleton className="w-20 h-4" />
      </div>
      <Skeleton className="w-full h-10" />
    </div>
  );
};

// Table Skeleton
export const TableSkeleton: React.FC<{ rows?: number }> = ({ rows = 5 }) => {
  return (
    <div className="space-y-2">
      {[...Array(rows)].map((_, i) => (
        <div key={i} className="flex space-x-4">
          <Skeleton className="w-1/4 h-12" />
          <Skeleton className="w-1/4 h-12" />
          <Skeleton className="w-1/4 h-12" />
          <Skeleton className="w-1/4 h-12" />
        </div>
      ))}
    </div>
  );
};

// Full Page Loading
export const PageLoading: React.FC<{ message?: string }> = ({ message = 'Loading...' }) => {
  return (
    <div className="fixed inset-0 bg-white bg-opacity-90 flex items-center justify-center z-50">
      <div className="text-center">
        <Spinner size="lg" />
        <p className="mt-4 text-gray-600 text-lg">{message}</p>
      </div>
    </div>
  );
};

// Button Loading State
export const ButtonLoading: React.FC<{ children: React.ReactNode; loading?: boolean }> = ({ 
  children, 
  loading = false 
}) => {
  return (
    <button 
      disabled={loading}
      className={`px-6 py-3 rounded-lg font-medium transition-all ${
        loading 
          ? 'bg-gray-400 cursor-not-allowed' 
          : 'bg-blue-600 hover:bg-blue-700 text-white'
      }`}
    >
      {loading ? (
        <span className="flex items-center justify-center">
          <Spinner size="sm" />
          <span className="ml-2">Loading...</span>
        </span>
      ) : children}
    </button>
  );
};

// Progress Bar
export const ProgressBar: React.FC<{ progress: number; className?: string }> = ({ 
  progress, 
  className = '' 
}) => {
  return (
    <div className={`w-full bg-gray-200 rounded-full h-2 ${className}`}>
      <div 
        className="bg-blue-600 h-2 rounded-full transition-all duration-300"
        style={{ width: `${Math.min(100, Math.max(0, progress))}%` }}
      />
    </div>
  );
};

// Shimmer Effect
export const Shimmer: React.FC<{ className?: string }> = ({ className = '' }) => {
  return (
    <div className={`relative overflow-hidden bg-gray-200 ${className}`}>
      <div className="absolute inset-0 -translate-x-full animate-[shimmer_2s_infinite] bg-gradient-to-r from-gray-200 via-gray-100 to-gray-200" />
    </div>
  );
};

// Pulse Loading
export const PulseLoading: React.FC = () => {
  return (
    <div className="flex space-x-2">
      <div className="w-3 h-3 bg-blue-600 rounded-full animate-bounce" style={{ animationDelay: '0ms' }} />
      <div className="w-3 h-3 bg-blue-600 rounded-full animate-bounce" style={{ animationDelay: '150ms' }} />
      <div className="w-3 h-3 bg-blue-600 rounded-full animate-bounce" style={{ animationDelay: '300ms' }} />
    </div>
  );
};
