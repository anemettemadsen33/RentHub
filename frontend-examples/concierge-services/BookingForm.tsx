'use client';

import React, { useState } from 'react';
import { Calendar, Clock, Users, Mail, Phone, User, MessageSquare } from 'lucide-react';

interface ConciergeService {
  id: number;
  name: string;
  base_price: number;
  max_guests: number | null;
  advance_booking_hours: number;
  pricing_extras?: Array<{ name: string; price: number }>;
}

interface BookingFormProps {
  service: ConciergeService;
  onSubmit: (bookingData: any) => Promise<void>;
  onCancel: () => void;
}

export default function BookingForm({ service, onSubmit, onCancel }: BookingFormProps) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [formData, setFormData] = useState({
    service_date: '',
    service_time: '',
    guests_count: 1,
    special_requests: '',
    contact_details: {
      name: '',
      phone: '',
      email: '',
    },
  });
  const [selectedExtras, setSelectedExtras] = useState<number[]>([]);

  const handleInputChange = (field: string, value: any) => {
    setFormData(prev => ({ ...prev, [field]: value }));
  };

  const handleContactChange = (field: string, value: string) => {
    setFormData(prev => ({
      ...prev,
      contact_details: {
        ...prev.contact_details,
        [field]: value,
      },
    }));
  };

  const toggleExtra = (index: number) => {
    setSelectedExtras(prev =>
      prev.includes(index) ? prev.filter(i => i !== index) : [...prev, index]
    );
  };

  const calculateTotal = () => {
    let total = service.base_price;
    if (service.pricing_extras) {
      selectedExtras.forEach(index => {
        total += service.pricing_extras![index].price;
      });
    }
    return total;
  };

  const getMinDate = () => {
    const minDate = new Date();
    minDate.setHours(minDate.getHours() + service.advance_booking_hours);
    return minDate.toISOString().split('T')[0];
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    // Validation
    if (!formData.service_date || !formData.service_time) {
      setError('Please select date and time');
      return;
    }

    if (!formData.contact_details.name || !formData.contact_details.phone || !formData.contact_details.email) {
      setError('Please fill in all contact details');
      return;
    }

    if (service.max_guests && formData.guests_count > service.max_guests) {
      setError(`Maximum ${service.max_guests} guests allowed`);
      return;
    }

    setLoading(true);
    try {
      await onSubmit({
        concierge_service_id: service.id,
        ...formData,
      });
    } catch (err: any) {
      setError(err.message || 'Failed to create booking');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="bg-white rounded-lg shadow-xl p-6 max-w-2xl mx-auto">
      <h2 className="text-2xl font-bold text-gray-900 mb-6">Book {service.name}</h2>

      {error && (
        <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
          {error}
        </div>
      )}

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* Date & Time */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              <Calendar size={16} className="inline mr-2" />
              Service Date
            </label>
            <input
              type="date"
              min={getMinDate()}
              value={formData.service_date}
              onChange={(e) => handleInputChange('service_date', e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              <Clock size={16} className="inline mr-2" />
              Service Time
            </label>
            <input
              type="time"
              value={formData.service_time}
              onChange={(e) => handleInputChange('service_time', e.target.value)}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
          </div>
        </div>

        {/* Number of Guests */}
        {service.max_guests && (
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              <Users size={16} className="inline mr-2" />
              Number of Guests
            </label>
            <input
              type="number"
              min="1"
              max={service.max_guests}
              value={formData.guests_count}
              onChange={(e) => handleInputChange('guests_count', parseInt(e.target.value))}
              className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              required
            />
            <p className="text-sm text-gray-500 mt-1">Maximum {service.max_guests} guests</p>
          </div>
        )}

        {/* Extras */}
        {service.pricing_extras && service.pricing_extras.length > 0 && (
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-3">
              Additional Services
            </label>
            <div className="space-y-2">
              {service.pricing_extras.map((extra, index) => (
                <label key={index} className="flex items-center justify-between p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                  <div className="flex items-center">
                    <input
                      type="checkbox"
                      checked={selectedExtras.includes(index)}
                      onChange={() => toggleExtra(index)}
                      className="mr-3 h-4 w-4 text-blue-600"
                    />
                    <span className="text-gray-700">{extra.name}</span>
                  </div>
                  <span className="font-medium text-gray-900">+{extra.price} RON</span>
                </label>
              ))}
            </div>
          </div>
        )}

        {/* Contact Details */}
        <div className="border-t border-gray-200 pt-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
          
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                <User size={16} className="inline mr-2" />
                Full Name
              </label>
              <input
                type="text"
                value={formData.contact_details.name}
                onChange={(e) => handleContactChange('name', e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  <Phone size={16} className="inline mr-2" />
                  Phone Number
                </label>
                <input
                  type="tel"
                  value={formData.contact_details.phone}
                  onChange={(e) => handleContactChange('phone', e.target.value)}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  <Mail size={16} className="inline mr-2" />
                  Email Address
                </label>
                <input
                  type="email"
                  value={formData.contact_details.email}
                  onChange={(e) => handleContactChange('email', e.target.value)}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                />
              </div>
            </div>
          </div>
        </div>

        {/* Special Requests */}
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            <MessageSquare size={16} className="inline mr-2" />
            Special Requests (Optional)
          </label>
          <textarea
            value={formData.special_requests}
            onChange={(e) => handleInputChange('special_requests', e.target.value)}
            rows={4}
            placeholder="Any special requests or dietary restrictions..."
            className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>

        {/* Price Summary */}
        <div className="bg-gray-50 rounded-lg p-4 border border-gray-200">
          <div className="flex justify-between items-center mb-2">
            <span className="text-gray-600">Base Price:</span>
            <span className="font-medium">{service.base_price} RON</span>
          </div>
          {selectedExtras.length > 0 && service.pricing_extras && (
            <>
              {selectedExtras.map(index => (
                <div key={index} className="flex justify-between items-center mb-2 text-sm">
                  <span className="text-gray-600">{service.pricing_extras[index].name}:</span>
                  <span className="font-medium">{service.pricing_extras[index].price} RON</span>
                </div>
              ))}
            </>
          )}
          <div className="border-t border-gray-300 pt-2 mt-2">
            <div className="flex justify-between items-center">
              <span className="text-lg font-semibold text-gray-900">Total:</span>
              <span className="text-2xl font-bold text-blue-600">{calculateTotal()} RON</span>
            </div>
          </div>
        </div>

        {/* Action Buttons */}
        <div className="flex gap-4">
          <button
            type="button"
            onClick={onCancel}
            className="flex-1 px-6 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            disabled={loading}
            className="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
          >
            {loading ? 'Booking...' : 'Confirm Booking'}
          </button>
        </div>
      </form>
    </div>
  );
}
