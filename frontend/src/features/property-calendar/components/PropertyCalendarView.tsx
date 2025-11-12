'use client';

import { useEffect, useState, useCallback } from 'react';
import { useTranslations } from '@/lib/i18n-temp';
import type { BlockedDate } from '@/lib/schemas/calendar';
import { getBlockedDates, blockDates, unblockDates } from '../api';
import { usePrivateChannel } from '@/hooks/use-echo';

type Status = 'idle' | 'loading' | 'error';

interface Props {
  propertyId: number;
}

export default function PropertyCalendarView({ propertyId }: Props) {
  const t = useTranslations('property.calendar');
  const [blockedDates, setBlockedDates] = useState<BlockedDate[]>([]);
  const [status, setStatus] = useState<Status>('idle');
  const [error, setError] = useState('');
  const [showBlockForm, setShowBlockForm] = useState(false);
  const [blockForm, setBlockForm] = useState({
    startDate: '',
    endDate: '',
    reason: 'maintenance',
  });
  const [userId, setUserId] = useState<number | null>(null);
  const [authToken, setAuthToken] = useState<string>('');

  useEffect(() => {
    if (typeof window === 'undefined') return;
    try {
      setAuthToken(localStorage.getItem('auth_token') || '');
      const rawUser = localStorage.getItem('user');
      if (rawUser) {
        const u = JSON.parse(rawUser);
        if (u?.id) setUserId(Number(u.id));
      }
    } catch {}
  }, []);

  const channel = usePrivateChannel(userId ? `user.${userId}` : '', authToken, !!userId);

  useEffect(() => {
    loadBlockedDates();
  }, [propertyId]);

  // Realtime calendar events
  const handleDatesBlocked = useCallback((data: any) => {
    if (!data?.dates) return;
    setBlockedDates(prev => {
      const newDates: BlockedDate[] = data.dates.filter((d: any) => !prev.some(p => p.id === d.id));
      return [...newDates, ...prev];
    });
  }, []);

  const handleDatesUnblocked = useCallback((data: any) => {
    if (!data?.dateIds) return;
    setBlockedDates(prev => prev.filter(d => !data.dateIds.includes(d.id)));
  }, []);

  useEffect(() => {
    if (!channel) return;
    channel.listen('calendar.dates.blocked', handleDatesBlocked);
    channel.listen('calendar.dates.unblocked', handleDatesUnblocked);
    return () => {
      try {
        channel.stopListening('calendar.dates.blocked');
        channel.stopListening('calendar.dates.unblocked');
      } catch {}
    };
  }, [channel, handleDatesBlocked, handleDatesUnblocked]);

  const loadBlockedDates = async () => {
    setStatus('loading');
    setError('');
    try {
      const data = await getBlockedDates(propertyId);
      setBlockedDates(data);
      setStatus('idle');
    } catch (e: any) {
      setError(e?.message || 'Failed to load blocked dates');
      setStatus('error');
    }
  };

  const handleBlockDates = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!blockForm.startDate || !blockForm.endDate) {
      alert('Please select start and end dates');
      return;
    }

    try {
      await blockDates(
        propertyId,
        blockForm.startDate,
        blockForm.endDate,
        blockForm.reason
      );
      loadBlockedDates();
      setBlockForm({ startDate: '', endDate: '', reason: 'maintenance' });
      setShowBlockForm(false);
      alert(t('toasts.datesBlocked') || 'Dates blocked successfully');
    } catch (e: any) {
      alert(e?.message || t('toasts.actionFailed') || 'Failed to block dates');
    }
  };

  const handleUnblock = async (blocked: BlockedDate) => {
    if (!confirm(`Unblock ${blocked.startDate} to ${blocked.endDate}?`)) return;

    try {
      await unblockDates(propertyId, blocked.startDate, blocked.endDate);
      setBlockedDates((prev) => prev.filter((b) => b.id !== blocked.id));
      alert(t('toasts.datesUnblocked') || 'Dates unblocked');
    } catch (e: any) {
      alert(e?.message || 'Failed to unblock dates');
    }
  };

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">
          {t('title') || 'Availability Calendar'}
        </h1>
        <p className="text-sm text-gray-600">
          {t('subtitle') || 'Manage pricing and availability'}
        </p>
      </div>

      <div className="flex justify-between items-center border-b pb-4">
        <div className="flex space-x-4 text-sm">
          <div className="flex items-center">
            <span className="w-4 h-4 bg-green-200 rounded mr-2"></span>
            {t('legend.available') || 'Available'}
          </div>
          <div className="flex items-center">
            <span className="w-4 h-4 bg-blue-200 rounded mr-2"></span>
            {t('legend.booked') || 'Booked'}
          </div>
          <div className="flex items-center">
            <span className="w-4 h-4 bg-red-200 rounded mr-2"></span>
            {t('legend.blocked') || 'Blocked'}
          </div>
          <div className="flex items-center">
            <span className="w-4 h-4 bg-yellow-200 rounded mr-2"></span>
            {t('legend.pending') || 'Pending'}
          </div>
        </div>

        <button
          onClick={() => setShowBlockForm(!showBlockForm)}
          className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        >
          {t('availability.blockDates') || 'Block Dates'}
        </button>
      </div>

      {showBlockForm && (
        <form onSubmit={handleBlockDates} className="border p-6 rounded space-y-4">
          <h3 className="text-lg font-semibold">
            {t('availability.blockDates') || 'Block Dates'}
          </h3>
          <div className="grid md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium mb-1">
                Start Date <span className="text-red-500">*</span>
              </label>
              <input
                type="date"
                value={blockForm.startDate}
                onChange={(e) =>
                  setBlockForm({ ...blockForm, startDate: e.target.value })
                }
                className="w-full px-3 py-2 border rounded"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium mb-1">
                End Date <span className="text-red-500">*</span>
              </label>
              <input
                type="date"
                value={blockForm.endDate}
                onChange={(e) =>
                  setBlockForm({ ...blockForm, endDate: e.target.value })
                }
                className="w-full px-3 py-2 border rounded"
                required
              />
            </div>
          </div>
          <div>
            <label className="block text-sm font-medium mb-1">
              {t('availability.reason') || 'Reason'}
            </label>
            <select
              value={blockForm.reason}
              onChange={(e) =>
                setBlockForm({ ...blockForm, reason: e.target.value })
              }
              className="w-full px-3 py-2 border rounded"
            >
              <option value="maintenance">
                {t('availability.maintenance') || 'Maintenance'}
              </option>
              <option value="personal">
                {t('availability.personal') || 'Personal Use'}
              </option>
              <option value="other">{t('availability.other') || 'Other'}</option>
            </select>
          </div>
          <div className="flex space-x-3">
            <button
              type="submit"
              className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
            >
              Block
            </button>
            <button
              type="button"
              onClick={() => setShowBlockForm(false)}
              className="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400"
            >
              Cancel
            </button>
          </div>
        </form>
      )}

      {status === 'loading' && <p>{t('loading') || 'Loading...'}</p>}
      {status === 'error' && <p className="text-red-600">{error}</p>}

      <div className="space-y-4">
        <h2 className="text-lg font-semibold">Blocked Dates</h2>
        {blockedDates.length === 0 && status !== 'loading' && (
          <p className="text-gray-500">No blocked dates</p>
        )}

        {blockedDates.length > 0 && (
          <div className="divide-y">
            {blockedDates.map((blocked) => (
              <div
                key={blocked.id}
                className="py-4 flex justify-between items-start"
              >
                <div>
                  <p className="font-medium">
                    {new Date(blocked.startDate).toLocaleDateString()} -{' '}
                    {new Date(blocked.endDate).toLocaleDateString()}
                  </p>
                  {blocked.reason && (
                    <p className="text-sm text-gray-500 capitalize">
                      {blocked.reason.replace('_', ' ')}
                    </p>
                  )}
                </div>
                <button
                  onClick={() => handleUnblock(blocked)}
                  className="text-sm px-3 py-1 text-red-600 hover:underline"
                >
                  {t('availability.unblockDates') || 'Unblock'}
                </button>
              </div>
            ))}
          </div>
        )}
      </div>

      <div className="border-t pt-6">
        <h2 className="text-lg font-semibold mb-3">
          {t('sync.title') || 'Calendar Sync'}
        </h2>
        <p className="text-sm text-gray-600 mb-4">
          Sync with external platforms (Airbnb, Booking.com) to prevent double bookings
        </p>
        <div className="grid md:grid-cols-2 gap-4">
          <button className="px-4 py-2 border rounded hover:bg-gray-50">
            {t('sync.import') || 'Import Calendar'}
          </button>
          <button className="px-4 py-2 border rounded hover:bg-gray-50">
            {t('sync.export') || 'Export Calendar (iCal)'}
          </button>
        </div>
      </div>
    </div>
  );
}
