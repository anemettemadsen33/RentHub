import axios, { AxiosError } from 'axios';
import { z } from 'zod';
import { AuthUserSchema, DashboardStatsSchema, type DashboardStats, ComparisonPropertySchema, type ComparisonProperty, ApiErrorSchema, type ApiError } from './schemas';

// Base configuration
const BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000/api/v1';
// Root host (strip trailing /api/v1 for Sanctum CSRF cookie endpoint)
const API_ROOT = BASE_URL.replace(/\/api\/v1$/, '');

export const apiClient = axios.create({
  baseURL: BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  timeout: 30000,
  withCredentials: true, // allow cookies for Sanctum CSRF in SPA mode
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
});

let csrfInitialized = false;
/**
 * Ensure we have the Sanctum CSRF cookie (required because config/sanctum.php includes ValidateCsrfToken).
 * Safe to call multiple times; will only fetch once per page load.
 */
export async function ensureCsrfCookie(): Promise<void> {
  if (csrfInitialized) return;
  try {
    await axios.get(API_ROOT + '/sanctum/csrf-cookie', { withCredentials: true });
    csrfInitialized = true;
    // console.log('[apiClient] CSRF cookie fetched');
  } catch (e) {
    console.warn('[apiClient] Failed to fetch CSRF cookie', e);
  }
}

// ---------- Zod Schemas (extend gradually) ----------
// Schemas moved into ./schemas for modularity

// ---------- Interceptors ----------
apiClient.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    const token = localStorage.getItem('auth_token');
    if (token) {
      (config.headers = config.headers || {}).Authorization = `Bearer ${token}`;
    }
  }
  return config;
});

apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    if (error.response?.status === 401 && typeof window !== 'undefined') {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      // Soft redirect to login preserving original path
      const returnTo = window.location.pathname + window.location.search;
      window.location.href = `/auth/login?returnTo=${encodeURIComponent(returnTo)}`;
    }
    return Promise.reject(error);
  }
);

// ---------- Typed helpers ----------
export async function parse<T>(schema: z.ZodSchema<T>, data: unknown): Promise<T> {
  const result = schema.safeParse(data);
  if (!result.success) {
    throw new Error('Schema validation failed: ' + JSON.stringify(result.error.issues));
  }
  return result.data;
}

export async function getDashboardStats(): Promise<DashboardStats> {
  const res = await apiClient.get('/dashboard/stats');
  const raw = res.data?.data ?? res.data;
  const normalized = {
    properties: Number(raw?.properties ?? raw?.total_properties ?? 0),
    bookingsUpcoming: Number(raw?.bookingsUpcoming ?? raw?.active_bookings ?? 0),
    revenueLast30: Number(raw?.revenueLast30 ?? raw?.total_revenue ?? 0),
    guestsUnique: Number(raw?.guestsUnique ?? 0),
  };
  return parse(DashboardStatsSchema, normalized);
}

export async function getComparisonProperties(): Promise<ComparisonProperty[]> {
  const res = await apiClient.get('/property-comparison');
  if (Array.isArray(res.data?.items)) {
    return res.data.items.map((item: any) => parse(ComparisonPropertySchema, item));
  }
  // Fallback: attempt to parse direct array
  return parse(z.array(ComparisonPropertySchema), res.data);
}

export async function addPropertyToComparison(propertyId: number): Promise<void> {
  await ensureCsrfCookie();
  await apiClient.post('/property-comparison/add', { propertyId });
}

export async function removePropertyFromComparison(propertyId: number): Promise<void> {
  await ensureCsrfCookie();
  await apiClient.delete(`/property-comparison/remove/${propertyId}`);
}

export async function clearComparison(): Promise<void> {
  await ensureCsrfCookie();
  await apiClient.delete('/property-comparison/clear');
}

export function isApiError(e: unknown): e is ApiError {
  if (typeof e !== 'object' || e === null) return false;
  const maybe = e as any;
  return typeof maybe.message === 'string';
}

export function extractApiError(e: unknown): string {
  if (isApiError(e)) return e.message;
  if (e instanceof Error) return e.message;
  return 'Unexpected error';
}

// Future: generic typed request builder or OpenAPI codegen integration

export default apiClient;
