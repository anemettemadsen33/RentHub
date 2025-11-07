'use client';

import React, { createContext, useContext, useState, useEffect, useCallback, ReactNode } from 'react';
import { api } from '@/services/api';

interface Property {
  id: number;
  title: string;
  type: string;
  price_per_night: number;
  price_per_month: number;
  bedrooms: number;
  bathrooms: number;
  guests: number;
  area_sqm: number;
  city: string;
  country: string;
  images: string[];
  average_rating: number;
  review_count: number;
}

interface ComparisonContextType {
  comparisonIds: number[];
  properties: Property[];
  addToComparison: (propertyId: number) => Promise<void>;
  removeFromComparison: (propertyId: number) => Promise<void>;
  clearComparison: () => Promise<void>;
  isInComparison: (propertyId: number) => boolean;
  loadComparison: () => Promise<void>;
  count: number;
  maxReached: boolean;
}

const ComparisonContext = createContext<ComparisonContextType | undefined>(undefined);

const MAX_COMPARISONS = 4;

export function ComparisonProvider({ children }: { children: ReactNode }) {
  const [comparisonIds, setComparisonIds] = useState<number[]>([]);
  const [properties, setProperties] = useState<Property[]>([]);
  const [sessionId, setSessionId] = useState<string>('');

  const loadComparison = useCallback(async () => {
    try {
      const sid = localStorage.getItem('comparison-session-id');
      const response = await api.get('/property-comparison', {
        headers: sid ? { 'X-Session-Id': sid } : {},
      });

      if (response.data) {
        setComparisonIds(response.data.property_ids || []);
        setProperties(response.data.properties || []);
      }
    } catch (error) {
      console.error('Failed to load comparison:', error);
    }
  }, []);

  useEffect(() => {
    // Generate or retrieve session ID
    let sid = localStorage.getItem('comparison-session-id');
    if (!sid) {
      sid = `session-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
      localStorage.setItem('comparison-session-id', sid);
    }
    setSessionId(sid);

    // Load comparison from server
    loadComparison();
  }, [loadComparison]);

  const addToComparison = async (propertyId: number) => {
    if (comparisonIds.includes(propertyId)) {
      return;
    }

    if (comparisonIds.length >= MAX_COMPARISONS) {
      throw new Error(`Maximum ${MAX_COMPARISONS} properties can be compared at once`);
    }

    try {
      const sid = localStorage.getItem('comparison-session-id');
      const response = await api.post('/property-comparison/add', 
        { property_id: propertyId },
        {
          headers: sid ? { 'X-Session-Id': sid } : {},
        }
      );

      if (response.data.session_id && !localStorage.getItem('comparison-session-id')) {
        localStorage.setItem('comparison-session-id', response.data.session_id);
      }

      await loadComparison();
    } catch (error: any) {
      throw new Error(error.response?.data?.message || 'Failed to add to comparison');
    }
  };

  const removeFromComparison = async (propertyId: number) => {
    try {
      const sid = localStorage.getItem('comparison-session-id');
      await api.delete(`/property-comparison/remove/${propertyId}`, {
        headers: sid ? { 'X-Session-Id': sid } : {},
      });

      setComparisonIds(prev => prev.filter(id => id !== propertyId));
      setProperties(prev => prev.filter(p => p.id !== propertyId));
    } catch (error) {
      console.error('Failed to remove from comparison:', error);
      throw error;
    }
  };

  const clearComparison = async () => {
    try {
      const sid = localStorage.getItem('comparison-session-id');
      await api.delete('/property-comparison/clear', {
        headers: sid ? { 'X-Session-Id': sid } : {},
      });

      setComparisonIds([]);
      setProperties([]);
    } catch (error) {
      console.error('Failed to clear comparison:', error);
      throw error;
    }
  };

  const isInComparison = (propertyId: number): boolean => {
    return comparisonIds.includes(propertyId);
  };

  return (
    <ComparisonContext.Provider
      value={{
        comparisonIds,
        properties,
        addToComparison,
        removeFromComparison,
        clearComparison,
        isInComparison,
        loadComparison,
        count: comparisonIds.length,
        maxReached: comparisonIds.length >= MAX_COMPARISONS,
      }}
    >
      {children}
    </ComparisonContext.Provider>
  );
}

export function useComparison() {
  const context = useContext(ComparisonContext);
  if (context === undefined) {
    throw new Error('useComparison must be used within a ComparisonProvider');
  }
  return context;
}
