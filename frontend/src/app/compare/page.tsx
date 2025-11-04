'use client';

import React, { useEffect, useState, Suspense } from 'react';
import { useSearchParams, useRouter } from 'next/navigation';
import { api } from '@/services/api';
import Image from 'next/image';
import Link from 'next/link';
import { toast } from 'react-hot-toast';

interface Property {
  id: number;
  title: string;
  description: string;
  type: string;
  furnishing_status: string;
  price_per_night: number;
  price_per_week: number;
  price_per_month: number;
  cleaning_fee: number;
  security_deposit: number;
  bedrooms: number;
  bathrooms: number;
  guests: number;
  area_sqm: number;
  square_footage: number;
  built_year: number;
  floor_number: number;
  street_address: string;
  city: string;
  state: string;
  country: string;
  latitude: number;
  longitude: number;
  amenities: Array<{ id: number; name: string; icon: string }>;
  parking_available: boolean;
  parking_spaces: number;
  min_nights: number;
  max_nights: number;
  cancellation_policy: string;
  rules: string;
  images: string[];
  average_rating: number;
  review_count: number;
  rating_breakdown: {
    cleanliness: number;
    accuracy: number;
    communication: number;
    location: number;
    checkin: number;
    value: number;
  };
  owner: {
    id: number;
    name: string;
    avatar: string;
    joined_at: string;
  };
}

interface ComparisonMatrix {
  feature: string;
  type: string;
  values: any[];
}

function CompareContent() {
  const searchParams = useSearchParams();
  const router = useRouter();
  const [properties, setProperties] = useState<Property[]>([]);
  const [matrix, setMatrix] = useState<ComparisonMatrix[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAllAmenities, setShowAllAmenities] = useState(false);

  useEffect(() => {
    const ids = searchParams.get('ids');
    if (!ids) {
      toast.error('No properties selected for comparison');
      router.push('/properties');
      return;
    }

    const propertyIds = ids.split(',').map(id => parseInt(id)).filter(id => !isNaN(id));
    
    if (propertyIds.length < 2) {
      toast.error('At least 2 properties are required for comparison');
      router.push('/properties');
      return;
    }

    if (propertyIds.length > 4) {
      toast.error('Maximum 4 properties can be compared');
      return;
    }

    fetchComparison(propertyIds);
  }, [searchParams]);

  const fetchComparison = async (propertyIds: number[]) => {
    try {
      setLoading(true);
      const response = await api.post('/property-comparison/compare', {
        property_ids: propertyIds,
      });

      setProperties(response.data.properties || []);
      setMatrix(response.data.comparison_matrix || []);
    } catch (error: any) {
      console.error('Failed to fetch comparison:', error);
      toast.error('Failed to load comparison data');
    } finally {
      setLoading(false);
    }
  };

  const removeProperty = (propertyId: number) => {
    const remainingIds = properties
      .filter(p => p.id !== propertyId)
      .map(p => p.id)
      .join(',');
    
    if (remainingIds) {
      router.push(`/compare?ids=${remainingIds}`);
    } else {
      router.push('/properties');
    }
  };

  const formatValue = (value: any, type: string) => {
    if (value === null || value === undefined) return '-';
    
    switch (type) {
      case 'currency':
        return `€${value.toLocaleString()}`;
      case 'number':
        return value.toLocaleString();
      case 'boolean':
        return value ? '✓' : '✗';
      case 'rating':
        return `${value} ⭐`;
      default:
        return value;
    }
  };

  const getBestValue = (values: any[], type: string): number[] => {
    if (type === 'currency') {
      const min = Math.min(...values.filter(v => v !== null));
      return values.map((v, i) => v === min ? i : -1).filter(i => i !== -1);
    }
    if (type === 'number' || type === 'rating') {
      const max = Math.max(...values.filter(v => v !== null));
      return values.map((v, i) => v === max ? i : -1).filter(i => i !== -1);
    }
    if (type === 'boolean') {
      return values.map((v, i) => v === true ? i : -1).filter(i => i !== -1);
    }
    return [];
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading comparison...</p>
        </div>
      </div>
    );
  }

  if (properties.length === 0) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h2 className="text-2xl font-bold text-gray-900 mb-4">No properties to compare</h2>
          <Link href="/properties" className="text-blue-600 hover:underline">
            Browse properties
          </Link>
        </div>
      </div>
    );
  }

  const allAmenities = Array.from(
    new Set(properties.flatMap(p => p.amenities.map(a => a.name)))
  ).sort();

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="container mx-auto px-4">
        {/* Header */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">Property Comparison</h1>
              <p className="text-gray-600 mt-2">
                Comparing {properties.length} properties side-by-side
              </p>
            </div>
            <Link
              href="/properties"
              className="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium"
            >
              ← Back to Search
            </Link>
          </div>
        </div>

        {/* Property Cards */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          {properties.map((property) => (
            <div key={property.id} className="bg-white rounded-lg shadow-md overflow-hidden">
              <div className="relative h-48">
                {property.images?.[0] ? (
                  <Image
                    src={property.images[0]}
                    alt={property.title}
                    fill
                    className="object-cover"
                  />
                ) : (
                  <div className="w-full h-full bg-gray-200 flex items-center justify-center">
                    <svg className="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                  </div>
                )}
                <button
                  onClick={() => removeProperty(property.id)}
                  className="absolute top-2 right-2 bg-white rounded-full p-2 shadow-md hover:bg-red-50 transition-colors"
                  title="Remove from comparison"
                >
                  <svg className="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              
              <div className="p-4">
                <h3 className="font-bold text-lg text-gray-900 mb-2 line-clamp-2">
                  {property.title}
                </h3>
                <p className="text-sm text-gray-600 mb-2">
                  {property.city}, {property.country}
                </p>
                <div className="flex items-center gap-2 mb-3">
                  <span className="font-bold text-blue-600">
                    €{property.price_per_night}/night
                  </span>
                  {property.average_rating > 0 && (
                    <span className="flex items-center gap-1 text-sm">
                      ⭐ {property.average_rating} ({property.review_count})
                    </span>
                  )}
                </div>
                <Link
                  href={`/properties/${property.id}`}
                  className="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                  View Details
                </Link>
              </div>
            </div>
          ))}
        </div>

        {/* Comparison Matrix */}
        <div className="bg-white rounded-lg shadow-md overflow-hidden mb-8">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead className="bg-gray-50 border-b-2 border-gray-200">
                <tr>
                  <th className="px-6 py-4 text-left text-sm font-semibold text-gray-900 sticky left-0 bg-gray-50 z-10">
                    Feature
                  </th>
                  {properties.map((property) => (
                    <th key={property.id} className="px-6 py-4 text-center text-sm font-semibold text-gray-900 min-w-[200px]">
                      {property.title}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-200">
                {matrix.map((row, idx) => {
                  const bestIndices = getBestValue(row.values, row.type);
                  return (
                    <tr key={idx} className={idx % 2 === 0 ? 'bg-white' : 'bg-gray-50'}>
                      <td className="px-6 py-4 text-sm font-medium text-gray-900 sticky left-0 bg-inherit z-10">
                        {row.feature}
                      </td>
                      {row.values.map((value, i) => (
                        <td
                          key={i}
                          className={`
                            px-6 py-4 text-center text-sm
                            ${bestIndices.includes(i) ? 'font-bold text-green-600 bg-green-50' : 'text-gray-700'}
                          `}
                        >
                          {formatValue(value, row.type)}
                        </td>
                      ))}
                    </tr>
                  );
                })}

                {/* Amenities Section */}
                <tr className="bg-blue-50">
                  <td colSpan={properties.length + 1} className="px-6 py-3">
                    <div className="flex items-center justify-between">
                      <h3 className="text-lg font-semibold text-gray-900">Amenities</h3>
                      {allAmenities.length > 10 && (
                        <button
                          onClick={() => setShowAllAmenities(!showAllAmenities)}
                          className="text-blue-600 hover:text-blue-700 text-sm font-medium"
                        >
                          {showAllAmenities ? 'Show Less' : 'Show All'}
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
                {(showAllAmenities ? allAmenities : allAmenities.slice(0, 10)).map((amenity, idx) => (
                  <tr key={amenity} className={idx % 2 === 0 ? 'bg-white' : 'bg-gray-50'}>
                    <td className="px-6 py-4 text-sm text-gray-900 sticky left-0 bg-inherit z-10">
                      {amenity}
                    </td>
                    {properties.map((property) => {
                      const hasAmenity = property.amenities.some(a => a.name === amenity);
                      return (
                        <td key={property.id} className="px-6 py-4 text-center">
                          <span className={hasAmenity ? 'text-green-600 text-xl' : 'text-gray-300 text-xl'}>
                            {hasAmenity ? '✓' : '✗'}
                          </span>
                        </td>
                      );
                    })}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>

        {/* Rating Breakdown */}
        <div className="bg-white rounded-lg shadow-md p-6">
          <h2 className="text-2xl font-bold text-gray-900 mb-6">Rating Breakdown</h2>
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {properties.map((property) => (
              <div key={property.id} className="border rounded-lg p-4">
                <h3 className="font-semibold text-gray-900 mb-4">{property.title}</h3>
                <div className="space-y-2">
                  {Object.entries(property.rating_breakdown).map(([key, value]) => (
                    <div key={key} className="flex items-center justify-between">
                      <span className="text-sm text-gray-600 capitalize">{key}</span>
                      <span className="font-medium">{value ? value.toFixed(1) : 'N/A'} ⭐</span>
                    </div>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
}

export default function ComparePage() {
  return (
    <Suspense fallback={
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600"></div>
      </div>
    }>
      <CompareContent />
    </Suspense>
  );
}
