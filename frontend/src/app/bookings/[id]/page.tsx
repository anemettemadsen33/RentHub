'use client';

import { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { bookingsApi, Booking } from '@/lib/api/bookings';
import { useAuth } from '@/contexts/AuthContext';
import Link from 'next/link';
import Image from 'next/image';

export default function BookingDetailsPage() {
  const params = useParams();
  const router = useRouter();
  const { user } = useAuth();
  const [booking, setBooking] = useState<Booking | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }

    if (params.id) {
      fetchBooking(parseInt(params.id as string));
    }
  }, [params.id, user, router]);

  const fetchBooking = async (id: number) => {
    setLoading(true);
    setError('');

    try {
      const response = await bookingsApi.getById(id);
      if (response.data.success && response.data.data) {
        setBooking(response.data.data);
      }
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      setError(error.response?.data?.message || 'Booking not found');
    } finally {
      setLoading(false);
    }
  };

  const handleCancel = async () => {
    if (!booking || !confirm('Are you sure you want to cancel this booking?')) return;

    try {
      await bookingsApi.cancel(booking.id);
      fetchBooking(booking.id);
    } catch (err: unknown) {
      const error = err as { response?: { data?: { message?: string } } };
      alert(error.response?.data?.message || 'Failed to cancel booking');
    }
  };

  const getStatusBadge = (status: string) => {
    const styles = {
      pending: 'bg-yellow-100 text-yellow-800',
      confirmed: 'bg-green-100 text-green-800',
      cancelled: 'bg-red-100 text-red-800',
      completed: 'bg-blue-100 text-blue-800',
    };

    return (
      <span className={`px-4 py-2 text-sm font-semibold rounded-full ${styles[status as keyof typeof styles]}`}>
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </span>
    );
  };

  const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600 text-lg">Loading booking...</p>
        </div>
      </div>
    );
  }

  if (error || !booking) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h3 className="text-xl font-medium text-gray-900 mb-2">Booking Not Found</h3>
          <p className="text-gray-600 mb-4">{error}</p>
          <Link href="/bookings" className="text-blue-600 hover:text-blue-700">
            Back to My Bookings
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <Link href="/bookings" className="inline-flex items-center text-blue-600 hover:text-blue-700 mb-6">
          <svg className="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to My Bookings
        </Link>

        <div className="flex items-center justify-between mb-8">
          <div>
            <h1 className="text-3xl font-bold text-gray-900">Booking Details</h1>
            <p className="text-gray-600 mt-1">Booking ID: #{booking.id}</p>
          </div>
          {getStatusBadge(booking.status)}
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Main Content */}
          <div className="lg:col-span-2 space-y-6">
            {/* Property Info */}
            <div className="bg-white rounded-lg shadow p-6">
              <h2 className="text-xl font-bold mb-4">Property Information</h2>
              
              <div className="flex gap-4">
                {booking.property?.main_image && (
                  <Image
                    src={booking.property.main_image}
                    alt={booking.property.title}
                    width={128}
                    height={128}
                    className="w-32 h-32 object-cover rounded-lg"
                  />
                )}
                <div>
                  <h3 className="text-lg font-semibold mb-2">{booking.property?.title}</h3>
                  <p className="text-gray-600 mb-2">
                    {booking.property?.street_address}<br />
                    {booking.property?.city}, {booking.property?.country}
                  </p>
                  <Link
                    href={`/properties/${booking.property_id}`}
                    className="text-blue-600 hover:text-blue-700 text-sm"
                  >
                    View Property Details →
                  </Link>
                </div>
              </div>
            </div>

            {/* Trip Details */}
            <div className="bg-white rounded-lg shadow p-6">
              <h2 className="text-xl font-bold mb-4">Trip Details</h2>
              
              <div className="grid grid-cols-2 gap-6">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Check-in</p>
                  <p className="font-semibold text-lg">{formatDate(booking.check_in)}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Check-out</p>
                  <p className="font-semibold text-lg">{formatDate(booking.check_out)}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Duration</p>
                  <p className="font-semibold text-lg">{booking.nights} {booking.nights === 1 ? 'night' : 'nights'}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600 mb-1">Guests</p>
                  <p className="font-semibold text-lg">{booking.guests} {booking.guests === 1 ? 'guest' : 'guests'}</p>
                </div>
              </div>
            </div>

            {/* Guest Information */}
            <div className="bg-white rounded-lg shadow p-6">
              <h2 className="text-xl font-bold mb-4">Guest Information</h2>
              
              <div className="space-y-3">
                <div>
                  <p className="text-sm text-gray-600">Name</p>
                  <p className="font-medium">{booking.guest_name}</p>
                </div>
                <div>
                  <p className="text-sm text-gray-600">Email</p>
                  <p className="font-medium">{booking.guest_email}</p>
                </div>
                {booking.guest_phone && (
                  <div>
                    <p className="text-sm text-gray-600">Phone</p>
                    <p className="font-medium">{booking.guest_phone}</p>
                  </div>
                )}
                {booking.special_requests && (
                  <div>
                    <p className="text-sm text-gray-600">Special Requests</p>
                    <p className="font-medium">{booking.special_requests}</p>
                  </div>
                )}
              </div>
            </div>

            {/* Booking Status */}
            <div className="bg-white rounded-lg shadow p-6">
              <h2 className="text-xl font-bold mb-4">Status & Timeline</h2>
              
              <div className="space-y-4">
                <div className="flex items-center">
                  <div className="w-2 h-2 bg-blue-600 rounded-full mr-3"></div>
                  <div>
                    <p className="font-medium">Booking Created</p>
                    <p className="text-sm text-gray-600">
                      {new Date(booking.created_at).toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit',
                      })}
                    </p>
                  </div>
                </div>

                {booking.confirmed_at && (
                  <div className="flex items-center">
                    <div className="w-2 h-2 bg-green-600 rounded-full mr-3"></div>
                    <div>
                      <p className="font-medium">Booking Confirmed</p>
                      <p className="text-sm text-gray-600">
                        {new Date(booking.confirmed_at).toLocaleString('en-US', {
                          month: 'short',
                          day: 'numeric',
                          year: 'numeric',
                          hour: 'numeric',
                          minute: '2-digit',
                        })}
                      </p>
                    </div>
                  </div>
                )}

                {booking.cancelled_at && (
                  <div className="flex items-center">
                    <div className="w-2 h-2 bg-red-600 rounded-full mr-3"></div>
                    <div>
                      <p className="font-medium">Booking Cancelled</p>
                      <p className="text-sm text-gray-600">
                        {new Date(booking.cancelled_at).toLocaleString('en-US', {
                          month: 'short',
                          day: 'numeric',
                          year: 'numeric',
                          hour: 'numeric',
                          minute: '2-digit',
                        })}
                      </p>
                    </div>
                  </div>
                )}
              </div>
            </div>

            {/* Actions */}
            {booking.status === 'pending' && (
              <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <h3 className="font-semibold text-yellow-900 mb-2">Pending Confirmation</h3>
                <p className="text-sm text-yellow-800 mb-4">
                  Your booking is pending confirmation from the property owner.
                </p>
                <button
                  onClick={handleCancel}
                  className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium"
                >
                  Cancel Booking
                </button>
              </div>
            )}
          </div>

          {/* Sidebar - Price Summary */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-lg shadow p-6 sticky top-4">
              <h2 className="text-xl font-bold mb-4">Price Summary</h2>

              <div className="space-y-3">
                <div className="flex justify-between text-sm">
                  <span className="text-gray-600">
                    ${booking.price_per_night} × {booking.nights} {booking.nights === 1 ? 'night' : 'nights'}
                  </span>
                  <span className="font-medium">${booking.subtotal.toFixed(2)}</span>
                </div>

                {booking.cleaning_fee > 0 && (
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Cleaning fee</span>
                    <span className="font-medium">${booking.cleaning_fee.toFixed(2)}</span>
                  </div>
                )}

                {booking.security_deposit > 0 && (
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Security deposit</span>
                    <span className="font-medium">${booking.security_deposit.toFixed(2)}</span>
                  </div>
                )}

                {booking.taxes > 0 && (
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">Taxes</span>
                    <span className="font-medium">${booking.taxes.toFixed(2)}</span>
                  </div>
                )}

                <div className="border-t pt-3 mt-3">
                  <div className="flex justify-between">
                    <span className="font-bold text-lg">Total</span>
                    <span className="font-bold text-lg text-blue-600">
                      ${booking.total_amount.toFixed(2)}
                    </span>
                  </div>
                </div>
              </div>

              <div className="mt-6 pt-6 border-t">
                <div className="flex justify-between items-center mb-2">
                  <span className="text-sm text-gray-600">Payment Status</span>
                  <span className={`px-2 py-1 text-xs font-medium rounded ${
                    booking.payment_status === 'paid' ? 'bg-green-100 text-green-800' :
                    booking.payment_status === 'refunded' ? 'bg-orange-100 text-orange-800' :
                    'bg-gray-100 text-gray-800'
                  }`}>
                    {booking.payment_status.charAt(0).toUpperCase() + booking.payment_status.slice(1)}
                  </span>
                </div>

                {booking.payment_method && (
                  <p className="text-sm text-gray-600">
                    Payment Method: {booking.payment_method}
                  </p>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
