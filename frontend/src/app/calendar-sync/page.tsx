'use client';

import { useEffect, useState, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/auth-context';
import { useToast } from '@/hooks/use-toast';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import {
  Calendar,
  Link2,
  RefreshCw,
  Trash2,
  CheckCircle,
  XCircle,
  AlertCircle,
  ExternalLink,
  Plus,
} from 'lucide-react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import apiClient from '@/lib/api-client';

interface CalendarSync {
  id: number;
  property_id: number;
  platform: 'airbnb' | 'booking_com' | 'vrbo' | 'ical';
  calendar_url: string | null;
  sync_enabled: boolean;
  last_sync: string | null;
  sync_status: 'success' | 'failed' | 'pending';
  error_message: string | null;
  auto_block: boolean;
  import_bookings: boolean;
  export_bookings: boolean;
  property: {
    id: number;
    title: string;
    address: string;
  };
}

interface Property {
  id: number;
  title: string;
  address: string;
}

export default function CalendarSyncPage() {
  const router = useRouter();
  const { user } = useAuth();
  const { toast } = useToast();
  const [syncs, setSyncs] = useState<CalendarSync[]>([]);
  const [properties, setProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddDialog, setShowAddDialog] = useState(false);
  const [syncing, setSyncing] = useState<number | null>(null);

  // Form state
  const [newSync, setNewSync] = useState({
    property_id: '',
    platform: '',
    calendar_url: '',
    auto_block: true,
    import_bookings: true,
    export_bookings: true,
  });

  const fetchSyncs = useCallback(async () => {
    try {
      const { data } = await apiClient.get('/calendar-syncs');
      setSyncs(data.data || []);
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to load calendar syncs',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  }, [toast]);

  const fetchProperties = useCallback(async () => {
    try {
      const { data } = await apiClient.get('/properties');
      setProperties(data.data || []);
    } catch (error) {
      console.error('Failed to fetch properties:', error);
    }
  }, []);

  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }
    fetchSyncs();
    fetchProperties();
  }, [user, router, fetchSyncs, fetchProperties]);

  const handleAddSync = useCallback(async () => {
    if (!newSync.property_id || !newSync.platform) {
      toast({
        title: 'Error',
        description: 'Please select a property and platform',
        variant: 'destructive',
      });
      return;
    }

    if (newSync.platform === 'ical' && !newSync.calendar_url) {
      toast({
        title: 'Error',
        description: 'Please enter an iCal URL',
        variant: 'destructive',
      });
      return;
    }

    try {
      await apiClient.post('/calendar-syncs', newSync);
      toast({
        title: 'Success',
        description: 'Calendar sync added successfully',
      });
      setShowAddDialog(false);
      setNewSync({
        property_id: '',
        platform: '',
        calendar_url: '',
        auto_block: true,
        import_bookings: true,
        export_bookings: true,
      });
      fetchSyncs();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to add calendar sync',
        variant: 'destructive',
      });
    }
  }, [newSync, toast, fetchSyncs]);

  const handleSyncNow = useCallback(async (syncId: number) => {
    setSyncing(syncId);
    try {
      await apiClient.post(`/calendar-syncs/${syncId}/sync`);
      toast({
        title: 'Success',
        description: 'Calendar synced successfully',
      });
      fetchSyncs();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to sync calendar',
        variant: 'destructive',
      });
    } finally {
      setSyncing(null);
    }
  }, [toast, fetchSyncs]);

  const handleToggleSync = useCallback(async (syncId: number, enabled: boolean) => {
    try {
      await apiClient.patch(`/calendar-syncs/${syncId}`, { sync_enabled: enabled });
      toast({
        title: 'Success',
        description: `Calendar sync ${enabled ? 'enabled' : 'disabled'}`,
      });
      fetchSyncs();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to update sync status',
        variant: 'destructive',
      });
    }
  }, [toast, fetchSyncs]);

  const handleDeleteSync = useCallback(async (syncId: number) => {
    if (!confirm('Are you sure you want to delete this calendar sync?')) {
      return;
    }

    try {
      await apiClient.delete(`/calendar-syncs/${syncId}`);
      toast({
        title: 'Success',
        description: 'Calendar sync deleted successfully',
      });
      fetchSyncs();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to delete calendar sync',
        variant: 'destructive',
      });
    }
  }, [toast, fetchSyncs]);

  const getPlatformName = useCallback((platform: string) => {
    switch (platform) {
      case 'airbnb':
        return 'Airbnb';
      case 'booking_com':
        return 'Booking.com';
      case 'vrbo':
        return 'VRBO';
      case 'ical':
        return 'iCal';
      default:
        return platform;
    }
  }, []);

  const getSyncStatusColor = useCallback((status: string) => {
    switch (status) {
      case 'success':
        return 'bg-green-100 text-green-800';
      case 'failed':
        return 'bg-red-100 text-red-800';
      case 'pending':
        return 'bg-yellow-100 text-yellow-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  }, []);

  const getSyncStatusIcon = useCallback((status: string) => {
    switch (status) {
      case 'success':
        return <CheckCircle className="h-4 w-4" />;
      case 'failed':
        return <XCircle className="h-4 w-4" />;
      case 'pending':
        return <AlertCircle className="h-4 w-4" />;
      default:
        return null;
    }
  }, []);

  if (!user) {
    return null;
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        <div className="mb-6 flex items-center justify-between">
          <div>
            <h1 className="text-3xl font-bold mb-2 flex items-center">
              <Calendar className="h-8 w-8 mr-3 text-primary" />
              Calendar Synchronization
            </h1>
            <p className="text-gray-600">
              Connect your calendars from Airbnb, Booking.com, and other platforms to avoid double bookings
            </p>
          </div>
          <Button onClick={() => setShowAddDialog(true)}>
            <Plus className="h-4 w-4 mr-2" />
            Add Calendar
          </Button>
        </div>

        {/* Info Banner */}
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
          <h3 className="font-semibold text-blue-900 mb-2">How Calendar Sync Works</h3>
          <ul className="text-sm text-blue-900 space-y-1 list-disc list-inside">
            <li>Import bookings from other platforms to block dates automatically</li>
            <li>Export your RentHub bookings to other platforms</li>
            <li>Sync every 4 hours to keep calendars up to date</li>
            <li>Manual sync available anytime</li>
          </ul>
        </div>

        {/* Calendar Syncs List */}
        {loading ? (
          <div className="text-center py-12">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
            <p className="text-gray-600 mt-4">Loading calendar syncs...</p>
          </div>
        ) : syncs.length === 0 ? (
          <Card>
            <CardContent className="text-center py-12">
              <Calendar className="h-16 w-16 text-gray-300 mx-auto mb-4" />
              <h3 className="text-lg font-semibold mb-2">No Calendar Syncs</h3>
              <p className="text-gray-600 mb-4">
                Connect your external calendars to synchronize availability
              </p>
              <Button onClick={() => setShowAddDialog(true)}>
                <Plus className="h-4 w-4 mr-2" />
                Add Your First Calendar
              </Button>
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {syncs.map((sync) => (
              <Card key={sync.id} className="hover:shadow-md transition-shadow">
                <CardContent className="p-6">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <div className="flex items-center space-x-3 mb-3">
                        <Link2 className="h-6 w-6 text-primary" />
                        <div>
                          <h3 className="text-lg font-semibold">
                            {getPlatformName(sync.platform)}
                          </h3>
                          <p className="text-sm text-gray-600">{sync.property.title}</p>
                        </div>
                        <Badge className={getSyncStatusColor(sync.sync_status)}>
                          {getSyncStatusIcon(sync.sync_status)}
                          <span className="ml-1">{sync.sync_status.toUpperCase()}</span>
                        </Badge>
                      </div>

                      {sync.calendar_url && (
                        <div className="mb-3 p-3 bg-gray-50 rounded-lg">
                          <p className="text-xs text-gray-600 mb-1">Calendar URL</p>
                          <p className="text-sm font-mono break-all">{sync.calendar_url}</p>
                        </div>
                      )}

                      <div className="grid grid-cols-3 gap-4 mb-3">
                        <div className="flex items-center space-x-2">
                          <Switch
                            checked={sync.auto_block}
                            disabled
                          />
                          <span className="text-sm">Auto Block Dates</span>
                        </div>
                        <div className="flex items-center space-x-2">
                          <Switch
                            checked={sync.import_bookings}
                            disabled
                          />
                          <span className="text-sm">Import Bookings</span>
                        </div>
                        <div className="flex items-center space-x-2">
                          <Switch
                            checked={sync.export_bookings}
                            disabled
                          />
                          <span className="text-sm">Export Bookings</span>
                        </div>
                      </div>

                      {sync.last_sync && (
                        <p className="text-xs text-gray-600">
                          Last synced: {new Date(sync.last_sync).toLocaleString()}
                        </p>
                      )}

                      {sync.error_message && (
                        <div className="mt-3 bg-red-50 border border-red-200 rounded-lg p-3">
                          <p className="text-sm text-red-900">
                            <strong>Error:</strong> {sync.error_message}
                          </p>
                        </div>
                      )}
                    </div>

                    <div className="flex flex-col space-y-2 ml-4">
                      <div className="flex items-center space-x-2">
                        <Switch
                          checked={sync.sync_enabled}
                          onCheckedChange={(enabled) => handleToggleSync(sync.id, enabled)}
                        />
                        <span className="text-sm font-medium">
                          {sync.sync_enabled ? 'Enabled' : 'Disabled'}
                        </span>
                      </div>
                      <Button
                        size="sm"
                        variant="outline"
                        onClick={() => handleSyncNow(sync.id)}
                        disabled={syncing === sync.id || !sync.sync_enabled}
                      >
                        <RefreshCw className={`h-4 w-4 mr-2 ${syncing === sync.id ? 'animate-spin' : ''}`} />
                        {syncing === sync.id ? 'Syncing...' : 'Sync Now'}
                      </Button>
                      <Button
                        size="sm"
                        variant="destructive"
                        onClick={() => handleDeleteSync(sync.id)}
                      >
                        <Trash2 className="h-4 w-4 mr-2" />
                        Delete
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}

        {/* Add Calendar Dialog */}
        <Dialog open={showAddDialog} onOpenChange={setShowAddDialog}>
          <DialogContent className="max-w-2xl">
            <DialogHeader>
              <DialogTitle>Add Calendar Sync</DialogTitle>
              <DialogDescription>
                Connect an external calendar to synchronize availability
              </DialogDescription>
            </DialogHeader>

            <div className="space-y-4">
              <div>
                <Label htmlFor="property">Property</Label>
                <Select
                  value={newSync.property_id}
                  onValueChange={(value) => setNewSync({ ...newSync, property_id: value })}
                >
                  <SelectTrigger id="property">
                    <SelectValue placeholder="Select a property" />
                  </SelectTrigger>
                  <SelectContent>
                    {properties.map((property) => (
                      <SelectItem key={property.id} value={property.id.toString()}>
                        {property.title}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div>
                <Label htmlFor="platform">Platform</Label>
                <Select
                  value={newSync.platform}
                  onValueChange={(value) => setNewSync({ ...newSync, platform: value })}
                >
                  <SelectTrigger id="platform">
                    <SelectValue placeholder="Select a platform" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="airbnb">Airbnb</SelectItem>
                    <SelectItem value="booking_com">Booking.com</SelectItem>
                    <SelectItem value="vrbo">VRBO</SelectItem>
                    <SelectItem value="ical">iCal / Other</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              {newSync.platform === 'ical' && (
                <div>
                  <Label htmlFor="calendar_url">iCal URL</Label>
                  <Input
                    id="calendar_url"
                    type="url"
                    placeholder="https://..."
                    value={newSync.calendar_url}
                    onChange={(e) => setNewSync({ ...newSync, calendar_url: e.target.value })}
                  />
                  <p className="text-xs text-gray-600 mt-1">
                    Enter the iCal feed URL from your calendar provider
                  </p>
                </div>
              )}

              <div className="space-y-3">
                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="auto_block">Auto Block Dates</Label>
                    <p className="text-xs text-gray-600">
                      Automatically block dates when bookings are imported
                    </p>
                  </div>
                  <Switch
                    id="auto_block"
                    checked={newSync.auto_block}
                    onCheckedChange={(checked) => setNewSync({ ...newSync, auto_block: checked })}
                  />
                </div>

                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="import_bookings">Import Bookings</Label>
                    <p className="text-xs text-gray-600">
                      Import bookings from this calendar
                    </p>
                  </div>
                  <Switch
                    id="import_bookings"
                    checked={newSync.import_bookings}
                    onCheckedChange={(checked) => setNewSync({ ...newSync, import_bookings: checked })}
                  />
                </div>

                <div className="flex items-center justify-between">
                  <div>
                    <Label htmlFor="export_bookings">Export Bookings</Label>
                    <p className="text-xs text-gray-600">
                      Export RentHub bookings to this calendar
                    </p>
                  </div>
                  <Switch
                    id="export_bookings"
                    checked={newSync.export_bookings}
                    onCheckedChange={(checked) => setNewSync({ ...newSync, export_bookings: checked })}
                  />
                </div>
              </div>

              {newSync.platform && newSync.platform !== 'ical' && (
                <div className="bg-blue-50 border border-blue-200 rounded-lg p-3">
                  <p className="text-sm text-blue-900">
                    <strong>Note:</strong> You&apos;ll need to authorize RentHub to access your{' '}
                    {getPlatformName(newSync.platform)} calendar after clicking Add.
                  </p>
                </div>
              )}

              <div className="flex space-x-2 justify-end">
                <Button variant="outline" onClick={() => setShowAddDialog(false)}>
                  Cancel
                </Button>
                <Button onClick={handleAddSync}>Add Calendar Sync</Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </MainLayout>
  );
}
