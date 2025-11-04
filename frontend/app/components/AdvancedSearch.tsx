'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import DatePicker from 'react-datepicker';
import Select from 'react-select';
import 'react-datepicker/dist/react-datepicker.css';

interface Filters {
  location: string;
  checkIn: Date | null;
  checkOut: Date | null;
  guests: number;
  propertyType: string;
  minPrice: string;
  maxPrice: string;
  bedrooms: string;
  bathrooms: string;
  amenities: string[];
}

export default function AdvancedSearch() {
  const router = useRouter();
  const [filters, setFilters] = useState<Filters>({
    location: '',
    checkIn: null,
    checkOut: null,
    guests: 1,
    propertyType: '',
    minPrice: '',
    maxPrice: '',
    bedrooms: '',
    bathrooms: '',
    amenities: [],
  });

  const propertyTypes = [
    { value: 'apartment', label: 'Apartment' },
    { value: 'house', label: 'House' },
    { value: 'villa', label: 'Villa' },
    { value: 'condo', label: 'Condo' },
  ];

  const handleSearch = () => {
    const params = new URLSearchParams();
    Object.entries(filters).forEach(([key, value]) => {
      if (value) {
        if (Array.isArray(value)) {
          params.append(key, JSON.stringify(value));
        } else if (value instanceof Date) {
          params.append(key, value.toISOString());
        } else {
          params.append(key, value.toString());
        }
      }
    });
    router.push(`/properties?${params.toString()}`);
  };

  return (
    <div className="bg-white rounded-lg shadow-lg p-6">
      <h2 className="text-2xl font-bold mb-6">Find Your Perfect Stay</h2>
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        {/* Location */}
        <input
          type="text"
          placeholder="Where to?"
          className="border rounded-lg px-4 py-2"
          value={filters.location}
          onChange={(e) => setFilters({...filters, location: e.target.value})}
        />

        {/* Check In */}
        <DatePicker
          selected={filters.checkIn}
          onChange={(date) => setFilters({...filters, checkIn: date})}
          placeholderText="Check In"
          className="border rounded-lg px-4 py-2 w-full"
          minDate={new Date()}
        />

        {/* Check Out */}
        <DatePicker
          selected={filters.checkOut}
          onChange={(date) => setFilters({...filters, checkOut: date})}
          placeholderText="Check Out"
          className="border rounded-lg px-4 py-2 w-full"
          minDate={filters.checkIn || new Date()}
        />

        {/* Guests */}
        <input
          type="number"
          min="1"
          placeholder="Guests"
          className="border rounded-lg px-4 py-2"
          value={filters.guests}
          onChange={(e) => setFilters({...filters, guests: parseInt(e.target.value) || 1})}
        />

        {/* Property Type */}
        <Select
          options={propertyTypes}
          placeholder="Property Type"
          onChange={(option) => setFilters({...filters, propertyType: option?.value || ''})}
        />

        {/* Price Range */}
        <input
          type="number"
          placeholder="Min Price"
          className="border rounded-lg px-4 py-2"
          value={filters.minPrice}
          onChange={(e) => setFilters({...filters, minPrice: e.target.value})}
        />
        <input
          type="number"
          placeholder="Max Price"
          className="border rounded-lg px-4 py-2"
          value={filters.maxPrice}
          onChange={(e) => setFilters({...filters, maxPrice: e.target.value})}
        />

        {/* Search Button */}
        <button
          onClick={handleSearch}
          className="bg-blue-600 text-white rounded-lg px-6 py-2 hover:bg-blue-700 transition-colors lg:col-span-4"
        >
          Search Properties
        </button>
      </div>
    </div>
  );
}
