'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import Link from 'next/link';

interface CalendarDay {
  date: string;
  isBlocked: boolean;
  isBooked: boolean;
  customPrice?: number;
  booking?: {
    id: number;
    guest_name: string;
  };
}

export default function PropertyCalendarPage() {
  const params = useParams();
  const propertyId = params.id as string;
  
  const [currentDate, setCurrentDate] = useState(new Date());
  const [calendarData, setCalendarData] = useState<CalendarDay[]>([]);
  const [selectedDates, setSelectedDates] = useState<string[]>([]);
  const [loading, setLoading] = useState(true);
  const [showBlockModal, setShowBlockModal] = useState(false);
  const [showPricingModal, setShowPricingModal] = useState(false);
  const [customPrice, setCustomPrice] = useState('');

  useEffect(() => {
    loadCalendar();
  }, [currentDate]);

  const loadCalendar = async () => {
    try {
      // Mock data - replace with actual API call
      const days: CalendarDay[] = [];
      const year = currentDate.getFullYear();
      const month = currentDate.getMonth();
      const daysInMonth = new Date(year, month + 1, 0).getDate();
      
      for (let i = 1; i <= daysInMonth; i++) {
        const date = new Date(year, month, i);
        days.push({
          date: date.toISOString().split('T')[0],
          isBlocked: Math.random() > 0.9,
          isBooked: Math.random() > 0.8,
          customPrice: Math.random() > 0.7 ? 150 + Math.floor(Math.random() * 100) : undefined,
        });
      }
      
      setCalendarData(days);
    } catch (error) {
      console.error('Failed to load calendar:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleDateClick = (date: string) => {
    setSelectedDates(prev => {
      if (prev.includes(date)) {
        return prev.filter(d => d !== date);
      } else {
        return [...prev, date];
      }
    });
  };

  const handleBlockDates = async () => {
    if (selectedDates.length === 0) return;
    
    try {
      // API call to block dates
      console.log('Blocking dates:', selectedDates);
      setShowBlockModal(false);
      setSelectedDates([]);
      loadCalendar();
    } catch (error) {
      console.error('Failed to block dates:', error);
    }
  };

  const handleUnblockDates = async () => {
    if (selectedDates.length === 0) return;
    
    try {
      // API call to unblock dates
      console.log('Unblocking dates:', selectedDates);
      setSelectedDates([]);
      loadCalendar();
    } catch (error) {
      console.error('Failed to unblock dates:', error);
    }
  };

  const handleSetCustomPricing = async () => {
    if (selectedDates.length === 0 || !customPrice) return;
    
    try {
      // API call to set custom pricing
      console.log('Setting custom pricing:', { dates: selectedDates, price: customPrice });
      setShowPricingModal(false);
      setCustomPrice('');
      setSelectedDates([]);
      loadCalendar();
    } catch (error) {
      console.error('Failed to set custom pricing:', error);
    }
  };

  const goToPreviousMonth = () => {
    setCurrentDate(new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 1));
  };

  const goToNextMonth = () => {
    setCurrentDate(new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 1));
  };

  const monthYear = currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
  const firstDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1).getDay();

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-4 text-gray-600">Loading calendar...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-8 flex items-center justify-between">
          <div>
            <Link href={`/owner/properties/${propertyId}`} className="text-blue-600 hover:text-blue-700 mb-2 inline-block">
              ← Back to Property
            </Link>
            <h1 className="text-3xl font-bold text-gray-900">Calendar Management</h1>
            <p className="mt-2 text-gray-600">Manage availability, pricing, and bookings</p>
          </div>
        </div>

        {/* Action Buttons */}
        {selectedDates.length > 0 && (
          <div className="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div className="flex items-center justify-between">
              <p className="text-sm text-blue-900">
                {selectedDates.length} date{selectedDates.length > 1 ? 's' : ''} selected
              </p>
              <div className="flex space-x-2">
                <button
                  onClick={handleBlockDates}
                  className="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm"
                >
                  Block Dates
                </button>
                <button
                  onClick={handleUnblockDates}
                  className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm"
                >
                  Unblock Dates
                </button>
                <button
                  onClick={() => setShowPricingModal(true)}
                  className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm"
                >
                  Set Custom Price
                </button>
                <button
                  onClick={() => setSelectedDates([])}
                  className="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 text-sm"
                >
                  Clear Selection
                </button>
              </div>
            </div>
          </div>
        )}

        {/* Calendar */}
        <div className="bg-white rounded-lg shadow">
          <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <button
              onClick={goToPreviousMonth}
              className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <svg className="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <h2 className="text-xl font-semibold text-gray-900">{monthYear}</h2>
            <button
              onClick={goToNextMonth}
              className="p-2 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <svg className="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>

          <div className="p-6">
            {/* Day Headers */}
            <div className="grid grid-cols-7 gap-2 mb-2">
              {['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].map((day) => (
                <div key={day} className="text-center text-sm font-semibold text-gray-600 py-2">
                  {day}
                </div>
              ))}
            </div>

            {/* Calendar Days */}
            <div className="grid grid-cols-7 gap-2">
              {/* Empty cells for days before month starts */}
              {Array.from({ length: firstDayOfMonth }).map((_, i) => (
                <div key={`empty-${i}`} className="aspect-square" />
              ))}

              {/* Calendar days */}
              {calendarData.map((day) => {
                const isSelected = selectedDates.includes(day.date);
                const dayNumber = new Date(day.date).getDate();

                return (
                  <button
                    key={day.date}
                    onClick={() => !day.isBooked && handleDateClick(day.date)}
                    disabled={day.isBooked}
                    className={`aspect-square border-2 rounded-lg p-2 transition-all ${
                      day.isBooked
                        ? 'bg-purple-100 border-purple-300 cursor-not-allowed'
                        : day.isBlocked
                        ? 'bg-red-50 border-red-300 hover:bg-red-100'
                        : isSelected
                        ? 'bg-blue-500 border-blue-600 text-white'
                        : 'bg-white border-gray-200 hover:border-blue-300 hover:bg-blue-50'
                    }`}
                  >
                    <div className="text-sm font-semibold">{dayNumber}</div>
                    {day.customPrice && (
                      <div className="text-xs mt-1 font-medium">
                        ${day.customPrice}
                      </div>
                    )}
                    {day.isBooked && (
                      <div className="text-xs mt-1 text-purple-700">Booked</div>
                    )}
                    {day.isBlocked && !day.isBooked && (
                      <div className="text-xs mt-1 text-red-700">Blocked</div>
                    )}
                  </button>
                );
              })}
            </div>
          </div>
        </div>

        {/* Legend */}
        <div className="mt-6 bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Legend</h3>
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div className="flex items-center">
              <div className="w-8 h-8 bg-white border-2 border-gray-200 rounded mr-3"></div>
              <span className="text-sm text-gray-700">Available</span>
            </div>
            <div className="flex items-center">
              <div className="w-8 h-8 bg-purple-100 border-2 border-purple-300 rounded mr-3"></div>
              <span className="text-sm text-gray-700">Booked</span>
            </div>
            <div className="flex items-center">
              <div className="w-8 h-8 bg-red-50 border-2 border-red-300 rounded mr-3"></div>
              <span className="text-sm text-gray-700">Blocked</span>
            </div>
            <div className="flex items-center">
              <div className="w-8 h-8 bg-blue-500 border-2 border-blue-600 rounded mr-3"></div>
              <span className="text-sm text-gray-700">Selected</span>
            </div>
          </div>
        </div>

        {/* Quick Actions */}
        <div className="mt-6 bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Link
              href={`/owner/properties/${propertyId}/calendar/sync`}
              className="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <svg className="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <div className="ml-4">
                <p className="font-medium text-gray-900">Sync Calendars</p>
                <p className="text-sm text-gray-500">Import from other platforms</p>
              </div>
            </Link>

            <button
              onClick={() => setShowPricingModal(true)}
              className="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-left"
            >
              <svg className="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div className="ml-4">
                <p className="font-medium text-gray-900">Pricing Rules</p>
                <p className="text-sm text-gray-500">Set dynamic pricing</p>
              </div>
            </button>

            <Link
              href={`/owner/properties/${propertyId}`}
              className="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <svg className="h-8 w-8 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <div className="ml-4">
                <p className="font-medium text-gray-900">Property Settings</p>
                <p className="text-sm text-gray-500">Manage property details</p>
              </div>
            </Link>
          </div>
        </div>

        {/* Custom Pricing Modal */}
        {showPricingModal && (
          <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">Set Custom Price</h3>
              <p className="text-sm text-gray-600 mb-4">
                Set a custom price for {selectedDates.length} selected date{selectedDates.length > 1 ? 's' : ''}
              </p>
              <div className="mb-4">
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Price per Night ($)
                </label>
                <input
                  type="number"
                  value={customPrice}
                  onChange={(e) => setCustomPrice(e.target.value)}
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Enter price"
                />
              </div>
              <div className="flex justify-end space-x-2">
                <button
                  onClick={() => {
                    setShowPricingModal(false);
                    setCustomPrice('');
                  }}
                  className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                  Cancel
                </button>
                <button
                  onClick={handleSetCustomPricing}
                  disabled={!customPrice}
                  className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Set Price
                </button>
              </div>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
