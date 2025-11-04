'use client';

import { useEffect, useState } from 'react';
import Link from 'next/link';

interface Wishlist {
  id: number;
  name: string;
  description?: string;
  is_public: boolean;
  items_count: number;
  created_at: string;
}

export default function WishlistsPage() {
  const [wishlists, setWishlists] = useState<Wishlist[]>([]);
  const [loading, setLoading] = useState(true);
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [newWishlistName, setNewWishlistName] = useState('');
  const [newWishlistDescription, setNewWishlistDescription] = useState('');
  const [newWishlistPublic, setNewWishlistPublic] = useState(false);

  useEffect(() => {
    loadWishlists();
  }, []);

  const loadWishlists = async () => {
    try {
      // Mock data - replace with actual API call
      const mockWishlists: Wishlist[] = [
        {
          id: 1,
          name: 'Beach Vacations',
          description: 'Properties near the beach',
          is_public: false,
          items_count: 5,
          created_at: new Date().toISOString(),
        },
        {
          id: 2,
          name: 'City Apartments',
          description: 'Urban properties for business trips',
          is_public: true,
          items_count: 3,
          created_at: new Date().toISOString(),
        },
      ];
      
      setWishlists(mockWishlists);
    } catch (error) {
      console.error('Failed to load wishlists:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleCreateWishlist = async () => {
    if (!newWishlistName.trim()) return;

    try {
      // API call to create wishlist
      console.log('Creating wishlist:', {
        name: newWishlistName,
        description: newWishlistDescription,
        is_public: newWishlistPublic,
      });

      setShowCreateModal(false);
      setNewWishlistName('');
      setNewWishlistDescription('');
      setNewWishlistPublic(false);
      loadWishlists();
    } catch (error) {
      console.error('Failed to create wishlist:', error);
    }
  };

  const handleDeleteWishlist = async (id: number) => {
    if (!confirm('Are you sure you want to delete this wishlist?')) return;

    try {
      // API call to delete wishlist
      console.log('Deleting wishlist:', id);
      loadWishlists();
    } catch (error) {
      console.error('Failed to delete wishlist:', error);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading wishlists...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-8 flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">My Wishlists</h1>
            <p className="mt-2 text-gray-600">Save and organize your favorite properties</p>
          </div>
          <button
            onClick={() => setShowCreateModal(true)}
            className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center"
          >
            <svg className="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Create Wishlist
          </button>
        </div>

        {wishlists.length === 0 ? (
          <div className="bg-white rounded-lg shadow p-12 text-center">
            <svg className="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <h3 className="mt-4 text-lg font-medium text-gray-900">No wishlists yet</h3>
            <p className="mt-2 text-gray-500">Start saving your favorite properties by creating a wishlist</p>
            <button
              onClick={() => setShowCreateModal(true)}
              className="mt-6 inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Create Your First Wishlist
            </button>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {wishlists.map((wishlist) => (
              <div key={wishlist.id} className="bg-white rounded-lg shadow overflow-hidden hover:shadow-lg transition-shadow">
                <Link href={`/wishlists/${wishlist.id}`} className="block">
                  <div className="p-6">
                    <div className="flex items-start justify-between mb-4">
                      <div className="flex-1">
                        <h3 className="text-lg font-semibold text-gray-900 mb-2">{wishlist.name}</h3>
                        {wishlist.description && (
                          <p className="text-sm text-gray-600">{wishlist.description}</p>
                        )}
                      </div>
                      <svg className="h-6 w-6 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fillRule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clipRule="evenodd" />
                      </svg>
                    </div>

                    <div className="flex items-center justify-between text-sm text-gray-500">
                      <span>{wishlist.items_count} properties</span>
                      {wishlist.is_public ? (
                        <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          <svg className="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                          </svg>
                          Public
                        </span>
                      ) : (
                        <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                          <svg className="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                          </svg>
                          Private
                        </span>
                      )}
                    </div>
                  </div>
                </Link>

                <div className="border-t border-gray-200 px-6 py-3 bg-gray-50 flex justify-between">
                  <Link
                    href={`/wishlists/${wishlist.id}`}
                    className="text-sm text-blue-600 hover:text-blue-700 font-medium"
                  >
                    View Properties
                  </Link>
                  <button
                    onClick={() => handleDeleteWishlist(wishlist.id)}
                    className="text-sm text-red-600 hover:text-red-700 font-medium"
                  >
                    Delete
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}

        {/* Create Wishlist Modal */}
        {showCreateModal && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Create New Wishlist</h3>
              
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Wishlist Name <span className="text-red-500">*</span>
                  </label>
                  <input
                    type="text"
                    value={newWishlistName}
                    onChange={(e) => setNewWishlistName(e.target.value)}
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="e.g., Beach Vacations"
                  />
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Description (Optional)
                  </label>
                  <textarea
                    value={newWishlistDescription}
                    onChange={(e) => setNewWishlistDescription(e.target.value)}
                    rows={3}
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Add a description..."
                  />
                </div>

                <div className="flex items-center">
                  <input
                    type="checkbox"
                    id="public"
                    checked={newWishlistPublic}
                    onChange={(e) => setNewWishlistPublic(e.target.checked)}
                    className="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label htmlFor="public" className="ml-2 block text-sm text-gray-700">
                    Make this wishlist public (others can view it)
                  </label>
                </div>
              </div>

              <div className="mt-6 flex justify-end space-x-2">
                <button
                  onClick={() => {
                    setShowCreateModal(false);
                    setNewWishlistName('');
                    setNewWishlistDescription('');
                    setNewWishlistPublic(false);
                  }}
                  className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  onClick={handleCreateWishlist}
                  disabled={!newWishlistName.trim()}
                  className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Create Wishlist
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
