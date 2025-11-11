import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { getPreferredMapProvider, setPreferredMapProvider, subscribeToMapProviderChanges, resolveEnvDefault } from '@/lib/map-provider';

// JSDOM provides window/localStorage; we simulate interactions

describe('map-provider utilities', () => {
  const originalEnv = { ...process.env };

  beforeEach(() => {
    Object.assign(process.env, { NEXT_PUBLIC_MAP_PROVIDER: '', NEXT_PUBLIC_MAPBOX_TOKEN: '' });
    localStorage.clear();
  });

  afterEach(() => {
    process.env = { ...originalEnv };
  });

  it('resolveEnvDefault defaults to leaflet when no token', () => {
    expect(resolveEnvDefault()).toBe('leaflet');
  });

  it('resolveEnvDefault prefers mapbox when token present', () => {
    process.env.NEXT_PUBLIC_MAPBOX_TOKEN = 'abc123';
    expect(resolveEnvDefault()).toBe('mapbox');
  });

  it('getPreferredMapProvider reads from localStorage', () => {
    localStorage.setItem('mapProvider', 'mapbox');
    expect(getPreferredMapProvider()).toBe('mapbox');
  });

  it('setPreferredMapProvider persists and emits event', () => {
    const spy = vi.fn();
    window.addEventListener('map-provider-change', (e: Event) => {
      const ce = e as CustomEvent;
      spy(ce.detail);
    });
    setPreferredMapProvider('leaflet');
    expect(localStorage.getItem('mapProvider')).toBe('leaflet');
    expect(spy).toHaveBeenCalledWith('leaflet');
  });

  it('subscribeToMapProviderChanges listens to both custom and storage events', () => {
    const spy = vi.fn();
    const unsub = subscribeToMapProviderChanges(spy);
    // custom event
    window.dispatchEvent(new CustomEvent('map-provider-change', { detail: 'mapbox' }));
    expect(spy).toHaveBeenCalledWith('mapbox');
    // storage event
    localStorage.setItem('mapProvider', 'leaflet');
    window.dispatchEvent(new StorageEvent('storage', { key: 'mapProvider', newValue: 'leaflet' }));
    expect(spy).toHaveBeenCalledWith('leaflet');
    unsub();
  });
});
