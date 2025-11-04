'use client';

import { useEffect, useState } from 'react';
import { reviewsApi } from '@/lib/api/reviews';
import Link from 'next/link';

interface Review {
  id: number;
  property_id: number;
  user_id: number;
  rating: number;
  comment?: string;
  cleanliness_rating?: number;
  communication_rating?: number;
  check_in_rating?: number;
  accuracy_rating?: number;
  location_rating?: number;
  value_rating?: number;
  owner_response?: string;
  property?: {
    id: number;
    title: string;
  };
  user?: {
    id: number;
    name: string;
    avatar?: string;
  };
  created_at: string;
}

export default function ReviewsPage() {
  const [reviews, setReviews] = useState<Review[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    loadReviews();
  }, []);

  const loadReviews = async () => {
    try {
      const response: any = await reviewsApi.getMyReviews();
      setReviews(response.data || []);
    } catch (err: any) {
      setError('Failed to load reviews');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const renderStars = (rating: number) => {
    return (
      <div className="flex items-center">
        {[1, 2, 3, 4, 5].map((star) => (
          <svg
            key={star}
            className={`h-5 w-5 ${star <= rating ? 'text-yellow-400' : 'text-gray-300'}`}
            fill="currentColor"
            viewBox="0 0 20 20"
          >
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        ))}
        <span className="ml-2 text-sm text-gray-600">{rating.toFixed(1)}</span>
      </div>
    );
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading reviews...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">My Reviews</h1>
          <p className="mt-2 text-gray-600">Reviews you've written for properties</p>
        </div>

        {error && (
          <div className="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded">
            {error}
          </div>
        )}

        {reviews.length === 0 ? (
          <div className="bg-white rounded-lg shadow p-12 text-center">
            <svg className="mx-auto h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
            <h3 className="mt-4 text-lg font-medium text-gray-900">No reviews yet</h3>
            <p className="mt-2 text-gray-500">After staying at a property, you can leave a review</p>
            <Link href="/properties" className="mt-6 inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
              Browse Properties
            </Link>
          </div>
        ) : (
          <div className="space-y-6">
            {reviews.map((review) => (
              <div key={review.id} className="bg-white rounded-lg shadow overflow-hidden">
                <div className="p-6">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      {review.property && (
                        <Link href={`/properties/${review.property.id}`} className="text-lg font-semibold text-blue-600 hover:text-blue-700">
                          {review.property.title}
                        </Link>
                      )}
                      <div className="mt-2">
                        {renderStars(review.rating)}
                      </div>
                    </div>
                    <div className="text-sm text-gray-500">
                      {formatDate(review.created_at)}
                    </div>
                  </div>

                  {review.comment && (
                    <div className="mt-4">
                      <p className="text-gray-700">{review.comment}</p>
                    </div>
                  )}

                  {/* Detailed Ratings */}
                  {(review.cleanliness_rating || review.communication_rating || review.check_in_rating || review.accuracy_rating || review.location_rating || review.value_rating) && (
                    <div className="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4">
                      {review.cleanliness_rating && (
                        <div>
                          <p className="text-sm text-gray-600">Cleanliness</p>
                          <div className="flex items-center mt-1">
                            <div className="flex-1 bg-gray-200 rounded-full h-2">
                              <div className="bg-blue-600 h-2 rounded-full" style={{ width: `${(review.cleanliness_rating / 5) * 100}%` }}></div>
                            </div>
                            <span className="ml-2 text-sm font-medium text-gray-900">{review.cleanliness_rating}</span>
                          </div>
                        </div>
                      )}
                      {review.communication_rating && (
                        <div>
                          <p className="text-sm text-gray-600">Communication</p>
                          <div className="flex items-center mt-1">
                            <div className="flex-1 bg-gray-200 rounded-full h-2">
                              <div className="bg-blue-600 h-2 rounded-full" style={{ width: `${(review.communication_rating / 5) * 100}%` }}></div>
                            </div>
                            <span className="ml-2 text-sm font-medium text-gray-900">{review.communication_rating}</span>
                          </div>
                        </div>
                      )}
                      {review.location_rating && (
                        <div>
                          <p className="text-sm text-gray-600">Location</p>
                          <div className="flex items-center mt-1">
                            <div className="flex-1 bg-gray-200 rounded-full h-2">
                              <div className="bg-blue-600 h-2 rounded-full" style={{ width: `${(review.location_rating / 5) * 100}%` }}></div>
                            </div>
                            <span className="ml-2 text-sm font-medium text-gray-900">{review.location_rating}</span>
                          </div>
                        </div>
                      )}
                    </div>
                  )}

                  {/* Owner Response */}
                  {review.owner_response && (
                    <div className="mt-4 bg-gray-50 rounded-lg p-4">
                      <p className="text-sm font-medium text-gray-900 mb-2">Response from owner:</p>
                      <p className="text-sm text-gray-700">{review.owner_response}</p>
                    </div>
                  )}
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
