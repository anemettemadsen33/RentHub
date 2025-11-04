'use client';

import React from 'react';
import Image from 'next/image';
import { Clock, Users, DollarSign, Star } from 'lucide-react';

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

interface ConciergeServiceCardProps {
  service: ConciergeService;
  onBook?: (service: ConciergeService) => void;
}

const getServiceIcon = (type: string) => {
  const icons: Record<string, string> = {
    airport_pickup: '✈️',
    grocery_delivery: '🛒',
    local_experience: '🎭',
    personal_chef: '👨‍🍳',
    spa_service: '💆',
    car_rental: '🚗',
    babysitting: '👶',
    housekeeping: '🧹',
    pet_care: '🐕',
    other: '⭐',
  };
  return icons[type] || '⭐';
};

const formatDuration = (minutes: number | null) => {
  if (!minutes) return 'Variable';
  const hours = Math.floor(minutes / 60);
  const mins = minutes % 60;
  if (hours > 0 && mins > 0) return `${hours}h ${mins}m`;
  if (hours > 0) return `${hours}h`;
  return `${mins}m`;
};

export default function ConciergeServiceCard({ service, onBook }: ConciergeServiceCardProps) {
  return (
    <div className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
      {/* Image */}
      <div className="relative h-48 w-full">
        {service.images && service.images.length > 0 ? (
          <Image
            src={service.images[0]}
            alt={service.name}
            fill
            className="object-cover"
          />
        ) : (
          <div className="w-full h-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center">
            <span className="text-6xl">{getServiceIcon(service.service_type)}</span>
          </div>
        )}
        
        {/* Service Type Badge */}
        <div className="absolute top-3 left-3 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-medium flex items-center gap-1">
          <span>{getServiceIcon(service.service_type)}</span>
          <span className="capitalize">{service.service_type.replace('_', ' ')}</span>
        </div>

        {/* Availability Badge */}
        {!service.is_available && (
          <div className="absolute top-3 right-3 bg-red-500 text-white px-3 py-1 rounded-full text-sm font-medium">
            Unavailable
          </div>
        )}
      </div>

      {/* Content */}
      <div className="p-4">
        <h3 className="text-xl font-semibold text-gray-900 mb-2">{service.name}</h3>
        
        {/* Service Provider */}
        {service.service_provider && (
          <div className="flex items-center gap-2 mb-3">
            <span className="text-sm text-gray-600">{service.service_provider.name}</span>
            <div className="flex items-center gap-1 text-yellow-500">
              <Star size={14} fill="currentColor" />
              <span className="text-sm font-medium">{service.service_provider.rating}</span>
            </div>
          </div>
        )}

        <p className="text-gray-600 text-sm mb-4 line-clamp-2">{service.description}</p>

        {/* Service Details */}
        <div className="flex items-center gap-4 mb-4 text-sm text-gray-600">
          {service.duration_minutes && (
            <div className="flex items-center gap-1">
              <Clock size={16} />
              <span>{formatDuration(service.duration_minutes)}</span>
            </div>
          )}
          
          {service.max_guests && (
            <div className="flex items-center gap-1">
              <Users size={16} />
              <span>Up to {service.max_guests}</span>
            </div>
          )}
        </div>

        {/* Price & Book Button */}
        <div className="flex items-center justify-between pt-4 border-t border-gray-200">
          <div className="flex items-baseline gap-1">
            <DollarSign size={18} className="text-gray-600" />
            <span className="text-2xl font-bold text-gray-900">{service.base_price}</span>
            <span className="text-sm text-gray-600">{service.price_unit}</span>
          </div>
          
          <button
            onClick={() => onBook?.(service)}
            disabled={!service.is_available}
            className={`px-6 py-2 rounded-lg font-medium transition-colors ${
              service.is_available
                ? 'bg-blue-600 text-white hover:bg-blue-700'
                : 'bg-gray-300 text-gray-500 cursor-not-allowed'
            }`}
          >
            Book Now
          </button>
        </div>
      </div>
    </div>
  );
}
