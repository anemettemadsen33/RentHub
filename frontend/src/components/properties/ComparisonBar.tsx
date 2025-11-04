'use client';

import React from 'react';
import { useComparison } from '@/contexts/ComparisonContext';
import { useRouter } from 'next/navigation';
import Image from 'next/image';

export default function ComparisonBar() {
  const { properties, count, removeFromComparison, clearComparison } = useComparison();
  const router = useRouter();

  if (count === 0) {
    return null;
  }

  const handleCompare = () => {
    const propertyIds = properties.map(p => p.id).join(',');
    router.push(`/compare?ids=${propertyIds}`);
  };

  return (
    <div className="fixed bottom-0 left-0 right-0 z-50 bg-white border-t-2 border-gray-200 shadow-2xl">
      <div className="container mx-auto px-4 py-4">
        <div className="flex items-center justify-between gap-4">
          <div className="flex items-center gap-4 flex-1 overflow-x-auto">
            <div className="flex items-center gap-2 font-semibold text-gray-800 whitespace-nowrap">
              <svg className="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <span>Compare ({count}/4)</span>
            </div>

            <div className="flex gap-3 overflow-x-auto pb-2">
              {properties.map((property) => (
                <div
                  key={property.id}
                  className="relative flex-shrink-0 bg-gray-50 rounded-lg p-2 border border-gray-200"
                >
                  <button
                    onClick={() => removeFromComparison(property.id)}
                    className="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors z-10"
                    title="Remove"
                  >
                    ×
                  </button>
                  
                  <div className="flex items-center gap-3">
                    {property.images?.[0] ? (
                      <Image
                        src={property.images[0]}
                        alt={property.title}
                        width={60}
                        height={60}
                        className="w-16 h-16 object-cover rounded"
                      />
                    ) : (
                      <div className="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                        <svg className="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                      </div>
                    )}
                    
                    <div className="min-w-0">
                      <h4 className="font-medium text-sm text-gray-900 truncate max-w-[150px]">
                        {property.title}
                      </h4>
                      <p className="text-xs text-gray-600">
                        €{property.price_per_night}/night
                      </p>
                      <p className="text-xs text-gray-500">
                        {property.city}, {property.country}
                      </p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </div>

          <div className="flex items-center gap-3 flex-shrink-0">
            <button
              onClick={clearComparison}
              className="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
            >
              Clear All
            </button>
            
            <button
              onClick={handleCompare}
              disabled={count < 2}
              className={`
                px-6 py-3 rounded-lg font-semibold text-white transition-colors
                ${count >= 2 
                  ? 'bg-blue-600 hover:bg-blue-700 cursor-pointer' 
                  : 'bg-gray-300 cursor-not-allowed'
                }
              `}
            >
              Compare Now
              {count >= 2 && (
                <svg className="inline-block w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              )}
            </button>
          </div>
        </div>

        {count < 2 && (
          <p className="text-sm text-gray-500 mt-2 text-center">
            Add at least 2 properties to compare
          </p>
        )}
      </div>
    </div>
  );
}
