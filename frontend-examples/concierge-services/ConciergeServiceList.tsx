'use client';

import React, { useState, useEffect } from 'react';
import ConciergeServiceCard from './ConciergeServiceCard';
import { Filter, Search } from 'lucide-react';

interface ConciergeService {
  id: number;
  name: string;
  description: string;
  service_type: string;
  base_price: number;
  price_unit: string;
  duration_minutes: number | null;
  max_guests: number | null;
  images: string[];
  is_available: boolean;
  service_provider?: {
    name: string;
    rating: number;
  };
}

interface ServiceType {
  value: string;
  label: string;
  icon: string;
  description: string;
}

export default function ConciergeServiceList() {
  const [services, setServices] = useState<ConciergeService[]>([]);
  const [serviceTypes, setServiceTypes] = useState<ServiceType[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedType, setSelectedType] = useState<string>('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [maxPrice, setMaxPrice] = useState<string>('');
  const [showFilters, setShowFilters] = useState(false);

  useEffect(() => {
    fetchServiceTypes();
    fetchServices();
  }, []);

  const fetchServiceTypes = async () => {
    try {
      const response = await fetch('/api/v1/concierge-services/types');
      const data = await response.json();
      if (data.success) {
        setServiceTypes(data.data);
      }
    } catch (error) {
      console.error('Error fetching service types:', error);
    }
  };

  const fetchServices = async () => {
    try {
      setLoading(true);
      const params = new URLSearchParams();
      
      if (selectedType && selectedType !== 'all') {
        params.append('service_type', selectedType);
      }
      if (maxPrice) {
        params.append('max_price', maxPrice);
      }

      const response = await fetch(`/api/v1/concierge-services?${params.toString()}`);
      const data = await response.json();
      
      if (data.success) {
        setServices(data.data.data);
      }
    } catch (error) {
      console.error('Error fetching services:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchServices();
  }, [selectedType, maxPrice]);

  const filteredServices = services.filter(service =>
    service.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    service.description.toLowerCase().includes(searchQuery.toLowerCase())
  );

  const handleBookService = (service: ConciergeService) => {
    // Navigate to booking page or open booking modal
    window.location.href = `/concierge/book/${service.id}`;
  };

  return (
    <div className="container mx-auto px-4 py-8">
      {/* Header */}
      <div className="mb-8">
        <h1 className="text-4xl font-bold text-gray-900 mb-2">Concierge Services</h1>
        <p className="text-lg text-gray-600">Enhance your stay with premium services</p>
      </div>

      {/* Search & Filter Bar */}
      <div className="bg-white rounded-lg shadow-md p-4 mb-8">
        <div className="flex flex-col md:flex-row gap-4">
          {/* Search */}
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" size={20} />
            <input
              type="text"
              placeholder="Search services..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          {/* Filter Toggle */}
          <button
            onClick={() => setShowFilters(!showFilters)}
            className="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <Filter size={20} />
            <span>Filters</span>
          </button>
        </div>

        {/* Filters */}
        {showFilters && (
          <div className="mt-4 pt-4 border-t border-gray-200">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              {/* Service Type Filter */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Service Type
                </label>
                <select
                  value={selectedType}
                  onChange={(e) => setSelectedType(e.target.value)}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="all">All Services</option>
                  {serviceTypes.map((type) => (
                    <option key={type.value} value={type.value}>
                      {type.icon} {type.label}
                    </option>
                  ))}
                </select>
              </div>

              {/* Max Price Filter */}
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Max Price (RON)
                </label>
                <input
                  type="number"
                  placeholder="Enter max price"
                  value={maxPrice}
                  onChange={(e) => setMaxPrice(e.target.value)}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Service Type Tabs */}
      <div className="flex overflow-x-auto gap-2 mb-8 pb-2">
        <button
          onClick={() => setSelectedType('all')}
          className={`px-4 py-2 rounded-full whitespace-nowrap transition-colors ${
            selectedType === 'all'
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          }`}
        >
          All Services
        </button>
        {serviceTypes.map((type) => (
          <button
            key={type.value}
            onClick={() => setSelectedType(type.value)}
            className={`px-4 py-2 rounded-full whitespace-nowrap transition-colors flex items-center gap-2 ${
              selectedType === type.value
                ? 'bg-blue-600 text-white'
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
            }`}
          >
            <span>{type.icon}</span>
            <span>{type.label}</span>
          </button>
        ))}
      </div>

      {/* Loading State */}
      {loading && (
        <div className="text-center py-12">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          <p className="mt-4 text-gray-600">Loading services...</p>
        </div>
      )}

      {/* Services Grid */}
      {!loading && filteredServices.length > 0 && (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {filteredServices.map((service) => (
            <ConciergeServiceCard
              key={service.id}
              service={service}
              onBook={handleBookService}
            />
          ))}
        </div>
      )}

      {/* Empty State */}
      {!loading && filteredServices.length === 0 && (
        <div className="text-center py-12">
          <div className="text-6xl mb-4">🔍</div>
          <h3 className="text-xl font-semibold text-gray-900 mb-2">No services found</h3>
          <p className="text-gray-600">Try adjusting your filters or search query</p>
        </div>
      )}
    </div>
  );
}
