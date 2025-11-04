// 🔐 Smart Locks Integration - Next.js Frontend Examples
// Copy these components to your Next.js frontend

'use client';

import { useState, useEffect } from 'react';
import { Lock, Unlock, Key, Activity, Battery, Wifi, AlertCircle } from 'lucide-react';

// ============================================
// Types & Interfaces
// ============================================

interface SmartLock {
  id: number;
  property_id: number;
  provider: string;
  lock_id: string;
  name: string;
  location: string;
  status: 'active' | 'inactive' | 'offline' | 'error';
  auto_generate_codes: boolean;
  battery_level: string | null;
  last_synced_at: string | null;
}

interface AccessCode {
  id: number;
  smart_lock_id: number;
  booking_id?: number;
  code: string;
  type: 'temporary' | 'permanent' | 'one_time';
  valid_from: string;
  valid_until: string | null;
  status: 'pending' | 'active' | 'expired' | 'revoked';
  uses_count: number;
  max_uses: number | null;
  notified: boolean;
}

interface LockActivity {
  id: number;
  event_type: 'unlock' | 'lock' | 'code_used' | 'code_created' | 'code_deleted' | 'battery_low' | 'error';
  code_used?: string;
  access_method?: 'code' | 'app' | 'key' | 'remote' | 'auto';
  description?: string;
  event_at: string;
  user?: {
    id: number;
    name: string;
  };
}

// ============================================
// 1. Smart Lock Card Component
// ============================================

export function SmartLockCard({ lock }: { lock: SmartLock }) {
  const [isLocked, setIsLocked] = useState(true);
  const [isLoading, setIsLoading] = useState(false);

  const handleToggleLock = async () => {
    setIsLoading(true);
    try {
      const endpoint = isLocked ? 'unlock' : 'lock';
      const response = await fetch(
        `/api/properties/${lock.property_id}/smart-locks/${lock.id}/${endpoint}`,
        {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
          },
        }
      );

      if (response.ok) {
        setIsLocked(!isLocked);
      }
    } catch (error) {
      console.error('Lock control error:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const batteryLevel = parseInt(lock.battery_level || '0');
  const isLowBattery = batteryLevel < 20;
  const isOnline = lock.status === 'active' && lock.last_synced_at;

  return (
    <div className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
      {/* Header */}
      <div className="flex items-start justify-between mb-4">
        <div className="flex-1">
          <h3 className="text-lg font-semibold text-gray-900">{lock.name}</h3>
          <p className="text-sm text-gray-600">{lock.location}</p>
        </div>
        
        {/* Status Badge */}
        <div className="flex items-center space-x-2">
          {isOnline ? (
            <span className="flex items-center text-green-600 text-sm">
              <Wifi className="w-4 h-4 mr-1" />
              Online
            </span>
          ) : (
            <span className="flex items-center text-red-600 text-sm">
              <AlertCircle className="w-4 h-4 mr-1" />
              Offline
            </span>
          )}
        </div>
      </div>

      {/* Lock Status */}
      <div className="flex items-center justify-center py-8">
        <button
          onClick={handleToggleLock}
          disabled={isLoading || !isOnline}
          className={`relative p-8 rounded-full transition-all ${
            isLocked
              ? 'bg-red-100 hover:bg-red-200'
              : 'bg-green-100 hover:bg-green-200'
          } ${isLoading ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'}`}
        >
          {isLocked ? (
            <Lock className="w-12 h-12 text-red-600" />
          ) : (
            <Unlock className="w-12 h-12 text-green-600" />
          )}
        </button>
      </div>

      {/* Battery & Info */}
      <div className="flex items-center justify-between pt-4 border-t">
        <div className="flex items-center space-x-2">
          <Battery className={`w-5 h-5 ${isLowBattery ? 'text-red-600' : 'text-gray-600'}`} />
          <span className={`text-sm ${isLowBattery ? 'text-red-600 font-semibold' : 'text-gray-600'}`}>
            {lock.battery_level || 'N/A'}%
          </span>
        </div>

        <div className="text-sm text-gray-600">
          Provider: <span className="font-medium capitalize">{lock.provider}</span>
        </div>
      </div>
    </div>
  );
}

// ============================================
// 2. Access Code List Component
// ============================================

export function AccessCodeList({ lockId, propertyId }: { lockId: number; propertyId: number }) {
  const [codes, setCodes] = useState<AccessCode[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [filter, setFilter] = useState<'all' | 'active' | 'expired'>('active');

  useEffect(() => {
    fetchCodes();
  }, [lockId, filter]);

  const fetchCodes = async () => {
    setIsLoading(true);
    try {
      const params = filter !== 'all' ? `?status=${filter}` : '';
      const response = await fetch(
        `/api/properties/${propertyId}/smart-locks/${lockId}/access-codes${params}`,
        {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
          },
        }
      );
      const data = await response.json();
      setCodes(data.data.data || []);
    } catch (error) {
      console.error('Failed to fetch codes:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleRevoke = async (codeId: number) => {
    if (!confirm('Are you sure you want to revoke this access code?')) return;

    try {
      await fetch(
        `/api/properties/${propertyId}/smart-locks/${lockId}/access-codes/${codeId}`,
        {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
          },
        }
      );
      fetchCodes();
    } catch (error) {
      console.error('Failed to revoke code:', error);
    }
  };

  if (isLoading) {
    return <div className="text-center py-8">Loading access codes...</div>;
  }

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      {/* Header */}
      <div className="flex items-center justify-between mb-6">
        <h3 className="text-xl font-semibold flex items-center">
          <Key className="w-5 h-5 mr-2" />
          Access Codes
        </h3>

        {/* Filter */}
        <div className="flex space-x-2">
          {['all', 'active', 'expired'].map((f) => (
            <button
              key={f}
              onClick={() => setFilter(f as any)}
              className={`px-4 py-2 rounded-md text-sm font-medium transition-colors ${
                filter === f
                  ? 'bg-blue-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              }`}
            >
              {f.charAt(0).toUpperCase() + f.slice(1)}
            </button>
          ))}
        </div>
      </div>

      {/* Code List */}
      <div className="space-y-4">
        {codes.length === 0 ? (
          <p className="text-center text-gray-500 py-8">No access codes found</p>
        ) : (
          codes.map((code) => (
            <AccessCodeItem
              key={code.id}
              code={code}
              onRevoke={() => handleRevoke(code.id)}
            />
          ))
        )}
      </div>
    </div>
  );
}

function AccessCodeItem({ code, onRevoke }: { code: AccessCode; onRevoke: () => void }) {
  const [showCode, setShowCode] = useState(false);
  
  const isValid = code.status === 'active' && 
    new Date(code.valid_from) <= new Date() &&
    (!code.valid_until || new Date(code.valid_until) >= new Date());

  return (
    <div className="border rounded-lg p-4 hover:bg-gray-50 transition-colors">
      <div className="flex items-start justify-between">
        <div className="flex-1">
          {/* Code */}
          <div className="flex items-center space-x-3 mb-2">
            <button
              onClick={() => setShowCode(!showCode)}
              className="font-mono text-2xl font-bold tracking-wider text-gray-900"
            >
              {showCode ? code.code : '••••••'}
            </button>
            <span className={`px-2 py-1 rounded text-xs font-medium ${
              isValid ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
            }`}>
              {code.status}
            </span>
          </div>

          {/* Details */}
          <div className="text-sm text-gray-600 space-y-1">
            <div>Valid from: {new Date(code.valid_from).toLocaleString()}</div>
            {code.valid_until && (
              <div>Valid until: {new Date(code.valid_until).toLocaleString()}</div>
            )}
            <div>Type: <span className="font-medium capitalize">{code.type}</span></div>
            {code.max_uses && (
              <div>Uses: {code.uses_count} / {code.max_uses}</div>
            )}
          </div>
        </div>

        {/* Actions */}
        {code.status === 'active' && (
          <button
            onClick={onRevoke}
            className="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded-md transition-colors"
          >
            Revoke
          </button>
        )}
      </div>
    </div>
  );
}

// ============================================
// 3. Lock Activity Timeline
// ============================================

export function LockActivityTimeline({ lockId, propertyId }: { lockId: number; propertyId: number }) {
  const [activities, setActivities] = useState<LockActivity[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    fetchActivities();
  }, [lockId]);

  const fetchActivities = async () => {
    setIsLoading(true);
    try {
      const response = await fetch(
        `/api/properties/${propertyId}/smart-locks/${lockId}/activities?per_page=20`,
        {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
          },
        }
      );
      const data = await response.json();
      setActivities(data.data.data || []);
    } catch (error) {
      console.error('Failed to fetch activities:', error);
    } finally {
      setIsLoading(false);
    }
  };

  if (isLoading) {
    return <div className="text-center py-8">Loading activity...</div>;
  }

  return (
    <div className="bg-white rounded-lg shadow-md p-6">
      <h3 className="text-xl font-semibold flex items-center mb-6">
        <Activity className="w-5 h-5 mr-2" />
        Recent Activity
      </h3>

      <div className="space-y-4">
        {activities.length === 0 ? (
          <p className="text-center text-gray-500 py-8">No activity recorded</p>
        ) : (
          activities.map((activity) => (
            <ActivityItem key={activity.id} activity={activity} />
          ))
        )}
      </div>
    </div>
  );
}

function ActivityItem({ activity }: { activity: LockActivity }) {
  const getEventIcon = () => {
    switch (activity.event_type) {
      case 'unlock':
        return <Unlock className="w-5 h-5 text-green-600" />;
      case 'lock':
        return <Lock className="w-5 h-5 text-red-600" />;
      case 'code_created':
        return <Key className="w-5 h-5 text-blue-600" />;
      case 'battery_low':
        return <Battery className="w-5 h-5 text-yellow-600" />;
      case 'error':
        return <AlertCircle className="w-5 h-5 text-red-600" />;
      default:
        return <Activity className="w-5 h-5 text-gray-600" />;
    }
  };

  return (
    <div className="flex items-start space-x-4 p-4 border-l-4 border-gray-200 hover:bg-gray-50">
      <div className="flex-shrink-0 mt-1">
        {getEventIcon()}
      </div>
      
      <div className="flex-1">
        <div className="flex items-center justify-between mb-1">
          <span className="font-medium text-gray-900 capitalize">
            {activity.event_type.replace('_', ' ')}
          </span>
          <span className="text-sm text-gray-500">
            {new Date(activity.event_at).toLocaleString()}
          </span>
        </div>
        
        {activity.description && (
          <p className="text-sm text-gray-600">{activity.description}</p>
        )}
        
        {activity.user && (
          <p className="text-xs text-gray-500 mt-1">by {activity.user.name}</p>
        )}
        
        {activity.code_used && (
          <p className="text-xs text-gray-500 mt-1">Code: ••••{activity.code_used.slice(-2)}</p>
        )}
      </div>
    </div>
  );
}

// ============================================
// 4. Guest Access Code Display
// ============================================

export function GuestAccessCodeCard({ bookingId }: { bookingId: number }) {
  const [accessCode, setAccessCode] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [showCode, setShowCode] = useState(false);

  useEffect(() => {
    fetchAccessCode();
  }, [bookingId]);

  const fetchAccessCode = async () => {
    setIsLoading(true);
    try {
      const response = await fetch(`/api/bookings/${bookingId}/access-code`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const data = await response.json();
      if (data.success) {
        setAccessCode(data.data);
      }
    } catch (error) {
      console.error('Failed to fetch access code:', error);
    } finally {
      setIsLoading(false);
    }
  };

  if (isLoading) {
    return <div className="text-center py-8">Loading access details...</div>;
  }

  if (!accessCode) {
    return null;
  }

  return (
    <div className="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
      <div className="flex items-center mb-4">
        <Key className="w-6 h-6 mr-2" />
        <h3 className="text-xl font-semibold">Your Access Code</h3>
      </div>

      {/* Property & Lock Info */}
      <div className="bg-white/10 rounded-lg p-4 mb-4">
        <p className="text-sm opacity-90 mb-1">Property: {accessCode.booking?.property?.title}</p>
        <p className="text-sm opacity-90">Lock: {accessCode.smart_lock?.name}</p>
        <p className="text-xs opacity-75 mt-2">{accessCode.smart_lock?.location}</p>
      </div>

      {/* Access Code */}
      <div className="bg-white rounded-lg p-6 mb-4">
        <div className="text-center">
          <button
            onClick={() => setShowCode(!showCode)}
            className="font-mono text-4xl font-bold tracking-widest text-gray-900"
          >
            {showCode ? accessCode.code : '••••••'}
          </button>
          <p className="text-xs text-gray-600 mt-2">
            {showCode ? 'Tap to hide' : 'Tap to reveal code'}
          </p>
        </div>
      </div>

      {/* Valid Period */}
      <div className="text-sm space-y-1">
        <p>✓ Valid from: {new Date(accessCode.valid_from).toLocaleString()}</p>
        <p>✓ Valid until: {new Date(accessCode.valid_until).toLocaleString()}</p>
      </div>

      {/* Instructions */}
      <div className="mt-4 pt-4 border-t border-white/20">
        <p className="text-xs opacity-90">
          🔒 Keep this code secure. Do not share with anyone.
        </p>
      </div>
    </div>
  );
}

// ============================================
// 5. Create Access Code Modal
// ============================================

export function CreateAccessCodeModal({
  lockId,
  propertyId,
  isOpen,
  onClose,
  onSuccess,
}: {
  lockId: number;
  propertyId: number;
  isOpen: boolean;
  onClose: () => void;
  onSuccess: () => void;
}) {
  const [formData, setFormData] = useState({
    type: 'temporary',
    valid_from: '',
    valid_until: '',
    notes: '',
  });
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);

    try {
      const response = await fetch(
        `/api/properties/${propertyId}/smart-locks/${lockId}/access-codes`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
          },
          body: JSON.stringify(formData),
        }
      );

      if (response.ok) {
        onSuccess();
        onClose();
      }
    } catch (error) {
      console.error('Failed to create code:', error);
    } finally {
      setIsSubmitting(false);
    }
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 className="text-xl font-semibold mb-4">Create Access Code</h3>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Type
            </label>
            <select
              value={formData.type}
              onChange={(e) => setFormData({ ...formData, type: e.target.value })}
              className="w-full border border-gray-300 rounded-md px-3 py-2"
            >
              <option value="temporary">Temporary</option>
              <option value="permanent">Permanent</option>
              <option value="one_time">One Time</option>
            </select>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Valid From
            </label>
            <input
              type="datetime-local"
              value={formData.valid_from}
              onChange={(e) => setFormData({ ...formData, valid_from: e.target.value })}
              className="w-full border border-gray-300 rounded-md px-3 py-2"
              required
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Valid Until
            </label>
            <input
              type="datetime-local"
              value={formData.valid_until}
              onChange={(e) => setFormData({ ...formData, valid_until: e.target.value })}
              className="w-full border border-gray-300 rounded-md px-3 py-2"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">
              Notes (Optional)
            </label>
            <textarea
              value={formData.notes}
              onChange={(e) => setFormData({ ...formData, notes: e.target.value })}
              className="w-full border border-gray-300 rounded-md px-3 py-2"
              rows={3}
            />
          </div>

          <div className="flex space-x-3 pt-4">
            <button
              type="button"
              onClick={onClose}
              className="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={isSubmitting}
              className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              {isSubmitting ? 'Creating...' : 'Create Code'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

// ============================================
// 6. Complete Owner Dashboard Page Example
// ============================================

export function SmartLocksOwnerDashboard({ propertyId }: { propertyId: number }) {
  const [locks, setLocks] = useState<SmartLock[]>([]);
  const [selectedLock, setSelectedLock] = useState<SmartLock | null>(null);
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);

  useEffect(() => {
    fetchLocks();
  }, [propertyId]);

  const fetchLocks = async () => {
    try {
      const response = await fetch(`/api/properties/${propertyId}/smart-locks`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
      });
      const data = await response.json();
      setLocks(data.data || []);
      if (data.data?.length > 0) {
        setSelectedLock(data.data[0]);
      }
    } catch (error) {
      console.error('Failed to fetch locks:', error);
    }
  };

  return (
    <div className="container mx-auto px-4 py-8">
      <h1 className="text-3xl font-bold mb-8">Smart Locks Management</h1>

      {/* Lock Cards Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        {locks.map((lock) => (
          <div key={lock.id} onClick={() => setSelectedLock(lock)}>
            <SmartLockCard lock={lock} />
          </div>
        ))}
      </div>

      {/* Selected Lock Details */}
      {selectedLock && (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <AccessCodeList lockId={selectedLock.id} propertyId={propertyId} />
          <LockActivityTimeline lockId={selectedLock.id} propertyId={propertyId} />
        </div>
      )}

      {/* Create Code Modal */}
      {selectedLock && (
        <CreateAccessCodeModal
          lockId={selectedLock.id}
          propertyId={propertyId}
          isOpen={isCreateModalOpen}
          onClose={() => setIsCreateModalOpen(false)}
          onSuccess={fetchLocks}
        />
      )}
    </div>
  );
}
