'use client';

import React, { useState, useEffect } from 'react';
import { savedSearchesApi } from '@/services/api/savedSearches';
import { SavedSearch } from '@/types/saved-search';
import { SavedSearchCard } from './SavedSearchCard';
import { useRouter } from 'next/navigation';

export const SavedSearchesList: React.FC = () => {
  const [searches, setSearches] = useState<SavedSearch[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState<'all' | 'active'>('all');
  const router = useRouter();

  useEffect(() => {
    loadSearches();
  }, [filter]);

  const loadSearches = async () => {
    try {
      setLoading(true);
      const data = await savedSearchesApi.getAll(filter === 'active' ? true : undefined);
      setSearches(data);
    } catch (error) {
      console.error('Failed to load saved searches:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleExecute = async (id: number) => {
    try {
      const result = await savedSearchesApi.execute(id);
      router.push(`/search?saved_search=${id}`);
    } catch (error) {
      console.error('Failed to execute search:', error);
    }
  };

  const handleToggleAlerts = async (id: number) => {
    try {
      await savedSearchesApi.toggleAlerts(id);
      loadSearches();
    } catch (error) {
      console.error('Failed to toggle alerts:', error);
    }
  };

  const handleEdit = (id: number) => {
    router.push(`/saved-searches/${id}/edit`);
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Are you sure you want to delete this saved search?')) return;
    
    try {
      await savedSearchesApi.delete(id);
      loadSearches();
    } catch (error) {
      console.error('Failed to delete search:', error);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center items-center min-h-[400px]">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="max-w-7xl mx-auto px-4 py-8">
      <div className="flex items-center justify-between mb-8">
        <div>
          <h1 className="text-3xl font-bold text-gray-900">Saved Searches</h1>
          <p className="text-gray-600 mt-2">Manage your property search alerts</p>
        </div>
        <button
          onClick={() => router.push('/search')}
          className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
        >
          + Create New Search
        </button>
      </div>

      {/* Filter Tabs */}
      <div className="flex gap-4 mb-6">
        <button
          onClick={() => setFilter('all')}
          className={`px-4 py-2 rounded-lg font-medium transition-colors ${
            filter === 'all'
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
          }`}
        >
          All Searches ({searches.length})
        </button>
        <button
          onClick={() => setFilter('active')}
          className={`px-4 py-2 rounded-lg font-medium transition-colors ${
            filter === 'active'
              ? 'bg-blue-600 text-white'
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
          }`}
        >
          Active Alerts
        </button>
      </div>

      {/* Searches Grid */}
      {searches.length === 0 ? (
        <div className="text-center py-12">
          <div className="text-6xl mb-4">🔍</div>
          <h3 className="text-xl font-semibold text-gray-900 mb-2">No saved searches yet</h3>
          <p className="text-gray-600 mb-6">
            Create your first saved search to get alerts for new listings
          </p>
          <button
            onClick={() => router.push('/search')}
            className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium"
          >
            Start Searching
          </button>
        </div>
      ) : (
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          {searches.map((search) => (
            <SavedSearchCard
              key={search.id}
              search={search}
              onExecute={handleExecute}
              onToggleAlerts={handleToggleAlerts}
              onEdit={handleEdit}
              onDelete={handleDelete}
            />
          ))}
        </div>
      )}
    </div>
  );
};
