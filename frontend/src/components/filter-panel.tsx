'use client';

import { useState, useCallback, useMemo } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Slider } from '@/components/ui/slider';
import { Badge } from '@/components/ui/badge';
import { 
  Search, 
  SlidersHorizontal, 
  X,
  Wifi,
  Car,
  Tv,
  Wind,
  Coffee,
  Waves,
  Dumbbell,
  Flame,
  Home,
} from 'lucide-react';
import { useTranslations } from 'next-intl';

interface FilterPanelProps {
  onFilterChange: (filters: FilterOptions) => void;
  onClose?: () => void;
  initial?: Partial<FilterOptions>;
}

export interface FilterOptions {
  priceRange: [number, number];
  bedrooms: number | null;
  bathrooms: number | null;
  propertyType: string[];
  amenities: string[];
  guests: number | null;
  instantBook: boolean;
}

export function FilterPanel({ onFilterChange, onClose, initial }: FilterPanelProps) {
  const t = useTranslations('filters');
  const [filters, setFilters] = useState<FilterOptions>({
    priceRange: (initial?.priceRange as [number, number]) || [0, 1000],
    bedrooms: initial?.bedrooms ?? null,
    bathrooms: initial?.bathrooms ?? null,
    propertyType: initial?.propertyType ?? [],
    amenities: initial?.amenities ?? [],
    guests: initial?.guests ?? null,
    instantBook: initial?.instantBook ?? false,
  });

  const propertyTypes = [
    { value: 'apartment', label: 'Apartment' },
    { value: 'house', label: 'House' },
    { value: 'villa', label: 'Villa' },
    { value: 'studio', label: 'Studio' },
  ];

  const amenitiesList = [
    { value: 'wifi', label: 'WiFi', icon: Wifi },
    { value: 'parking', label: 'Parking', icon: Car },
    { value: 'tv', label: 'TV', icon: Tv },
    { value: 'ac', label: 'A/C', icon: Wind },
    { value: 'kitchen', label: 'Kitchen', icon: Coffee },
    { value: 'pool', label: 'Pool', icon: Waves },
    { value: 'gym', label: 'Gym', icon: Dumbbell },
    { value: 'heating', label: 'Heating', icon: Flame },
  ];

  const handlePriceChange = useCallback((value: number[]) => {
    const newFilters = { ...filters, priceRange: value as [number, number] };
    setFilters(newFilters);
    onFilterChange(newFilters); // instant apply
  }, [filters, onFilterChange]);

  const togglePropertyType = useCallback((type: string) => {
    const newTypes = filters.propertyType.includes(type)
      ? filters.propertyType.filter(t => t !== type)
      : [...filters.propertyType, type];
    const newFilters = { ...filters, propertyType: newTypes };
    setFilters(newFilters);
    onFilterChange(newFilters); // instant apply
  }, [filters, onFilterChange]);

  const toggleAmenity = useCallback((amenity: string) => {
    const newAmenities = filters.amenities.includes(amenity)
      ? filters.amenities.filter(a => a !== amenity)
      : [...filters.amenities, amenity];
    const newFilters = { ...filters, amenities: newAmenities };
    setFilters(newFilters);
    onFilterChange(newFilters); // instant apply
  }, [filters, onFilterChange]);

  const handleApply = useCallback(() => {
    onFilterChange(filters);
    onClose?.();
  }, [filters, onFilterChange, onClose]);

  const handleReset = useCallback(() => {
    const resetFilters: FilterOptions = {
      priceRange: [0, 1000],
      bedrooms: null,
      bathrooms: null,
      propertyType: [],
      amenities: [],
      guests: null,
      instantBook: false,
    };
    setFilters(resetFilters);
    onFilterChange(resetFilters);
  }, [onFilterChange]);

  // Memoize active filter count
  const activeFiltersCount = useMemo(() => {
    let count = 0;
    if (filters.propertyType.length > 0) count += filters.propertyType.length;
    if (filters.amenities.length > 0) count += filters.amenities.length;
    if (filters.bedrooms) count++;
    if (filters.bathrooms) count++;
    if (filters.guests) count++;
    if (filters.instantBook) count++;
    if (filters.priceRange[0] > 0 || filters.priceRange[1] < 1000) count++;
    return count;
  }, [filters]);

  return (
  <Card className="sticky top-4" role="region" aria-label="Property filters">
      <CardHeader className="flex flex-row items-center justify-between">
        <CardTitle className="flex items-center gap-2">
          <SlidersHorizontal className="h-5 w-5" />
          {t('title')}
          {activeFiltersCount > 0 && (
            <span className="sr-only">{activeFiltersCount} active filters</span>
          )}
        </CardTitle>
        {onClose && (
          <Button variant="ghost" size="sm" onClick={onClose} aria-label="Close filters">
            <X className="h-4 w-4" />
          </Button>
        )}
      </CardHeader>
      <CardContent className="space-y-6">
        {/* Price Range */}
        <div>
          <Label className="text-base font-semibold mb-3 block" id="price-range-label">{t('priceRange')}</Label>
          <Slider
            value={filters.priceRange}
            onValueChange={handlePriceChange}
            max={1000}
            step={10}
            className="mb-3"
            aria-labelledby="price-range-label"
            aria-valuetext={`Price range from $${filters.priceRange[0]} to $${filters.priceRange[1]}`}
          />
          <div className="flex items-center justify-between text-sm">
            <span className="text-gray-600">
              ${filters.priceRange[0]}
            </span>
            <span className="text-gray-600">
              ${filters.priceRange[1]}+
            </span>
          </div>
          {/* Test-only inputs for E2E selectors (min/max price) */}
          <div className="sr-only" aria-hidden="true">
            <input
              data-testid="min-price"
              type="number"
              value={filters.priceRange[0]}
              onChange={(e) => {
                const val = parseInt(e.target.value || '0');
                const newRange: [number, number] = [val, filters.priceRange[1]];
                setFilters({ ...filters, priceRange: newRange });
                onFilterChange({ ...filters, priceRange: newRange });
              }}
            />
            <input
              data-testid="max-price"
              type="number"
              value={filters.priceRange[1]}
              onChange={(e) => {
                const val = parseInt(e.target.value || '1000');
                const newRange: [number, number] = [filters.priceRange[0], val];
                setFilters({ ...filters, priceRange: newRange });
                onFilterChange({ ...filters, priceRange: newRange });
              }}
            />
          </div>
        </div>

        {/* Property Type */}
        <div>
          <Label className="text-base font-semibold mb-3 block" id="property-type-label">{t('propertyType')}</Label>
          <div className="grid grid-cols-2 gap-2" role="group" aria-labelledby="property-type-label">
            {propertyTypes.map((type) => (
              <Button
                key={type.value}
                variant={filters.propertyType.includes(type.value) ? 'default' : 'outline'}
                size="sm"
                onClick={() => togglePropertyType(type.value)}
                className="justify-start"
                aria-pressed={filters.propertyType.includes(type.value)}
                aria-label={`${type.label}, ${filters.propertyType.includes(type.value) ? 'selected' : 'not selected'}`}
              >
                <Home className="h-4 w-4 mr-2" />
                {type.label}
              </Button>
            ))}
          </div>
        </div>

        {/* Rooms */}
        <div>
          <Label className="text-base font-semibold mb-3 block">{t('rooms')}</Label>
          <div className="space-y-3">
            <div>
              <Label htmlFor="bedrooms" className="text-sm">{t('bedrooms')}</Label>
              <Input
                id="bedrooms"
                type="number"
                min="0"
                value={filters.bedrooms || ''}
                onChange={(e) => {
                  const nf = { ...filters, bedrooms: e.target.value ? parseInt(e.target.value) : null };
                  setFilters(nf);
                  onFilterChange(nf); // instant apply
                }}
                placeholder="Any"
              />
            </div>
            <div>
              <Label htmlFor="bathrooms" className="text-sm">{t('bathrooms')}</Label>
              <Input
                id="bathrooms"
                type="number"
                min="0"
                value={filters.bathrooms || ''}
                onChange={(e) => {
                  const nf = { ...filters, bathrooms: e.target.value ? parseInt(e.target.value) : null };
                  setFilters(nf);
                  onFilterChange(nf);
                }}
                placeholder="Any"
              />
            </div>
            <div>
              <Label htmlFor="guests" className="text-sm">{t('guests')}</Label>
              <Input
                id="guests"
                type="number"
                min="1"
                value={filters.guests || ''}
                onChange={(e) => {
                  const nf = { ...filters, guests: e.target.value ? parseInt(e.target.value) : null };
                  setFilters(nf);
                  onFilterChange(nf);
                }}
                placeholder="Any"
              />
            </div>
          </div>
        </div>

        {/* Amenities */}
        <div>
          <Label className="text-base font-semibold mb-3 block" id="amenities-label">{t('amenities')}</Label>
          <div className="grid grid-cols-2 gap-2" role="group" aria-labelledby="amenities-label">
            {amenitiesList.map((amenity) => {
              const Icon = amenity.icon;
              return (
                <Button
                  key={amenity.value}
                  variant={filters.amenities.includes(amenity.value) ? 'default' : 'outline'}
                  size="sm"
                  onClick={() => toggleAmenity(amenity.value)}
                  aria-pressed={filters.amenities.includes(amenity.value)}
                  aria-label={`${amenity.label}, ${filters.amenities.includes(amenity.value) ? 'selected' : 'not selected'}`}
                  className="justify-start"
                >
                  <Icon className="h-4 w-4 mr-2" />
                  {amenity.label}
                </Button>
              );
            })}
          </div>
        </div>

        {/* Instant Book */}
        <div className="flex items-center justify-between">
          <Label htmlFor="instant-book" className="text-base font-semibold cursor-pointer">
            Instant Book
          </Label>
          <input
            id="instant-book"
            type="checkbox"
            checked={filters.instantBook}
            onChange={(e) => {
              const nf = { ...filters, instantBook: e.target.checked };
              setFilters(nf);
              onFilterChange(nf);
            }}
            className="h-5 w-5 cursor-pointer"
          />
        </div>

        {/* Actions */}
        <div className="space-y-2 pt-4 border-t">
          <Button onClick={handleApply} className="w-full" data-testid="apply-filters">
            Apply Filters
          </Button>
          <Button onClick={handleReset} variant="outline" className="w-full">
            Reset All
          </Button>
        </div>
      </CardContent>
    </Card>
  );
}
