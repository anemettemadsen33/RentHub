'use client';

import React, { useState } from 'react';
import SimpleMapSearch from '@/components/map/SimpleMapSearch';
import { useRouter } from 'next/navigation';

export default function MapSearchPage() {
  const router = useRouter();
  const [filters, setFilters] = useState({
    type: '',
    min_price: undefined as number | undefined,
    max_price: undefined as number | undefined,
    bedrooms: undefined as number | undefined,
    bathrooms: undefined as number | undefined,
    guests: undefined as number | undefined,
  });

  const [showFilters, setShowFilters] = useState(false);

  const handlePropertyClick = (propertyId: number) => {
    router.push(`/properties/${propertyId}`);
  };

  const handleFilterChange = (key: string, value: any) => {
    setFilters((prev) => ({
      ...prev,
      [key]: value || undefined,
    }));
  };

  const clearFilters = () => {
    setFilters({
      type: '',
      min_price: undefined,
      max_price: undefined,
      bedrooms: undefined,
      bathrooms: undefined,
      guests: undefined,
    });
  };

  return (
    <div className="flex flex-col h-screen">
      {/* Header */}
      <header className="bg-white shadow-sm z-10">
        <div className="container mx-auto px-4 py-4">
          <div className="flex items-center justify-between">
            <h1 className="text-2xl font-bold text-gray-900">
              Map Search
            </h1>
            
            <button
              onClick={() => setShowFilters(!showFilters)}
              className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
            >
              <svg
                className="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"
                />
              </svg>
              Filters
            </button>
          </div>
        </div>
      </header>

      {/* Filters Panel */}
      {showFilters && (
        <div className="bg-white border-b shadow-sm z-10">
          <div className="container mx-auto px-4 py-4">
            <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
              {/* Property Type */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Type
                </label>
                <select
                  value={filters.type}
                  onChange={(e) => handleFilterChange('type', e.target.value)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">All Types</option>
                  <option value="apartment">Apartment</option>
                  <option value="house">House</option>
                  <option value="villa">Villa</option>
                  <option value="studio">Studio</option>
                </select>
              </div>

              {/* Min Price */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Min Price (€)
                </label>
                <input
                  type="number"
                  value={filters.min_price || ''}
                  onChange={(e) => handleFilterChange('min_price', e.target.value ? Number(e.target.value) : undefined)}
                  placeholder="0"
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              {/* Max Price */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Max Price (€)
                </label>
                <input
                  type="number"
                  value={filters.max_price || ''}
                  onChange={(e) => handleFilterChange('max_price', e.target.value ? Number(e.target.value) : undefined)}
                  placeholder="1000"
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>

              {/* Bedrooms */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Bedrooms
                </label>
                <select
                  value={filters.bedrooms || ''}
                  onChange={(e) => handleFilterChange('bedrooms', e.target.value ? Number(e.target.value) : undefined)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Any</option>
                  <option value="1">1+</option>
                  <option value="2">2+</option>
                  <option value="3">3+</option>
                  <option value="4">4+</option>
                </select>
              </div>

              {/* Bathrooms */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Bathrooms
                </label>
                <select
                  value={filters.bathrooms || ''}
                  onChange={(e) => handleFilterChange('bathrooms', e.target.value ? Number(e.target.value) : undefined)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Any</option>
                  <option value="1">1+</option>
                  <option value="2">2+</option>
                  <option value="3">3+</option>
                </select>
              </div>

              {/* Guests */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">
                  Guests
                </label>
                <select
                  value={filters.guests || ''}
                  onChange={(e) => handleFilterChange('guests', e.target.value ? Number(e.target.value) : undefined)}
                  className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Any</option>
                  <option value="1">1+</option>
                  <option value="2">2+</option>
                  <option value="4">4+</option>
                  <option value="6">6+</option>
                </select>
              </div>
            </div>

            <div className="mt-4 flex justify-end">
              <button
                onClick={clearFilters}
                className="px-4 py-2 text-gray-700 hover:text-gray-900 transition-colors"
              >
                Clear Filters
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Map */}
      <div className="flex-1">
        <SimpleMapSearch
          filters={filters}
          onPropertyClick={handlePropertyClick}
          className="h-full w-full"
        />
      </div>
    </div>
  );
}
