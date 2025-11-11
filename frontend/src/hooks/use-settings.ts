import { useQuery } from '@tanstack/react-query';
import { apiClient } from '@/lib/api-client';

/**
 * Hook pentru preluarea setărilor aplicației din backend
 * Sincronizează automat cu /api/v1/settings/public
 */

export interface AppSettings {
  site: {
    name: string;
    description: string;
    logo?: string;
    favicon?: string;
  };
  api: {
    url: string;
    version: string;
  };
  reverb: {
    host: string;
    port: number;
    key: string;
    app_id: string;
  };
  features: {
    registration_enabled: boolean;
    social_login_enabled: boolean;
    two_factor_enabled: boolean;
    guest_checkout_enabled: boolean;
    reviews_enabled: boolean;
    messaging_enabled: boolean;
  };
  payment: {
    stripe_enabled: boolean;
    currency: string;
  };
  maps: {
    provider: string;
    api_key?: string;
    default_center: {
      lat: number;
      lng: number;
    };
  };
  analytics: {
    google_analytics_id?: string;
    facebook_pixel_id?: string;
  };
  company: {
    name: string;
    email: string;
    phone?: string;
    address?: string;
  };
  seo: {
    meta_title?: string;
    meta_description?: string;
    meta_keywords?: string;
  };
}

/**
 * Hook pentru accesarea setărilor aplicației
 * Cachează rezultatele pentru 5 minute
 * 
 * @example
 * ```tsx
 * function MyComponent() {
 *   const { data: settings, isLoading, error } = useSettings();
 *   
 *   if (isLoading) return <div>Loading...</div>;
 *   if (error) return <div>Error loading settings</div>;
 *   
 *   return <div>{settings.site.name}</div>;
 * }
 * ```
 */
export function useSettings() {
  return useQuery<AppSettings>({
    queryKey: ['app-settings'],
    queryFn: async () => {
      const { data } = await apiClient.get('/settings/public');
      return data;
    },
    staleTime: 5 * 60 * 1000, // 5 minutes
    gcTime: 10 * 60 * 1000, // 10 minutes (formerly cacheTime)
    retry: 3,
    refetchOnWindowFocus: false,
  });
}

/**
 * Hook pentru o setare specifică
 * 
 * @example
 * ```tsx
 * const siteName = useSetting('site.name', 'RentHub');
 * ```
 */
export function useSetting<T = any>(path: string, defaultValue?: T): T | undefined {
  const { data: settings } = useSettings();
  
  if (!settings) return defaultValue;
  
  // Navighează prin path (e.g., "site.name" => settings.site.name)
  const keys = path.split('.');
  let value: any = settings;
  
  for (const key of keys) {
    if (value && typeof value === 'object' && key in value) {
      value = value[key];
    } else {
      return defaultValue;
    }
  }
  
  return value ?? defaultValue;
}
