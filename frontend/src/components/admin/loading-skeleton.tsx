'use client';

export function AdminLoadingSkeleton() {
  return (
    <div className="space-y-6 animate-pulse">
      {/* Stats Cards Skeleton */}
      <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        {[1, 2, 3, 4].map((i) => (
          <div key={i} className="bg-gray-200 dark:bg-gray-800 h-32 rounded-lg" />
        ))}
      </div>

      {/* Charts Skeleton */}
      <div className="grid gap-4 md:grid-cols-2">
        <div className="bg-gray-200 dark:bg-gray-800 h-80 rounded-lg" />
        <div className="bg-gray-200 dark:bg-gray-800 h-80 rounded-lg" />
      </div>

      {/* Tabs Skeleton */}
      <div className="space-y-4">
        <div className="bg-gray-200 dark:bg-gray-800 h-10 w-96 rounded-lg" />
        <div className="bg-gray-200 dark:bg-gray-800 h-64 rounded-lg" />
      </div>
    </div>
  );
}
