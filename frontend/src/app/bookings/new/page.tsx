'use client';

import { useState, useEffect } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { propertiesApi, Property } from '@/lib/api/properties';
import { bookingsApi, BookingFormData, BookingCalculation } from '@/lib/api/bookings';
import { useAuth } from '@/contexts/AuthContext';
import Link from 'next/link';

export default function NewBookingPage() {
  const router = useRouter();
  const searchParams = useSearchParams();
  const { user } = useAuth();
  const propertyId = searchParams.get('property');

  const [property, setProperty] = useState<Property | null>(null);
  const [loading, setLoading] = useState(true);
  const [calculating, setCalculating] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [calculation, setCalculation] = useState<BookingCalculation | null>(null);

  const [formData, setFormData] = useState<BookingFormData>({
    property_id: parseInt(propertyId || '0'),
    check_in: '',
    check_out: '',
    guests: 1,
    guest_name: user?.name || '',
    guest_email: user?.email || '',
    guest_phone: user?.phone || '',
    special_requests: '',
  });

  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }

    if (!propertyId) {
      router.push('/properties');
      return;
    }

    fetchProperty(parseInt(propertyId));
  }, [propertyId, user]);

  useEffect(() => {
    if (user) {
      setFormData((prev) => ({
        ...prev,
        guest_name: user.name || '',
        guest_email: user.email || '',
        guest_phone: user.phone || '',
      }));
    }
  }, [user]);

  useEffect(() => {
    if (formData.check_in && formData.check_out && formData.guests) {
      calculatePrice();
    }
  }, [formData.check_in, formData.check_out, formData.guests]);

  const fetchProperty = async (id: number) => {
    setLoading(true);
    try {
      const response = await propertiesApi.getById(id);
      if (response.data.success && response.data.data) {
        setProperty(response.data.data);
      }
    } catch (err: any) {
      setError('Property not found');
    } finally {
      setLoading(false);
    }
  };

  const calculatePrice = async () => {
    if (!formData.check_in || !formData.check_out || !formData.guests) return;

    setCalculating(true);
    try {
      const response = await bookingsApi.calculate({
        property_id: formData.property_id,
        check_in: formData.check_in,
        check_out: formData.check_out,
        guests: formData.guests,
      });

      if (response.data.success && response.data.data) {
        setCalculation(response.data.data);
      }
    } catch (err: any) {
      console.error('Calculation error:', err);
    } finally {
      setCalculating(false);
    }
  };

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value, type } = e.target;
    setFormData({
      ...formData,
      [name]: type === 'number' ? parseInt(value) || 0 : value,
    });
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');
    setSubmitting(true);

    try {
      const response = await bookingsApi.create(formData);

      if (response.data.success && response.data.data) {
        router.push(`/bookings/${response.data.data.id}`);
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Failed to create booking');
    } finally {
      setSubmitting(false);
    }
  };

  const getTodayDate = () => {
    return new Date().toISOString().split('T')[0];
  };

  const getTomorrowDate = () => {
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    return tomorrow.toISOString().split('T')[0];
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mx-auto mb-4"></div>
          <p className="text-gray-600 text-lg">Loading...</p>
        </div>
      </div>
    );
  }

  if (!property) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="text-center">
          <h3 className="text-xl font-medium text-gray-900 mb-2">Property Not Found</h3>
          <Link href="/properties" className="text-blue-600 hover:text-blue-700">
            Back to Properties
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 py-8">
      <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Header */}
        <Link href={`/properties/${property.id}`} className="inline-flex items-center text-blue-600 hover:text-blue-700 mb-6">
          <svg className="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Property
        </Link>

        <h1 className="text-3xl font-bold text-gray-900 mb-8">Complete Your Booking</h1>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
          {/* Booking Form */}
          <div className="lg:col-span-2">
            <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow p-6">
              {/* Property Info */}
              <div className="mb-6 pb-6 border-b">
                <h2 className="text-xl font-semibold mb-3">Property Details</h2>
                <div className="flex gap-4">
                  {property.main_image && (
                    <img
                      src={property.main_image}
                      alt={property.title}
                      className="w-24 h-24 object-cover rounded-lg"
                    />
                  )}
                  <div>
                    <h3 className="font-semibold text-lg">{property.title}</h3>
                    <p className="text-gray-600 text-sm">{property.city}, {property.country}</p>
                    <p className="text-blue-600 font-semibold mt-1">
                      ${property.price_per_night} / night
                    </p>
                  </div>
                </div>
              </div>

              {error && (
                <div className="mb-6 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                  {error}
                </div>
              )}

              {/* Dates & Guests */}
              <div className="mb-6">
                <h2 className="text-xl font-semibold mb-4">Trip Details</h2>
                
                <div className="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Check-in Date *
                    </label>
                    <input
                      type="date"
                      name="check_in"
                      required
                      min={getTodayDate()}
                      value={formData.check_in}
                      onChange={handleChange}
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Check-out Date *
                    </label>
                    <input
                      type="date"
                      name="check_out"
                      required
                      min={formData.check_in || getTomorrowDate()}
                      value={formData.check_out}
                      onChange={handleChange}
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Number of Guests *
                  </label>
                  <input
                    type="number"
                    name="guests"
                    required
                    min="1"
                    max={property.guests}
                    value={formData.guests}
                    onChange={handleChange}
                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                  />
                  <p className="text-sm text-gray-500 mt-1">
                    Maximum {property.guests} guests
                  </p>
                </div>
              </div>

              {/* Guest Information */}
              <div className="mb-6">
                <h2 className="text-xl font-semibold mb-4">Guest Information</h2>
                
                <div className="space-y-4">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Full Name *
                    </label>
                    <input
                      type="text"
                      name="guest_name"
                      required
                      value={formData.guest_name}
                      onChange={handleChange}
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Email *
                    </label>
                    <input
                      type="email"
                      name="guest_email"
                      required
                      value={formData.guest_email}
                      onChange={handleChange}
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Phone Number
                    </label>
                    <input
                      type="tel"
                      name="guest_phone"
                      value={formData.guest_phone}
                      onChange={handleChange}
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Special Requests
                    </label>
                    <textarea
                      name="special_requests"
                      rows={3}
                      value={formData.special_requests}
                      onChange={handleChange}
                      placeholder="Any special requests or notes for the host..."
                      className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    />
                  </div>
                </div>
              </div>

              {/* Submit Button */}
              <button
                type="submit"
                disabled={submitting || calculating || !calculation}
                className="w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition font-semibold text-lg disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {submitting ? 'Creating Booking...' : 'Confirm Booking'}
              </button>
            </form>
          </div>

          {/* Price Summary */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-lg shadow p-6 sticky top-4">
              <h2 className="text-xl font-semibold mb-4">Price Summary</h2>

              {calculating ? (
                <div className="text-center py-8">
                  <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-2"></div>
                  <p className="text-sm text-gray-600">Calculating...</p>
                </div>
              ) : calculation ? (
                <div className="space-y-3">
                  <div className="flex justify-between text-sm">
                    <span className="text-gray-600">
                      ${calculation.price_per_night} × {calculation.nights} {calculation.nights === 1 ? 'night' : 'nights'}
                    </span>
                    <span className="font-medium">${calculation.subtotal.toFixed(2)}</span>
                  </div>

                  {calculation.cleaning_fee > 0 && (
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-600">Cleaning fee</span>
                      <span className="font-medium">${calculation.cleaning_fee.toFixed(2)}</span>
                    </div>
                  )}

                  {calculation.security_deposit > 0 && (
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-600">Security deposit</span>
                      <span className="font-medium">${calculation.security_deposit.toFixed(2)}</span>
                    </div>
                  )}

                  {calculation.taxes > 0 && (
                    <div className="flex justify-between text-sm">
                      <span className="text-gray-600">Taxes</span>
                      <span className="font-medium">${calculation.taxes.toFixed(2)}</span>
                    </div>
                  )}

                  <div className="border-t pt-3 mt-3">
                    <div className="flex justify-between">
                      <span className="font-bold text-lg">Total</span>
                      <span className="font-bold text-lg text-blue-600">
                        ${calculation.total_amount.toFixed(2)}
                      </span>
                    </div>
                  </div>
                </div>
              ) : (
                <p className="text-center text-gray-500 py-8">
                  Select dates to see pricing
                </p>
              )}

              <div className="mt-6 pt-6 border-t">
                <p className="text-xs text-gray-500 text-center">
                  You won't be charged yet. The host will review and confirm your booking.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
