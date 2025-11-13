"use client";

import { useState } from 'react';
import { useTranslations } from '@/lib/i18n-temp';
import { Button } from '@/components/ui/button';
import { notify } from '@/lib/notify';
import apiClient from '@/lib/api-client';
import { Scale, Check } from 'lucide-react';
import { cn } from '@/lib/utils';

interface CompareButtonProps {
  propertyId: number;
  variant?: 'default' | 'outline' | 'ghost';
  size?: 'default' | 'sm' | 'lg' | 'icon';
  showLabel?: boolean;
  onToggle?: (isComparing: boolean) => void;
}

export function CompareButton({
  propertyId,
  variant = 'outline',
  size = 'sm',
  showLabel = true,
  onToggle,
}: CompareButtonProps) {
  const [isComparing, setIsComparing] = useState(false);
  const [loading, setLoading] = useState(false);
  const tNotify = useTranslations('notify');
  const tButton = useTranslations('compareButton');

  const toggleCompare = async () => {
    setLoading(true);
    try {
      if (isComparing) {
        await apiClient.delete(`/property-comparison/remove/${propertyId}`);
        setIsComparing(false);
        notify.info({
          title: tNotify('propertyRemovedTitle'),
          description: tNotify('propertyRemoved'),
        });
      } else {
        await apiClient.post('/property-comparison/add', { property_id: propertyId });
        setIsComparing(true);
        notify.success({
          title: tNotify('addedToComparisonTitle'),
          description: tNotify('addedToComparison'),
        });
      }
      onToggle?.(isComparing);
    } catch (error: any) {
      notify.error({
        title: error.response?.data?.message || tNotify('errorUpdateSearch'),
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Button
      variant={variant}
      size={size}
      onClick={toggleCompare}
      disabled={loading}
      aria-label={isComparing ? tButton('comparing') : tButton('compare')}
      className={cn(
        'transition-colors',
        isComparing && 'bg-primary text-primary-foreground'
      )}
    >
      {isComparing ? (
        <Check className={cn('h-4 w-4', showLabel && 'mr-2')} aria-hidden="true" />
      ) : (
        <Scale className={cn('h-4 w-4', showLabel && 'mr-2')} aria-hidden="true" />
      )}
      {showLabel && (isComparing ? tButton('comparing') : tButton('compare'))}
    </Button>
  );
}
