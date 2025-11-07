'use client';

import { useState } from 'react';
import { PropertyFilters } from '@/lib/api/properties';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Search, Loader2, MapPin, Users, Bed, DollarSign } from 'lucide-react';

interface SearchBarProps {
  onSearch: (filters: PropertyFilters) => void;
  loading?: boolean;
}

export default function SearchBar({ onSearch, loading }: SearchBarProps) {
  const [filters, setFilters] = useState<PropertyFilters>({
    search: '',
    city: '',
    guests: undefined,
    min_price: undefined,
    max_price: undefined,
    bedrooms: undefined,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSearch(filters);
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFilters({
      ...filters,
      [name]: value === '' ? undefined : name === 'search' || name === 'city' ? value : parseInt(value),
    });
  };

  return (
    <Card className="shadow-xl border-2">
      <CardContent className="p-6">
        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Main Search */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div className="md:col-span-2 space-y-2">
              <Label htmlFor="search" className="flex items-center gap-2">
                <MapPin className="h-4 w-4 text-primary" />
                Location or Property Name
              </Label>
              <Input
                id="search"
                type="text"
                name="search"
                value={filters.search}
                onChange={handleChange}
                placeholder="e.g., New York, Downtown, Cozy Apartment..."
                className="h-11"
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="guests" className="flex items-center gap-2">
                <Users className="h-4 w-4 text-primary" />
                Guests
              </Label>
              <Input
                id="guests"
                type="number"
                name="guests"
                min="1"
                value={filters.guests || ''}
                onChange={handleChange}
                placeholder="Any"
                className="h-11"
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="bedrooms" className="flex items-center gap-2">
                <Bed className="h-4 w-4 text-primary" />
                Bedrooms
              </Label>
              <Input
                id="bedrooms"
                type="number"
                name="bedrooms"
                min="0"
                value={filters.bedrooms || ''}
                onChange={handleChange}
                placeholder="Any"
                className="h-11"
              />
            </div>
          </div>

          {/* Price Range */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div className="space-y-2">
              <Label htmlFor="min_price" className="flex items-center gap-2">
                <DollarSign className="h-4 w-4 text-primary" />
                Min Price ($/night)
              </Label>
              <Input
                id="min_price"
                type="number"
                name="min_price"
                min="0"
                value={filters.min_price || ''}
                onChange={handleChange}
                placeholder="No min"
                className="h-11"
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="max_price" className="flex items-center gap-2">
                <DollarSign className="h-4 w-4 text-primary" />
                Max Price ($/night)
              </Label>
              <Input
                id="max_price"
                type="number"
                name="max_price"
                min="0"
                value={filters.max_price || ''}
                onChange={handleChange}
                placeholder="No max"
                className="h-11"
              />
            </div>

            <div className="flex items-end">
              <Button
                type="submit"
                disabled={loading}
                size="lg"
                className="w-full h-11 shadow-md hover:shadow-lg transition-all"
              >
                {loading ? (
                  <>
                    <Loader2 className="h-5 w-5 mr-2 animate-spin" />
                    Searching...
                  </>
                ) : (
                  <>
                    <Search className="h-5 w-5 mr-2" />
                    Search Properties
                  </>
                )}
              </Button>
            </div>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}
