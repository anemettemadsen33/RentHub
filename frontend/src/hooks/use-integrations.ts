import { useState, useEffect, useCallback } from 'react';
import { integrationService, Integration, SyncResult } from '@/services/integration-service';
import { toast } from 'sonner';

export function useIntegrations() {
  const [integrations, setIntegrations] = useState<Integration[]>([]);
  const [loading, setLoading] = useState(true);
  const [syncing, setSyncing] = useState<Record<string, boolean>>({});

  const fetchIntegrations = useCallback(async () => {
    try {
      setLoading(true);
      const data = await integrationService.getIntegrations();
      setIntegrations(data);
    } catch (error) {
      console.error('Failed to fetch integrations:', error);
      toast.error('Failed to load integrations');
    } finally {
      setLoading(false);
    }
  }, []);

  const connectIntegration = useCallback(async (platform: string) => {
    try {
      const oauthUrl = await integrationService.getOAuthUrl(platform);
      window.location.href = oauthUrl;
    } catch (error) {
      console.error('Failed to get OAuth URL:', error);
      toast.error('Failed to connect integration');
    }
  }, []);

  const disconnectIntegration = useCallback(async (id: string) => {
    try {
      await integrationService.disconnectIntegration(id);
      setIntegrations(prev => prev.filter(integration => integration.id !== id));
      toast.success('Integration disconnected successfully');
    } catch (error) {
      console.error('Failed to disconnect integration:', error);
      toast.error('Failed to disconnect integration');
    }
  }, []);

  const syncIntegration = useCallback(async (id: string) => {
    try {
      setSyncing(prev => ({ ...prev, [id]: true }));
      const result: SyncResult = await integrationService.syncIntegration(id);
      
      if (result.success) {
        toast.success(`Sync completed: ${result.properties_synced} properties, ${result.bookings_synced} bookings`);
      } else {
        toast.error(`Sync failed: ${result.message}`);
      }
      
      return result;
    } catch (error) {
      console.error('Failed to sync integration:', error);
      toast.error('Failed to sync integration');
      return { success: false, message: 'Sync failed', properties_synced: 0, bookings_synced: 0, errors: [] };
    } finally {
      setSyncing(prev => ({ ...prev, [id]: false }));
    }
  }, []);

  const updateIntegrationSettings = useCallback(async (id: string, settings: Record<string, any>) => {
    try {
      const updated = await integrationService.updateIntegrationSettings(id, settings);
      setIntegrations(prev => prev.map(integration => 
        integration.id === id ? updated : integration
      ));
      toast.success('Settings updated successfully');
    } catch (error) {
      console.error('Failed to update settings:', error);
      toast.error('Failed to update settings');
    }
  }, []);

  useEffect(() => {
    fetchIntegrations();
  }, [fetchIntegrations]);

  const getIntegrationByType = useCallback((type: string) => {
    return integrations.find(integration => integration.type === type);
  }, [integrations]);

  const isConnected = useCallback((type: string) => {
    const integration = getIntegrationByType(type);
    return integration?.status === 'connected';
  }, [getIntegrationByType]);

  return {
    integrations,
    loading,
    syncing,
    fetchIntegrations,
    connectIntegration,
    disconnectIntegration,
    syncIntegration,
    updateIntegrationSettings,
    getIntegrationByType,
    isConnected,
  };
}