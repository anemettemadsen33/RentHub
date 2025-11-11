import React from 'react';
import { PropertyCard as BasePropertyCard } from './property-card';
import { Property } from '@/types';

interface PropertyCardProps {
  property: Property;
  onFavorite?: (id: number) => void;
  isFavorite?: boolean;
  className?: string;
}

/**
 * Memoized PropertyCard component to prevent unnecessary re-renders
 * Only re-renders when property data, favorite status, or className changes
 */
export const MemoizedPropertyCard = React.memo(
  BasePropertyCard,
  (prevProps, nextProps) => {
    return (
      prevProps.property.id === nextProps.property.id &&
      prevProps.isFavorite === nextProps.isFavorite &&
      prevProps.className === nextProps.className &&
      prevProps.property.updated_at === nextProps.property.updated_at
    );
  }
);

MemoizedPropertyCard.displayName = 'MemoizedPropertyCard';
