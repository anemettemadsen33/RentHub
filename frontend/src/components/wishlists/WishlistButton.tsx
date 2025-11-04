'use client';

import { useState, useEffect } from 'react';
import { Heart } from 'lucide-react';
import { Button } from '@/components/ui/Button';
import toast from 'react-hot-toast';
import { togglePropertyInWishlist, checkPropertyInWishlist } from '@/lib/api/wishlists';

interface WishlistButtonProps {
  propertyId: number;
  variant?: 'default' | 'icon';
  size?: 'sm' | 'md' | 'lg';
  className?: string;
}

export default function WishlistButton({
  propertyId,
  variant = 'icon',
  size = 'md',
  className = '',
}: WishlistButtonProps) {
  const [isFavorite, setIsFavorite] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  useEffect(() => {
    checkStatus();
  }, [propertyId]);

  const checkStatus = async () => {
    try {
      const result = await checkPropertyInWishlist(propertyId);
      setIsFavorite(result.in_wishlist);
    } catch (error) {
      console.error('Error checking wishlist status:', error);
    }
  };

  const handleToggle = async (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();

    setIsLoading(true);
    try {
      const result = await togglePropertyInWishlist(propertyId);
      setIsFavorite(result.action === 'added');

      toast.success(
        result.action === 'added'
          ? 'Property saved to your favorites'
          : 'Property removed from your favorites'
      );
    } catch (error: any) {
      if (error.response?.status === 401) {
        toast.error('Please login to save properties to your wishlist');
      } else {
        toast.error('Failed to update wishlist');
      }
    } finally {
      setIsLoading(false);
    }
  };

  if (variant === 'icon') {
    return (
      <Button
        variant="ghost"
        size="sm"
        onClick={handleToggle}
        disabled={isLoading}
        className={`rounded-full ${className}`}
        aria-label={isFavorite ? 'Remove from wishlist' : 'Add to wishlist'}
      >
        <Heart
          className={`h-5 w-5 transition-colors ${
            isFavorite ? 'fill-red-500 text-red-500' : 'text-gray-600'
          }`}
        />
      </Button>
    );
  }

  return (
    <Button
      variant={isFavorite ? 'default' : 'outline'}
      size={size}
      onClick={handleToggle}
      disabled={isLoading}
      className={className}
    >
      <Heart
        className={`mr-2 h-4 w-4 ${isFavorite ? 'fill-current' : ''}`}
      />
      {isFavorite ? 'Saved' : 'Save'}
    </Button>
  );
}
