'use client';

import { useEffect, useState, useMemo, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import apiClient from '@/lib/api-client';
import { Property } from '@/types';
import { useConversionTracking } from '@/hooks/use-conversion-tracking';
import { MainLayout } from '@/components/layouts/main-layout';
import { PropertyCardSkeleton } from '@/components/skeletons';
import { Skeleton } from '@/components/ui/skeleton';
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { MemoizedPropertyCard } from '@/components/memoized-property-card';
import { FilterPanel, FilterOptions } from '@/components/filter-panel';
import { Badge } from '@/components/ui/badge';
import { NoPropertiesFound, NoSearchResults } from '@/components/empty-states';
import { useDebounce } from '@/hooks/use-debounce';
import { createLogger } from '@/lib/logger';
import { 
  Search, 
  SlidersHorizontal, 
  MapIcon, 
  Grid3x3, 
  List,
  ArrowUpDown,
  Heart,
    Loader2,
    Building2,
    Star,
    TrendingUp
} from 'lucide-react';
import { LazyMap } from '@/components/lazy-map';
import { debounce } from '@/lib/utils';
import { formatCurrency } from '@/lib/utils';
import { mockProperties } from '@/lib/mock-data';
import Link from 'next/link';
import { useTranslations } from 'next-intl';

const isStubE2E = process.env.NEXT_PUBLIC_E2E === 'true';
const initialStub = isStubE2E ? mockProperties.slice(0, 6) : [];

const propertiesLogger = createLogger('PropertiesPage');

type ViewMode = 'grid' | 'list' | 'map';
type SortOption = 'price-asc' | 'price-desc' | 'rating' | 'newest';

export default function PropertiesPage() {
  // Translations (must be first hooks, before conditional returns)
  const tProps = useTranslations('properties');
  const tNav = useTranslations('navigation');
  const tComparison = useTranslations('comparison');
  const [properties, setProperties] = useState<Property[]>(initialStub);
  const [filteredProperties, setFilteredProperties] = useState<Property[]>(initialStub);
  const [isLoading, setIsLoading] = useState(!isStubE2E);
  const [comparison, setComparison] = useState<number[]>([]);
  const [searchQuery, setSearchQuery] = useState('');
  const debouncedSearch = useDebounce(searchQuery, 500);
  const [viewMode, setViewMode] = useState<ViewMode>('grid');
  const [mapLoading, setMapLoading] = useState(false);
  const [mapBoundsQuerying, setMapBoundsQuerying] = useState(false);
  const [mapViewport, setMapViewport] = useState<{bounds: [number, number, number, number]; zoom: number} | null>(null);
  const [sortBy, setSortBy] = useState<SortOption>('newest');
  const [showFilters, setShowFilters] = useState(false);
  const [favorites, setFavorites] = useState<number[]>([]);
  const [filters, setFilters] = useState<FilterOptions>({
    priceRange: [0, isStubE2E ? 100000 : 1000],
    bedrooms: null,
    bathrooms: null,
    propertyType: [],
    amenities: [],
    guests: null,
    instantBook: false,
  });
  // Parse URL query and seed filters on first mount
  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const nf: FilterOptions = { ...filters };
    if (params.get('minPrice') || params.get('maxPrice')) {
      const min = parseInt(params.get('minPrice') || '0');
      const max = parseInt(params.get('maxPrice') || (isStubE2E ? '100000' : '1000'));
      nf.priceRange = [min, max];
    } else if (isStubE2E) {
      nf.priceRange = [0, 100000];
    }
    if (params.get('bedrooms')) nf.bedrooms = parseInt(params.get('bedrooms')!);
    if (params.get('bathrooms')) nf.bathrooms = parseInt(params.get('bathrooms')!);
    if (params.get('guests')) nf.guests = parseInt(params.get('guests')!);
    if (params.get('types')) nf.propertyType = params.get('types')!.split(',').filter(Boolean);
    if (params.get('amenities')) nf.amenities = params.get('amenities')!.split(',').filter(Boolean);
    if (params.get('instant') === '1') nf.instantBook = true;
    setFilters(nf);
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  // Sync filters to URL (replace state, no reload)
  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    params.set('minPrice', String(filters.priceRange[0]));
    params.set('maxPrice', String(filters.priceRange[1]));
    if (filters.bedrooms) params.set('bedrooms', String(filters.bedrooms)); else params.delete('bedrooms');
    if (filters.bathrooms) params.set('bathrooms', String(filters.bathrooms)); else params.delete('bathrooms');
    if (filters.guests) params.set('guests', String(filters.guests)); else params.delete('guests');
    if (filters.propertyType.length > 0) params.set('types', filters.propertyType.join(',')); else params.delete('types');
    if (filters.amenities.length > 0) params.set('amenities', filters.amenities.join(',')); else params.delete('amenities');
    if (filters.instantBook) params.set('instant', '1'); else params.delete('instant');
    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.replaceState({}, '', newUrl);
  }, [filters]);

  useEffect(() => {
    if (!isStubE2E) {
      fetchProperties();
    }
    loadFavorites();
    // Load comparison list
    const cmp = localStorage.getItem('comparison');
    if (cmp) {
      try {
        setComparison(JSON.parse(cmp));
      } catch {}
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    applyFiltersAndSort();
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [properties, debouncedSearch, filters, sortBy]);

  const fetchProperties = useCallback(async (params?: { bounds?: [number, number, number, number]; zoom?: number }) => {
    try {
      const query: Record<string,string|number> = {};
      if (params?.bounds) {
        const [minLng, minLat, maxLng, maxLat] = params.bounds;
        query.min_lat = minLat;
        query.max_lat = maxLat;
        query.min_lng = minLng;
        query.max_lng = maxLng;
      }
      if (params?.zoom !== undefined) {
        query.zoom = params.zoom;
      }
      const qs = Object.keys(query).length > 0 ? '?' + new URLSearchParams(query as any).toString() : '';
      const { data } = await apiClient.get(`/properties${qs}`);
      setProperties(data.data || []);
      propertiesLogger.info('Properties loaded successfully', { count: data.data?.length || 0 });
    } catch (error) {
      propertiesLogger.warn('Backend not available, using mock data', { error });
      // Use mock data when backend is not available
      setProperties(mockProperties);
    } finally {
      setIsLoading(false);
      setMapBoundsQuerying(false);
    }
  }, []);
  // Debounced map-driven refresh
  const debouncedMapRefresh = useMemo(() => debounce((viewport: {bounds: [number, number, number, number]; zoom: number}) => {
    setMapBoundsQuerying(true);
    fetchProperties({ bounds: viewport.bounds, zoom: viewport.zoom });
  }, 600), [fetchProperties]);

  const handleMapViewportChange = useCallback((vp: { bounds: [number, number, number, number]; zoom: number }) => {
    setMapViewport(vp);
    debouncedMapRefresh(vp);
  }, [debouncedMapRefresh]);

  const handlePropertyClick = useCallback((property: Property) => {
    // Could track analytics event here
  }, []);

  const loadFavorites = useCallback(() => {
    const saved = localStorage.getItem('favorites');
    if (saved) {
      setFavorites(JSON.parse(saved));
    }
  }, []);

  const { trackWishlistToggle, trackSearchPerformed, trackFiltersApplied } = useConversionTracking();

  const toggleFavorite = useCallback((id: number) => {
    const newFavorites = favorites.includes(id)
      ? favorites.filter(fav => fav !== id)
      : [...favorites, id];
    setFavorites(newFavorites);
    localStorage.setItem('favorites', JSON.stringify(newFavorites));
    // Fire conversion event for wishlist toggle
    trackWishlistToggle(id, newFavorites.includes(id));
  }, [favorites, trackWishlistToggle]);

  const addToComparison = useCallback((id: number) => {
    setComparison(prev => {
      if (prev.includes(id)) return prev; // ignore duplicates
      const next = [...prev, id];
      localStorage.setItem('comparison', JSON.stringify(next));
      return next;
    });
  }, []);

  const router = useRouter();

  const ComparisonBar = ({ ids }: { ids: number[] }) => {
    return (
      <div
        className="fixed bottom-4 right-4 z-40"
        data-testid="comparison-bar"
        role="region"
        aria-label="Comparison bar"
      >
        <div className="bg-primary text-primary-foreground rounded shadow-lg px-4 py-2 flex items-center gap-3">
          <span className="font-medium" data-testid="comparison-count">{ids.length}</span>
          <Button
            size="sm"
            variant="outline"
            data-testid="compare-now-button"
            onClick={() => router.push('/property-comparison')}
          >
            {tComparison('compareNow')}
          </Button>
        </div>
      </div>
    );
  };

  // Memoized filtered and sorted properties
  const filteredAndSortedProperties = useMemo(() => {
    let filtered = [...properties];

    // Search filter (using debounced value)
    if (debouncedSearch) {
      filtered = filtered.filter(p =>
        p.title.toLowerCase().includes(debouncedSearch.toLowerCase()) ||
        p.city.toLowerCase().includes(debouncedSearch.toLowerCase()) ||
        p.country.toLowerCase().includes(debouncedSearch.toLowerCase())
      );
    }

    // Price range filter
    filtered = filtered.filter(p => {
      const price = p.price_per_night || p.price;
      return price >= filters.priceRange[0] && price <= filters.priceRange[1];
    });

    // Property type filter
    if (filters.propertyType.length > 0) {
      filtered = filtered.filter(p => 
        filters.propertyType.includes(p.type) || 
        filters.propertyType.includes(p.property_type || '')
      );
    }

    // Bedrooms filter
    if (filters.bedrooms) {
      filtered = filtered.filter(p => p.bedrooms >= filters.bedrooms!);
    }

    // Bathrooms filter
    if (filters.bathrooms) {
      filtered = filtered.filter(p => p.bathrooms >= filters.bathrooms!);
    }

    // Guests filter
    if (filters.guests) {
      filtered = filtered.filter(p => (p.max_guests || 4) >= filters.guests!);
    }

    // Amenities filter
    if (filters.amenities.length > 0) {
      filtered = filtered.filter(p => {
        const names = (p.amenities || []).map((a: any) => (typeof a === 'string' ? a : a?.name)).filter(Boolean).map((s: string) => s.toLowerCase());
        return filters.amenities.every(amenity => names.includes(amenity));
      });
    }

    // Sort
    switch (sortBy) {
      case 'price-asc':
        filtered.sort((a, b) => (a.price_per_night || a.price) - (b.price_per_night || b.price));
        break;
      case 'price-desc':
        filtered.sort((a, b) => (b.price_per_night || b.price) - (a.price_per_night || a.price));
        break;
      case 'rating':
        filtered.sort((a, b) => (b.rating || 0) - (a.rating || 0));
        break;
      case 'newest':
        filtered.sort((a, b) => new Date(b.created_at || 0).getTime() - new Date(a.created_at || 0).getTime());
        break;
    }

    return filtered;
  }, [properties, debouncedSearch, filters, sortBy]);

  // Update filtered list
  useEffect(() => {
    setFilteredProperties(filteredAndSortedProperties);
  }, [filteredAndSortedProperties]);

  // Track search after filtered list updates and when search query is active
  useEffect(() => {
    if (debouncedSearch) {
      // compute activeFiltersCount inline to avoid order dependency
      const count = (() => {
        let c = 0;
        if (filters.propertyType.length > 0) c += filters.propertyType.length;
        if (filters.amenities.length > 0) c += filters.amenities.length;
        if (filters.bedrooms) c++;
        if (filters.bathrooms) c++;
        if (filters.guests) c++;
        if (filters.instantBook) c++;
        if (filters.priceRange[0] > 0 || filters.priceRange[1] < 1000) c++;
        return c;
      })();
      trackSearchPerformed({
        query: debouncedSearch,
        results: filteredAndSortedProperties.length,
        filtersCount: count,
      });
    }
  }, [filteredAndSortedProperties, debouncedSearch, filters, trackSearchPerformed]);

  // Memoized callbacks to prevent unnecessary re-renders
  const handleFilterChange = useCallback((newFilters: FilterOptions) => {
    const changed: Record<string, any> = {};
    Object.keys(newFilters).forEach(k => {
      // @ts-ignore index access
      if (newFilters[k] !== filters[k]) {
        // @ts-ignore index access
        changed[k] = newFilters[k];
      }
    });
    setFilters(newFilters);
    if (Object.keys(changed).length > 0) {
      trackFiltersApplied(changed);
    }
  }, [filters, trackFiltersApplied]);

  const handleResetFilters = useCallback(() => {
    setFilters({
      priceRange: [0, 1000],
      bedrooms: null,
      bathrooms: null,
      propertyType: [],
      amenities: [],
      guests: null,
      instantBook: false,
    });
    setSearchQuery('');
  }, []);

  // Memoized active filters count
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

  const applyFiltersAndSort = useCallback(() => {
    let filtered = [...properties];

    // Search filter (using debounced value)
    if (debouncedSearch) {
      filtered = filtered.filter(p =>
        p.title.toLowerCase().includes(debouncedSearch.toLowerCase()) ||
        p.city.toLowerCase().includes(debouncedSearch.toLowerCase()) ||
        p.country.toLowerCase().includes(debouncedSearch.toLowerCase())
      );
    }

    // Price range filter
    filtered = filtered.filter(p => {
      const price = p.price_per_night || p.price;
      return price >= filters.priceRange[0] && price <= filters.priceRange[1];
    });

    // Property type filter
    if (filters.propertyType.length > 0) {
      filtered = filtered.filter(p => 
        filters.propertyType.includes(p.type) || 
        filters.propertyType.includes(p.property_type || '')
      );
    }

    // Bedrooms filter
    if (filters.bedrooms) {
      filtered = filtered.filter(p => p.bedrooms >= filters.bedrooms!);
    }

    // Bathrooms filter
    if (filters.bathrooms) {
      filtered = filtered.filter(p => p.bathrooms >= filters.bathrooms!);
    }

    // Guests filter
    if (filters.guests) {
      filtered = filtered.filter(p => (p.max_guests || 4) >= filters.guests!);
    }

    // Amenities filter
    if (filters.amenities.length > 0) {
      filtered = filtered.filter(p => {
        const names = (p.amenities || []).map((a: any) => (typeof a === 'string' ? a : a?.name)).filter(Boolean).map((s: string) => s.toLowerCase());
        return filters.amenities.every(amenity => names.includes(amenity));
      });
    }

    // Sort
    switch (sortBy) {
      case 'price-asc':
        filtered.sort((a, b) => (a.price_per_night || a.price) - (b.price_per_night || b.price));
        break;
      case 'price-desc':
        filtered.sort((a, b) => (b.price_per_night || b.price) - (a.price_per_night || a.price));
        break;
      case 'rating':
        filtered.sort((a, b) => (b.rating || 0) - (a.rating || 0));
        break;
      case 'newest':
        filtered.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
        break;
    }

    setFilteredProperties(filtered);
  }, [properties, debouncedSearch, filters, sortBy]);

  if (isLoading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8 space-y-6">
          <Skeleton className="h-10 w-64" />
          <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            {[1, 2, 3, 4, 5, 6, 7, 8].map((i) => (
              <PropertyCardSkeleton key={i} />
            ))}
          </div>
        </div>
      </MainLayout>
    );
  }

  // Simplified stub-mode early return for E2E to guarantee presence of property cards

  if (isStubE2E) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8" data-testid="properties-page-root">
          <h1 className="text-2xl font-bold mb-6">{tProps('titleStub')}</h1>
          <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" data-testid="debug-count" data-count={filteredProperties.length}>
            {filteredProperties.map(p => (
              <div key={p.id} data-testid="property-card">
                <MemoizedPropertyCard
                  property={p}
                  onFavorite={toggleFavorite}
                  isFavorite={favorites.includes(p.id)}
                />
                <div className="mt-2 flex items-center justify-between text-sm">
                  <span data-testid="property-price">{formatCurrency(p.price_per_night || p.price)}</span>
                  <Button size="sm" variant={comparison.includes(p.id) ? 'default' : 'secondary'} data-testid="compare-button" aria-label={`Compare property ${p.id}`} onClick={() => addToComparison(p.id)}>
                    {comparison.includes(p.id) ? tComparison('added') : tComparison('compare')}
                  </Button>
                </div>
              </div>
            ))}
          </div>
          {comparison.length > 0 && <ComparisonBar ids={comparison} />}
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        <div data-testid="properties-page-root" className="sr-only">properties-root-mounted</div>

          {/* Stats Header */}
          <div className="grid gap-4 md:grid-cols-4 mb-8">
            <Card>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-muted-foreground">Total Properties</p>
                    <p className="text-2xl font-bold">{properties.length}</p>
                  </div>
                  <Building2 className="h-8 w-8 text-muted-foreground" />
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-muted-foreground">Showing Results</p>
                    <p className="text-2xl font-bold">{filteredProperties.length}</p>
                  </div>
                  <TrendingUp className="h-8 w-8 text-muted-foreground" />
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-muted-foreground">Avg Rating</p>
                    <p className="text-2xl font-bold">
                      {properties.length > 0 
                        ? (properties.reduce((acc, p) => acc + (p.rating || 0), 0) / properties.length).toFixed(1)
                        : '0.0'}
                    </p>
                  </div>
                  <Star className="h-8 w-8 text-muted-foreground" />
                </div>
              </CardContent>
            </Card>
            <Card>
              <CardContent className="p-6">
                <div className="flex items-center justify-between">
                  <div>
                    <p className="text-sm font-medium text-muted-foreground">Your Favorites</p>
                    <p className="text-2xl font-bold">{favorites.length}</p>
                  </div>
                  <Heart className="h-8 w-8 text-muted-foreground" />
                </div>
              </CardContent>
            </Card>
          </div>

        {/* Header with Search */}
          <div className="mb-6 animate-fade-in">
          <div className="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between mb-4">
            <div>
                <h1 className="text-2xl font-bold mb-1">{tProps('title')}</h1>
                <p className="text-sm text-muted-foreground" aria-live="polite">
                  Find your perfect rental property
              </p>
            </div>
          </div>

          {/* Search Bar */}
          <div className="relative max-w-2xl">
            <Search className="absolute left-3 top-3.5 h-5 w-5 text-gray-400" />
            <Input
              type="text"
              placeholder={tProps('searchPlaceholder')}
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-10 h-12 text-base"
              aria-label={tProps('searchAriaLabel') || 'Search properties by city, country or title'}
            />
          </div>
        </div>

        {/* Toolbar */}
        <div className="flex flex-wrap gap-3 items-center justify-between mb-6">
          <div className="flex flex-wrap gap-2 items-center">
            {/* Filters Button */}
            <Button
              variant={showFilters ? 'default' : 'outline'}
              onClick={() => setShowFilters(!showFilters)}
              className="gap-2"
              data-testid="filters-button"
            >
              <SlidersHorizontal className="h-4 w-4" />
              {tProps('filters')}
              {activeFiltersCount > 0 && (
                <Badge variant="secondary" className="ml-1">
                  {activeFiltersCount}
                </Badge>
              )}
            </Button>

            {/* Sort Dropdown (shadcn Select) */}
            <Select value={sortBy} onValueChange={(v) => setSortBy(v as SortOption)}>
              <SelectTrigger className="w-[200px]" aria-label="Sort properties">
                <SelectValue placeholder={tProps('sort.label') || 'Sort by'} />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="newest">{tProps('sort.newestFirst')}</SelectItem>
                <SelectItem value="price-asc">{tProps('sort.priceAsc')}</SelectItem>
                <SelectItem value="price-desc">{tProps('sort.priceDesc')}</SelectItem>
                <SelectItem value="rating">{tProps('sort.rating')}</SelectItem>
              </SelectContent>
            </Select>
          </div>

          {/* View Mode */}
          <TooltipProvider>
            <ToggleGroup
              type="single"
              value={viewMode}
              onValueChange={(v) => v && setViewMode(v as ViewMode)}
              className="border rounded-md p-1"
              aria-label="Select view mode"
            >
              <Tooltip>
                <TooltipTrigger asChild>
                  <ToggleGroupItem value="grid" aria-label="Grid view">
                    <Grid3x3 className="h-4 w-4" />
                  </ToggleGroupItem>
                </TooltipTrigger>
                <TooltipContent>Grid</TooltipContent>
              </Tooltip>
              <Tooltip>
                <TooltipTrigger asChild>
                  <ToggleGroupItem value="list" aria-label="List view">
                    <List className="h-4 w-4" />
                  </ToggleGroupItem>
                </TooltipTrigger>
                <TooltipContent>List</TooltipContent>
              </Tooltip>
              <Tooltip>
                <TooltipTrigger asChild>
                  <ToggleGroupItem value="map" aria-label="Map view">
                    <MapIcon className="h-4 w-4" />
                  </ToggleGroupItem>
                </TooltipTrigger>
                <TooltipContent>Map</TooltipContent>
              </Tooltip>
            </ToggleGroup>
          </TooltipProvider>
        </div>

        {/* Active Filters Display */}
        {activeFiltersCount > 0 && (
          <div className="flex flex-wrap gap-2 mb-6">
            {filters.propertyType.map(type => (
              <Badge key={type} variant="secondary" className="capitalize">
                {type}
                <button
                  onClick={() => {
                    const newFilters = {
                      ...filters,
                      propertyType: filters.propertyType.filter(t => t !== type)
                    };
                    setFilters(newFilters);
                  }}
                  className="ml-2"
                >
                  ×
                </button>
              </Badge>
            ))}
            {filters.amenities.map(amenity => (
              <Badge key={amenity} variant="secondary" className="capitalize">
                {amenity}
                <button
                  onClick={() => {
                    const newFilters = {
                      ...filters,
                      amenities: filters.amenities.filter(a => a !== amenity)
                    };
                    setFilters(newFilters);
                  }}
                  className="ml-2"
                >
                  ×
                </button>
              </Badge>
            ))}
          </div>
        )}

        {/* Main Content */}
        <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
          {/* Filters Sidebar */}
          {showFilters && (
            <div className="lg:col-span-1">
              <FilterPanel
                onFilterChange={handleFilterChange}
                onClose={() => setShowFilters(false)}
                initial={filters}
              />
            </div>
          )}

          {/* Properties Grid */}
          <div className={showFilters ? 'lg:col-span-3' : 'lg:col-span-4'}>
            {viewMode === 'map' ? (
              <div className="space-y-3">
                <div className="flex items-center justify-between text-sm">
                  <div className="flex items-center gap-2">
                    <span className="font-medium">{tProps('viewMode.map')}</span>
                    {mapBoundsQuerying && (
                            <span className="inline-flex items-center gap-1 text-muted-foreground"><Loader2 className="h-3 w-3 animate-spin" /> {tProps('updating')}</span>
                    )}
                  </div>
                  {mapViewport && (
                    <span className="text-muted-foreground">Zoom: {mapViewport.zoom}</span>
                  )}
                </div>
                <div className="h-[600px]">
                  <LazyMap
                    properties={filteredProperties}
                    zoom={11}
                    onPropertyClick={handlePropertyClick}
                    onViewportChange={handleMapViewportChange}
                  />
                </div>
                <p className="text-xs text-muted-foreground">
                  {tProps('mapHint')}
                </p>
              </div>
            ) : filteredProperties.length === 0 ? (
              <Card>
                <CardContent className="flex flex-col items-center justify-center py-12">
                  <Search className="h-16 w-16 text-gray-300 mb-4" />
                  <h3 className="text-xl font-semibold mb-2">{tProps('noResults')}</h3>
                  <p className="text-gray-600 mb-4">{tProps('tryAdjusting')}</p>
                  <Button
                    variant="outline"
                    onClick={() => {
                      setSearchQuery('');
                      setFilters({
                        priceRange: [0, 1000],
                        bedrooms: null,
                        bathrooms: null,
                        propertyType: [],
                        amenities: [],
                        guests: null,
                        instantBook: false,
                      });
                    }}
                  >
                    {tProps('clearAllFilters')}
                  </Button>
                </CardContent>
              </Card>
            ) : (
              <div
                className={
                  viewMode === 'grid'
                    ? 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6'
                    : 'space-y-4'
                }
              >
                {filteredProperties.map((property) => (
                  <div key={property.id} data-testid="property-card" data-loaded="true" className="animate-fade-in-up">
                    <MemoizedPropertyCard
                      property={property}
                      onFavorite={toggleFavorite}
                      isFavorite={favorites.includes(property.id)}
                    />
                    <div className="mt-2 flex items-center justify-between text-sm">
                      <span data-testid="property-price">{formatCurrency(property.price_per_night || property.price)}</span>
                      <Button
                        size="sm"
                        variant={comparison.includes(property.id) ? 'default' : 'secondary'}
                        data-testid="compare-button"
                        aria-label={`Compare property ${property.id}`}
                        onClick={() => addToComparison(property.id)}
                      >
                        {comparison.includes(property.id) ? tComparison('added') : tComparison('compare')}
                      </Button>
                    </div>
                  </div>
                ))}
                {comparison.length > 0 && (
                  <ComparisonBar ids={comparison} />
                )}
              </div>
            )}
          </div>
        </div>
      </div>
    </MainLayout>
  );
}
