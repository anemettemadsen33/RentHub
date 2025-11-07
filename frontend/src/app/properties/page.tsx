'use client';

import { useState, useEffect } from 'react';
import { propertiesApi, Property, PropertyFilters } from '@/lib/api/properties';
import SearchBar from '@/components/properties/SearchBar';
import PropertyCard from '@/components/properties/PropertyCard';
import { Header } from '@/components/layout/Header';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Skeleton } from '@/components/ui/skeleton';
import { Badge } from '@/components/ui/badge';
import { AlertCircle, Search as SearchIcon } from 'lucide-react';

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

      if (response.data.success && response.data.data) {
        setProperties(response.data.data.data);
        setPagination({
          current_page: response.data.data.current_page,
          last_page: response.data.data.last_page,
          per_page: response.data.data.per_page,
          total: response.data.data.total,
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

  const handleSortChange = (value: string) => {
    const newFilters = {
      ...filters,
      sort_by: value as any,
      sort_order: (value === filters.sort_by && filters.sort_order === 'desc' ? 'asc' : 'desc') as 'asc' | 'desc',
    };
    setFilters(newFilters);
  };

  const handlePageChange = (page: number) => {
    setFilters({ ...filters, page });
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <div className="min-h-screen bg-background">
      <Header />
      
      {/* Hero Section */}
      <div className="relative bg-gradient-to-br from-primary via-blue-600 to-violet-600 text-primary-foreground py-20 border-b overflow-hidden">
        <div className="absolute inset-0 bg-grid-white/[0.05] bg-[size:20px_20px]" />
        <div className="absolute inset-0 bg-gradient-to-b from-transparent to-background/20" />
        <div className="relative container mx-auto px-4">
          <div className="max-w-3xl">
            <Badge variant="secondary" className="mb-4 bg-white/20 text-white border-white/30">
              <SearchIcon className="h-3 w-3 mr-1" />
              {pagination.total} Available Properties
            </Badge>
            <h1 className="text-4xl md:text-6xl font-bold mb-4">Find Your Perfect Stay</h1>
            <p className="text-xl text-primary-foreground/90">Discover amazing properties for your next adventure</p>
          </div>
        </div>
      </div>

      <div className="container mx-auto px-4 -mt-8 pb-16">
        {/* Search Bar */}
        <SearchBar onSearch={handleSearch} loading={loading} />

        {/* Sort & Results Count */}
        <div className="flex flex-wrap items-center justify-between mb-6 mt-8 gap-4">
          <div className="flex items-center gap-3">
            <h2 className="text-2xl font-bold">All Properties</h2>
            <Badge variant="secondary" className="text-base px-3 py-1">
              {pagination.total} found
            </Badge>
          </div>

          <div className="flex items-center gap-3">
            <span className="text-sm font-medium text-muted-foreground">Sort by:</span>
            <Select value={filters.sort_by} onValueChange={handleSortChange}>
              <SelectTrigger className="w-[180px]">
                <SelectValue placeholder="Sort by..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="created_at">Newest First</SelectItem>
                <SelectItem value="price">Price</SelectItem>
                <SelectItem value="rating">Rating</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        {/* Error Message */}
        {error && (
          <Alert variant="destructive" className="mb-6">
            <AlertCircle className="h-4 w-4" />
            <AlertDescription>{error}</AlertDescription>
          </Alert>
        )}

        {/* Loading State */}
        {loading && (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            {[...Array(6)].map((_, i) => (
              <div key={i} className="space-y-4">
                <Skeleton className="h-56 w-full rounded-xl" />
                <div className="space-y-2">
                  <Skeleton className="h-4 w-3/4" />
                  <Skeleton className="h-4 w-1/2" />
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Properties Grid */}
        {!loading && properties.length === 0 ? (
          <div className="bg-card border-2 border-dashed rounded-xl shadow-sm p-16 text-center">
            <SearchIcon className="mx-auto h-16 w-16 text-muted-foreground mb-4" />
            <h3 className="text-2xl font-semibold mb-2">No properties found</h3>
            <p className="text-muted-foreground text-lg mb-6">Try adjusting your search filters to find more results.</p>
            <Button onClick={() => handleSearch({})}>Clear Filters</Button>
          </div>
        ) : !loading && (
          <>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
              {properties.map((property) => (
                <PropertyCard key={property.id} property={property} />
              ))}
            </div>

            {/* Pagination */}
            {pagination.last_page > 1 && (
              <div className="flex items-center justify-center gap-2">
                <Button
                  onClick={() => handlePageChange(pagination.current_page - 1)}
                  disabled={pagination.current_page === 1}
                  variant="outline"
                  size="lg"
                >
                  Previous
                </Button>

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
                      <Button
                        key={page}
                        onClick={() => handlePageChange(page)}
                        variant={pagination.current_page === page ? "default" : "outline"}
                        size="lg"
                      >
                        {page}
                      </Button>
                    );
                  } else if (
                    page === 3 && pagination.current_page > 4 ||
                    page === pagination.last_page - 2 && pagination.current_page < pagination.last_page - 3
                  ) {
                    return <span key={page} className="px-2 text-muted-foreground">...</span>;
                  }
                  return null;
                })}

                <Button
                  onClick={() => handlePageChange(pagination.current_page + 1)}
                  disabled={pagination.current_page === pagination.last_page}
                  variant="outline"
                  size="lg"
                >
                  Next
                </Button>
              </div>
            )}
          </>
        )}
      </div>
    </div>
  );
}
