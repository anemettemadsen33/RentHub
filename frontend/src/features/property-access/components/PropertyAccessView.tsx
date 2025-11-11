'use client';

import { useEffect, useState, useCallback } from 'react';
import { useTranslations } from 'next-intl';
import type { SmartLock, AccessCode, LockActivity } from '@/lib/schemas/smart-lock';
import { usePrivateChannel } from '@/hooks/use-echo';
import {
  getPropertySmartLocks,
  lockDoor,
  unlockDoor,
  getAccessCodes,
  getLockActivities,
  createAccessCode,
} from '../api';

type Status = 'idle' | 'loading' | 'error';

interface Props {
  propertyId: number;
}

export default function PropertyAccessView({ propertyId }: Props) {
  const t = useTranslations('property.access');
  const [locks, setLocks] = useState<SmartLock[]>([]);
  const [selectedLock, setSelectedLock] = useState<number | null>(null);
  const [codes, setCodes] = useState<AccessCode[]>([]);
  const [activities, setActivities] = useState<LockActivity[]>([]);
  const [status, setStatus] = useState<Status>('idle');
  const [error, setError] = useState('');
  const [activeTab, setActiveTab] = useState<'codes' | 'activity'>('codes');
  const [showCodeForm, setShowCodeForm] = useState(false);
  const [newCode, setNewCode] = useState<{ code: string; type: 'one_time' | 'recurring' }>({ code: '', type: 'one_time' });
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
    loadLocks();
  }, [propertyId]);

  useEffect(() => {
    if (selectedLock) {
      loadCodes();
      loadActivities();
    }
  }, [selectedLock, activeTab]);

  // Realtime lock updates
  const handleLockStatusChanged = useCallback((data: any) => {
    if (!data?.lock) return;
    setLocks(prev => prev.map(l => l.id === data.lock.id ? { ...l, ...data.lock } : l));
  }, []);

  const handleAccessCodeCreated = useCallback((data: any) => {
    if (!data?.code) return;
    setCodes(prev => [data.code, ...prev]);
  }, []);

  const handleAccessCodeRevoked = useCallback((data: any) => {
    if (!data?.codeId) return;
    setCodes(prev => prev.map(c => c.id === data.codeId ? { ...c, status: 'revoked' } : c));
  }, []);

  const handleLockActivity = useCallback((data: any) => {
    if (!data?.activity) return;
    // Only show if activity belongs to selected lock
    setActivities(prev => {
      if (selectedLock && data.activity.lockId && data.activity.lockId !== selectedLock) return prev;
      return [data.activity, ...prev];
    });
  }, [selectedLock]);

  useEffect(() => {
    if (!channel) return;
    channel.listen('lock.status.changed', handleLockStatusChanged);
    channel.listen('access.code.created', handleAccessCodeCreated);
    channel.listen('access.code.revoked', handleAccessCodeRevoked);
    channel.listen('lock.activity', handleLockActivity);
    return () => {
      try {
        channel.stopListening('lock.status.changed');
        channel.stopListening('access.code.created');
        channel.stopListening('access.code.revoked');
        channel.stopListening('lock.activity');
      } catch {}
    };
  }, [channel, handleLockStatusChanged, handleAccessCodeCreated, handleAccessCodeRevoked, handleLockActivity]);

  const loadLocks = async () => {
    setStatus('loading');
    setError('');
    try {
      const data = await getPropertySmartLocks(propertyId);
      setLocks(data);
      if (data.length > 0 && !selectedLock) {
        setSelectedLock(data[0].id);
      }
      setStatus('idle');
    } catch (e: any) {
      setError(e?.message || 'Failed to load smart locks');
      setStatus('error');
    }
  };

  const loadCodes = async () => {
    if (!selectedLock) return;
    try {
      const data = await getAccessCodes(propertyId, selectedLock);
      setCodes(data);
    } catch (e: any) {
      console.error('Failed to load access codes', e);
    }
  };

  const loadActivities = async () => {
    if (!selectedLock) return;
    try {
      const data = await getLockActivities(propertyId, selectedLock);
      setActivities(data);
    } catch (e: any) {
      console.error('Failed to load activities', e);
    }
  };

  const handleLock = async (lockId: number) => {
    try {
      await lockDoor(propertyId, lockId);
      setLocks((prev) =>
        prev.map((l) => (l.id === lockId ? { ...l, isLocked: true } : l))
      );
      alert(t('toasts.locked') || 'Door locked');
    } catch (e: any) {
      alert(e?.message || 'Failed to lock');
    }
  };

  const handleUnlock = async (lockId: number) => {
    try {
      await unlockDoor(propertyId, lockId);
      setLocks((prev) =>
        prev.map((l) => (l.id === lockId ? { ...l, isLocked: false } : l))
      );
      alert(t('toasts.unlocked') || 'Door unlocked');
    } catch (e: any) {
      alert(e?.message || 'Failed to unlock');
    }
  };

  const handleCreateCode = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedLock || !newCode.code) return;
    try {
      const created = await createAccessCode(propertyId, selectedLock, newCode);
      setCodes((prev) => [created, ...prev]);
      setNewCode({ code: '', type: 'one_time' });
      setShowCodeForm(false);
      alert(t('toasts.codeCreated') || 'Access code created');
    } catch (e: any) {
      alert(e?.message || 'Failed to create code');
    }
  };

  const getStatusBadge = (lockStatus?: string) => {
    const st = lockStatus || 'offline';
    return (
      <span
        className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${
          st === 'online' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700'
        }`}
      >
        {t(`locks.${st}`) || st}
      </span>
    );
  };

  const getCodeStatusBadge = (codeStatus?: string) => {
    const st = codeStatus || 'active';
    const colors: Record<string, string> = {
      active: 'bg-green-100 text-green-800',
      expired: 'bg-gray-100 text-gray-700',
      revoked: 'bg-red-100 text-red-800',
    };
    return (
      <span
        className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${colors[st] || colors.active}`}
      >
        {t(`codes.${st}`) || st}
      </span>
    );
  };

  const selectedLockData = locks.find((l) => l.id === selectedLock);

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-2xl font-bold">{t('title') || 'Property Access'}</h1>
        <p className="text-sm text-gray-600">
          {t('subtitle') || 'Manage smart locks and access codes'}
        </p>
      </div>

      {status === 'loading' && <p>{t('loading') || 'Loading...'}</p>}
      {status === 'error' && <p className="text-red-600">{error}</p>}

      {locks.length === 0 && status !== 'loading' && (
        <div className="text-center py-12">
          <p className="text-gray-500">{t('noLocks') || 'No smart locks configured'}</p>
          <p className="text-sm text-gray-400 mt-1">
            {t('noLocksDesc') || 'Add smart locks to manage property access digitally'}
          </p>
        </div>
      )}

      {locks.length > 0 && (
        <div className="grid md:grid-cols-3 gap-6">
          {/* Locks Sidebar */}
          <div className="space-y-3">
            <h2 className="text-lg font-semibold">{t('locks.title') || 'Smart Locks'}</h2>
            {locks.map((lock) => (
              <div
                key={lock.id}
                onClick={() => setSelectedLock(lock.id)}
                className={`p-4 border rounded-lg cursor-pointer ${
                  selectedLock === lock.id ? 'border-blue-500 bg-blue-50' : ''
                }`}
              >
                <div className="flex justify-between items-start">
                  <div>
                    <p className="font-medium">{lock.name}</p>
                    {getStatusBadge(lock.status)}
                  </div>
                  <div className="text-right text-xs text-gray-500">
                    {lock.batteryLevel !== undefined && (
                      <p>
                        {t('locks.battery', { level: lock.batteryLevel }) ||
                          `Battery: ${lock.batteryLevel}%`}
                      </p>
                    )}
                  </div>
                </div>
                <div className="mt-2 flex space-x-2">
                  {lock.isLocked ? (
                    <button
                      onClick={(e) => {
                        e.stopPropagation();
                        handleUnlock(lock.id);
                      }}
                      className="text-xs px-2 py-1 bg-blue-600 text-white rounded"
                    >
                      {t('actions.unlock') || 'Unlock'}
                    </button>
                  ) : (
                    <button
                      onClick={(e) => {
                        e.stopPropagation();
                        handleLock(lock.id);
                      }}
                      className="text-xs px-2 py-1 bg-gray-600 text-white rounded"
                    >
                      {t('actions.lock') || 'Lock'}
                    </button>
                  )}
                </div>
              </div>
            ))}
          </div>

          {/* Details Panel */}
          <div className="md:col-span-2 space-y-4">
            {selectedLockData && (
              <>
                <div className="border-b pb-2">
                  <h2 className="text-xl font-semibold">{selectedLockData.name}</h2>
                  <p className="text-sm text-gray-500">
                    {t('locks.lastSync', {
                      time: selectedLockData.lastSyncAt
                        ? new Date(selectedLockData.lastSyncAt).toLocaleString()
                        : 'Never',
                    }) || `Last sync: ${selectedLockData.lastSyncAt || 'Never'}`}
                  </p>
                </div>

                <div className="border-b border-gray-200">
                  <nav className="-mb-px flex space-x-8">
                    <button
                      onClick={() => setActiveTab('codes')}
                      className={`py-2 px-1 border-b-2 font-medium text-sm ${
                        activeTab === 'codes'
                          ? 'border-blue-500 text-blue-600'
                          : 'border-transparent text-gray-500 hover:text-gray-700'
                      }`}
                    >
                      {t('codes.title') || 'Access Codes'}
                    </button>
                    <button
                      onClick={() => setActiveTab('activity')}
                      className={`py-2 px-1 border-b-2 font-medium text-sm ${
                        activeTab === 'activity'
                          ? 'border-blue-500 text-blue-600'
                          : 'border-transparent text-gray-500 hover:text-gray-700'
                      }`}
                    >
                      {t('activity.title') || 'Activity Log'}
                    </button>
                  </nav>
                </div>

                {activeTab === 'codes' && (
                  <div className="space-y-4">
                    <button
                      onClick={() => setShowCodeForm(!showCodeForm)}
                      className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                    >
                      {t('codes.create') || 'Create Code'}
                    </button>

                    {showCodeForm && (
                      <form onSubmit={handleCreateCode} className="border p-4 rounded space-y-3">
                        <div>
                          <label className="block text-sm font-medium mb-1">Code</label>
                          <input
                            type="text"
                            value={newCode.code}
                            onChange={(e) => setNewCode({ ...newCode, code: e.target.value })}
                            className="w-full px-3 py-2 border rounded"
                            required
                          />
                        </div>
                        <div>
                          <label className="block text-sm font-medium mb-1">Type</label>
                          <select
                            value={newCode.type}
                            onChange={(e) =>
                              setNewCode({
                                ...newCode,
                                type: e.target.value as 'one_time' | 'recurring',
                              })
                            }
                            className="w-full px-3 py-2 border rounded"
                          >
                            <option value="one_time">{t('codes.oneTime') || 'One-time'}</option>
                            <option value="recurring">{t('codes.recurring') || 'Recurring'}</option>
                          </select>
                        </div>
                        <button
                          type="submit"
                          className="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                        >
                          Create
                        </button>
                      </form>
                    )}

                    <div className="divide-y">
                      {codes.map((code) => (
                        <div key={code.id} className="py-3 flex justify-between">
                          <div>
                            <p className="font-mono font-medium">{code.code}</p>
                            <p className="text-xs text-gray-500">
                              {code.type === 'one_time'
                                ? t('codes.oneTime')
                                : t('codes.recurring')}
                            </p>
                          </div>
                          {getCodeStatusBadge(code.status)}
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                {activeTab === 'activity' && (
                  <div className="space-y-2">
                    {activities.length === 0 && (
                      <p className="text-gray-500 text-sm">No activity yet</p>
                    )}
                    {activities.map((act) => (
                      <div key={act.id} className="flex justify-between items-start py-2 border-b">
                        <div>
                          <p className="font-medium">
                            {t(`activity.${act.action}`) || act.action || 'Unknown'}
                          </p>
                          <p className="text-xs text-gray-500">{act.description || ''}</p>
                        </div>
                        <p className="text-xs text-gray-500">
                          {act.timestamp
                            ? new Date(act.timestamp).toLocaleString()
                            : 'N/A'}
                        </p>
                      </div>
                    ))}
                  </div>
                )}
              </>
            )}
          </div>
        </div>
      )}
    </div>
  );
}
