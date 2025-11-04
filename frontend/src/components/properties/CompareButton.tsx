'use client';

import React, { useState } from 'react';
import { useComparison } from '@/contexts/ComparisonContext';
import { toast } from 'react-hot-toast';

interface CompareButtonProps {
  propertyId: number;
  className?: string;
}

export default function CompareButton({ propertyId, className = '' }: CompareButtonProps) {
  const { isInComparison, addToComparison, removeFromComparison, count, maxReached } = useComparison();
  const [loading, setLoading] = useState(false);
  const inComparison = isInComparison(propertyId);

  const handleToggle = async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    
    setLoading(true);
    try {
      if (inComparison) {
        await removeFromComparison(propertyId);
        toast.success('Removed from comparison');
      } else {
        if (maxReached) {
          toast.error('Maximum 4 properties can be compared');
          return;
        }
        await addToComparison(propertyId);
        toast.success('Added to comparison');
      }
    } catch (error: any) {
      toast.error(error.message || 'Failed to update comparison');
    } finally {
      setLoading(false);
    }
  };

  return (
    <button
      onClick={handleToggle}
      disabled={loading || (!inComparison && maxReached)}
      className={`
        inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium
        transition-all duration-200
        ${inComparison 
          ? 'bg-blue-600 text-white hover:bg-blue-700' 
          : 'bg-white text-gray-700 border-2 border-gray-300 hover:border-blue-500 hover:text-blue-600'
        }
        ${loading ? 'opacity-50 cursor-not-allowed' : ''}
        ${!inComparison && maxReached ? 'opacity-50 cursor-not-allowed' : ''}
        ${className}
      `}
      title={!inComparison && maxReached ? 'Maximum 4 properties can be compared' : ''}
    >
      {loading ? (
        <svg className="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      ) : (
        <svg 
          className="w-5 h-5" 
          fill={inComparison ? 'currentColor' : 'none'} 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path 
            strokeLinecap="round" 
            strokeLinejoin="round" 
            strokeWidth={2} 
            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" 
          />
        </svg>
      )}
      <span className="hidden sm:inline">
        {inComparison ? 'Remove from Compare' : 'Compare'}
      </span>
    </button>
  );
}
