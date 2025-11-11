// Utilities to manage runtime map provider preference (leaflet | mapbox)
// Persisted in localStorage and broadcast via CustomEvent + storage events.

export type MapProvider = 'leaflet' | 'mapbox';

const STORAGE_KEY = 'mapProvider';

export function resolveEnvDefault(): MapProvider {
  const envProvider = process.env.NEXT_PUBLIC_MAP_PROVIDER as MapProvider | undefined;
  const hasMapboxToken = !!process.env.NEXT_PUBLIC_MAPBOX_TOKEN;
  if (envProvider === 'leaflet' || envProvider === 'mapbox') return envProvider;
  return hasMapboxToken ? 'mapbox' : 'leaflet';
}

export function getPreferredMapProvider(fallback?: MapProvider): MapProvider {
  // On the server, just return fallback/env default
  if (typeof window === 'undefined') return fallback ?? resolveEnvDefault();
  try {
    const raw = window.localStorage.getItem(STORAGE_KEY);
    if (raw === 'leaflet' || raw === 'mapbox') return raw;
  } catch {
    // ignore storage errors (Safari private mode, etc.)
  }
  return fallback ?? resolveEnvDefault();
}

export function setPreferredMapProvider(provider: MapProvider) {
  if (typeof window === 'undefined') return;
  try {
    window.localStorage.setItem(STORAGE_KEY, provider);
  } catch {
    // ignore
  }
  // Notify same-tab listeners
  window.dispatchEvent(new CustomEvent('map-provider-change', { detail: provider }));
}

export function subscribeToMapProviderChanges(callback: (p: MapProvider) => void) {
  if (typeof window === 'undefined') return () => {};

  const onCustom = (e: Event) => {
    const ce = e as CustomEvent<MapProvider>;
    const detail = (ce as any).detail as MapProvider | undefined;
    if (detail === 'leaflet' || detail === 'mapbox') callback(detail);
  };
  const onStorage = (e: StorageEvent) => {
    if (e.key === STORAGE_KEY && (e.newValue === 'leaflet' || e.newValue === 'mapbox')) {
      callback(e.newValue);
    }
  };

  window.addEventListener('map-provider-change', onCustom as EventListener);
  window.addEventListener('storage', onStorage);
  return () => {
    window.removeEventListener('map-provider-change', onCustom as EventListener);
    window.removeEventListener('storage', onStorage);
  };
}
