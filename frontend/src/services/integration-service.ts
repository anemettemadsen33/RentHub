import apiClient, { ensureCsrfCookie } from '@/lib/api-client';

export interface Integration {
  id: string;
  name: string;
  type: 'airbnb' | 'booking' | 'vrbo' | 'google_calendar' | 'stripe';
  status: 'connected' | 'disconnected' | 'error' | 'pending';
  settings: Record<string, any>;
  last_sync_at?: string;
  created_at: string;
  updated_at: string;
}

export interface IntegrationConnection {
  platform: string;
  client_id?: string;
  client_secret?: string;
  access_token?: string;
  refresh_token?: string;
  expires_at?: string;
  scope?: string[];
}

export interface SyncResult {
  success: boolean;
  message: string;
  properties_synced: number;
  bookings_synced: number;
  errors: string[];
}

class IntegrationService {
  private baseUrl = '/integrations';

  async getIntegrations(): Promise<Integration[]> {
    try {
      const res = await apiClient.get(`${this.baseUrl}`);
      return res.data?.data ?? [];
    } catch (error) {
      throw new Error('Failed to load integrations');
    }
  }

  async getIntegration(id: string): Promise<Integration> {
    try {
      const res = await apiClient.get(`${this.baseUrl}/${id}`);
      return res.data?.data as Integration;
    } catch (error) {
      throw new Error('Failed to load integration');
    }
  }

  async connectIntegration(platform: string, authCode: string): Promise<Integration> {
    try {
      await ensureCsrfCookie();
      const res = await apiClient.post(`${this.baseUrl}/connect`, { platform, auth_code: authCode });
      return res.data?.data as Integration;
    } catch (error) {
      throw new Error('Failed to connect integration');
    }
  }

  async disconnectIntegration(id: string): Promise<void> {
    try {
      await ensureCsrfCookie();
      await apiClient.delete(`${this.baseUrl}/${id}`);
    } catch (error) {
      throw new Error('Failed to disconnect integration');
    }
  }

  async syncIntegration(id: string): Promise<SyncResult> {
    try {
      await ensureCsrfCookie();
      const res = await apiClient.post(`${this.baseUrl}/${id}/sync`);
      return res.data?.data as SyncResult;
    } catch (error) {
      throw new Error('Failed to sync integration');
    }
  }

  async getOAuthUrl(platform: string): Promise<string> {
    try {
      const res = await apiClient.get(`${this.baseUrl}/oauth-url?platform=${encodeURIComponent(platform)}`);
      return res.data?.data?.url as string;
    } catch (error) {
      throw new Error('Failed to get OAuth URL');
    }
  }

  async updateIntegrationSettings(id: string, settings: Record<string, any>): Promise<Integration> {
    try {
      await ensureCsrfCookie();
      const res = await apiClient.put(`${this.baseUrl}/${id}/settings`, { settings });
      return res.data?.data as Integration;
    } catch (error) {
      throw new Error('Failed to update integration settings');
    }
  }

  async getSyncHistory(id: string): Promise<any[]> {
    try {
      const res = await apiClient.get(`${this.baseUrl}/${id}/sync-history`);
      return res.data?.data ?? [];
    } catch (error) {
      throw new Error('Failed to load sync history');
    }
  }
}

export const integrationService = new IntegrationService();
