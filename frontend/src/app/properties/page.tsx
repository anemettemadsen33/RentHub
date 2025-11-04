'use client';

import { useState, useEffect } from 'react';
import { propertiesApi, Property, PropertyFilters } from '@/lib/api/properties';
import SearchBar from '@/components/properties/SearchBar';
import PropertyCard from '@/components/properties/PropertyCard';

export default function PropertiesPage() {
  const [properties, setProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });
  const [filters, setFilters] = useState<PropertyFilters>({
    sort_by: 'created_at',
    sort_order: 'desc',
    per_page: 15,
    page: 1,
  });

  useEffect(() => {
    fetchProperties();
  }, [filters.page, filters.sort_by, filters.sort_order]);

  const fetchProperties = async (searchFilters?: PropertyFilters) => {
    setLoading(true);
    setError('');

    try {
      const finalFilters = searchFilters || filters;
      const response = await propertiesApi.getAll(finalFilters);

      if (response.success && response.data) {
        setProperties(response.data.data);
        setPagination({
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          per_page: response.data.per_page,
          total: response.data.total,
        });
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to load properties');
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (searchFilters: PropertyFilters) => {
    const newFilters = {
      ...filters,
      ...searchFilters,
      page: 1,
    };
    setFilters(newFilters);
    fetchProperties(newFilters);
  };

  const handleSortChange = (sortBy: string) => {
    const newFilters = {
      ...filters,
      sort_by: sortBy as any,
      sort_order: sortBy === filters.sort_by && filters.sort_order === 'desc' ? 'asc' : 'desc',
    };
    setFilters(newFilters);
  };

  const handlePageChange = (page: number) => {
    setFilters({ ...filters, page });
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Hero Section */}
      <div className="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h1 className="text-4xl md:text-5xl font-bold mb-4">Find Your Perfect Stay</h1>
          <p className="text-xl text-blue-100">Discover amazing properties for your next adventure</p>
        </div>
      </div>

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
        {/* Search Bar */}
        <SearchBar onSearch={handleSearch} loading={loading} />

        {/* Sort & Results Count */}
        <div className="flex flex-wrap items-center justify-between mb-6">
          <div className="flex items-center gap-2 mb-4 md:mb-0">
            <span className="text-gray-600 font-medium">
              {pagination.total} properties found
            </span>
          </div>

          <div className="flex items-center gap-2">
            <span className="text-gray-600 text-sm">Sort by:</span>
            <select
              value={filters.sort_by}
              onChange={(e) => handleSortChange(e.target.value)}
              className="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
            >
              <option value="created_at">Newest</option>
              <option value="price">Price</option>
              <option value="rating">Rating</option>
            </select>
          </div>
        </div>

        {/* Error Message */}
        {error && (
          <div className="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
            {error}
          </div>
        )}

        {/* Loading State */}
        {loading && (
          <div className="flex items-center justify-center py-20">
            <div className="text-center">
              <div className="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mx-auto mb-4"></div>
              <p className="text-gray-600 text-lg">Loading properties...</p>
            </div>
          </div>
        )}

        {/* Properties Grid */}
        {!loading && properties.length === 0 ? (
          <div className="bg-white rounded-lg shadow p-12 text-center">
            <svg className="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <h3 className="text-xl font-medium text-gray-900 mb-2">No properties found</h3>
            <p className="text-gray-600">Try adjusting your search filters to find more results.</p>
          </div>
        ) : (
          <>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
              {properties.map((property) => (
                <PropertyCard key={property.id} property={property} />
              ))}
            </div>

            {/* Pagination */}
            {pagination.last_page > 1 && (
              <div className="flex items-center justify-center gap-2 pb-12">
                <button
                  onClick={() => handlePageChange(pagination.current_page - 1)}
                  disabled={pagination.current_page === 1}
                  className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Previous
                </button>

                {[...Array(pagination.last_page)].map((_, idx) => {
                  const page = idx + 1;
                  if (
                    page === 1 ||
                    page === 2 ||
                    page === pagination.last_page ||
                    page === pagination.last_page - 1 ||
                    (page >= pagination.current_page - 1 && page <= pagination.current_page + 1)
                  ) {
                    return (
                      <button
                        key={page}
                        onClick={() => handlePageChange(page)}
                        className={`px-4 py-2 rounded-lg ${
                          pagination.current_page === page
                            ? 'bg-blue-600 text-white'
                            : 'border border-gray-300 hover:bg-gray-50'
                        }`}
                      >
                        {page}
                      </button>
                    );
                  } else if (
                    page === 3 && pagination.current_page > 4 ||
                    page === pagination.last_page - 2 && pagination.current_page < pagination.last_page - 3
                  ) {
                    return <span key={page} className="px-2">...</span>;
                  }
                  return null;
                })}

                <button
                  onClick={() => handlePageChange(pagination.current_page + 1)}
                  disabled={pagination.current_page === pagination.last_page}
                  className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Next
                </button>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}
