"use client";

export function HostLoadingSkeleton() {
  return (
    <div className="animate-pulse space-y-8">
      <div className="h-8 bg-gray-200 rounded w-1/3" />
      <div className="grid md:grid-cols-4 gap-6">
        {[...Array(4)].map((_, i) => (
          <div key={i} className="h-32 bg-gray-200 rounded" />
        ))}
      </div>
      <div className="h-64 bg-gray-200 rounded" />
    </div>
  );
}
