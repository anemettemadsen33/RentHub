'use client';

import { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import { propertiesApi, Property } from '@/lib/api/properties';
import { useAuth } from '@/contexts/AuthContext';

export default function OwnerPropertiesPage() {
  const router = useRouter();
  const { user } = useAuth();
  const [properties, setProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [filter, setFilter] = useState<'all' | 'published' | 'draft' | 'inactive'>('all');

  useEffect(() => {
    if (user?.role !== 'owner' && user?.role !== 'admin') {
      router.push('/');
      return;
    }
    fetchProperties();
  }, [filter]);

  const fetchProperties = async () => {
    setLoading(true);
    setError('');
    
    try {
      const response = await propertiesApi.getMy();
      let data = response.data.data;

      // Filter by status
      if (filter !== 'all') {
        data = data.filter((p: Property) => p.status === filter);
      }

      setProperties(data);
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to load properties');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Are you sure you want to delete this property?')) return;

    try {
      await propertiesApi.delete(id);
      setProperties(properties.filter(p => p.id !== id));
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to delete property');
    }
  };

  const handlePublish = async (id: number) => {
    try {
      await propertiesApi.publish(id);
      fetchProperties();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to publish property');
    }
  };

  const handleUnpublish = async (id: number) => {
    try {
      await propertiesApi.unpublish(id);
      fetchProperties();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Failed to unpublish property');
    }
  };

  const getStatusBadge = (status: string) => {
    const styles = {
      published: 'bg-green-100 text-green-800',
      draft: 'bg-yellow-100 text-yellow-800',
      inactive: 'bg-gray-100 text-gray-800',
    };

    return (
      <span className={`px-2 py-1 text-xs font-semibold rounded-full ${styles[status as keyof typeof styles]}`}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </span>
    );
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading properties...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <div className="mb-8">
          <div className="flex justify-between items-center">
            <div>
              <h1 className="text-3xl font-bold text-gray-900">My Properties</h1>
              <p className="mt-2 text-gray-600">Manage your property listings</p>
            </div>
            <Link
              href="/owner/properties/new"
              className="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition"
            >
              + Add New Property
            </Link>
          </div>
        </div>

        {/* Filters */}
        <div className="mb-6 flex gap-4">
          <button
            onClick={() => setFilter('all')}
            className={`px-4 py-2 rounded-lg ${filter === 'all' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'}`}
          >
            All ({properties.length})
          </button>
          <button
            onClick={() => setFilter('published')}
            className={`px-4 py-2 rounded-lg ${filter === 'published' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'}`}
          >
            Published
          </button>
          <button
            onClick={() => setFilter('draft')}
            className={`px-4 py-2 rounded-lg ${filter === 'draft' ? 'bg-yellow-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'}`}
          >
            Drafts
          </button>
          <button
            onClick={() => setFilter('inactive')}
            className={`px-4 py-2 rounded-lg ${filter === 'inactive' ? 'bg-gray-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'}`}
          >
            Inactive
          </button>
        </div>

        {/* Error Message */}
        {error && (
          <div className="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
            {error}
          </div>
        )}

        {/* Properties Grid */}
        {properties.length === 0 ? (
          <div className="bg-white rounded-lg shadow p-12 text-center">
            <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <h3 className="mt-4 text-lg font-medium text-gray-900">No properties found</h3>
            <p className="mt-2 text-gray-600">Get started by creating your first property listing.</p>
            <Link
              href="/owner/properties/new"
              className="mt-6 inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700"
            >
              Create Property
            </Link>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {properties.map((property) => (
              <div key={property.id} className="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                {/* Property Image */}
                <div className="relative h-48 bg-gray-200">
                  {property.main_image || property.images?.[0] ? (
                    <img
                      src={property.main_image || property.images[0]}
                      alt={property.title}
                      className="w-full h-full object-cover"
                    />
                  ) : (
                    <div className="flex items-center justify-center h-full text-gray-400">
                      <svg className="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                    </div>
                  )}
                  <div className="absolute top-2 right-2">
                    {getStatusBadge(property.status)}
                  </div>
                </div>

                {/* Property Info */}
                <div className="p-4">
                  <h3 className="text-lg font-semibold text-gray-900 mb-2 line-clamp-1">
                    {property.title}
                  </h3>
                  <p className="text-sm text-gray-600 mb-2 line-clamp-1">
                    📍 {property.city}, {property.country}
                  </p>
                  <p className="text-sm text-gray-600 mb-4">
                    🛏️ {property.bedrooms} bed • 🚿 {property.bathrooms} bath • 👥 {property.guests} guests
                  </p>
                  <div className="flex justify-between items-center mb-4">
                    <span className="text-xl font-bold text-blue-600">
                      ${property.price_per_night}<span className="text-sm text-gray-600">/night</span>
                    </span>
                    {property.average_rating && (
                      <div className="flex items-center">
                        <span className="text-yellow-400">★</span>
                        <span className="ml-1 text-sm font-medium">{property.average_rating.toFixed(1)}</span>
                        <span className="ml-1 text-sm text-gray-500">({property.reviews_count})</span>
                      </div>
                    )}
                  </div>

                  {/* Actions */}
                  <div className="flex gap-2">
                    <Link
                      href={`/owner/properties/${property.id}/edit`}
                      className="flex-1 text-center bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 text-sm"
                    >
                      Edit
                    </Link>
                    {property.status === 'published' ? (
                      <button
                        onClick={() => handleUnpublish(property.id)}
                        className="flex-1 bg-gray-600 text-white py-2 rounded-lg hover:bg-gray-700 text-sm"
                      >
                        Unpublish
                      </button>
                    ) : (
                      <button
                        onClick={() => handlePublish(property.id)}
                        className="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 text-sm"
                      >
                        Publish
                      </button>
                    )}
                    <button
                      onClick={() => handleDelete(property.id)}
                      className="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm"
                    >
                      🗑️
                    </button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
