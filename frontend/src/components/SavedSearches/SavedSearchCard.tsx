'use client';

import React from 'react';
import { SavedSearch } from '@/types/saved-search';

interface SavedSearchCardProps {
  search: SavedSearch;
  onExecute: (id: number) => void;
  onToggleAlerts: (id: number) => void;
  onEdit: (id: number) => void;
  onDelete: (id: number) => void;
}

export const SavedSearchCard: React.FC<SavedSearchCardProps> = ({
  search,
  onExecute,
  onToggleAlerts,
  onEdit,
  onDelete,
}) => {
  const formatDate = (dateString?: string) => {
    if (!dateString) return 'Never';
    return new Date(dateString).toLocaleDateString();
  };

  const getAlertBadgeColor = (frequency: string) => {
    switch (frequency) {
      case 'instant': return 'bg-red-100 text-red-800';
      case 'daily': return 'bg-blue-100 text-blue-800';
      case 'weekly': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
      <div className="flex items-start justify-between mb-4">
        <div className="flex-1">
          <h3 className="text-lg font-semibold text-gray-900">{search.name}</h3>
          {search.location && (
            <div className="flex items-center text-sm text-gray-600 mt-1">
              📍 {search.location} {search.radius_km && `(${search.radius_km}km)`}
            </div>
          )}
        </div>
        
        {search.enable_alerts && (
          <span className={`px-2 py-1 rounded-full text-xs font-medium ${getAlertBadgeColor(search.alert_frequency)}`}>
            {search.alert_frequency}
          </span>
        )}
      </div>

      <div className="space-y-2 mb-4 text-sm text-gray-600">
        {(search.min_price || search.max_price) && (
          <div>💰 ${search.min_price || 'Any'} - ${search.max_price || 'Any'}/night</div>
        )}
        {search.min_bedrooms && <div>🛏️ {search.min_bedrooms}+ bedrooms</div>}
        {search.property_type && <div>🏠 {search.property_type}</div>}
      </div>

      {search.new_listings_count > 0 && (
        <div className="bg-blue-50 text-blue-700 px-3 py-2 rounded-lg mb-4 text-sm font-medium">
          🎉 {search.new_listings_count} new listings available!
        </div>
      )}

      <div className="flex items-center gap-2">
        <button
          onClick={() => onExecute(search.id)}
          className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Search Now
        </button>
        
        <button
          onClick={() => onToggleAlerts(search.id)}
          className={`px-4 py-2 rounded-lg ${
            search.enable_alerts ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
          }`}
          title={search.enable_alerts ? 'Disable alerts' : 'Enable alerts'}
        >
          🔔
        </button>

        <button
          onClick={() => onEdit(search.id)}
          className="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200"
        >
          ✏️
        </button>

        <button
          onClick={() => onDelete(search.id)}
          className="px-4 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200"
        >
          🗑️
        </button>
      </div>
    </div>
  );
};
