import apiClient from '@/lib/api-client-enhanced';
import { logger } from '@/lib/logger';

export async function logPWAEvent(type: string, payload?: Record<string, any>) {
  try {
    await apiClient.post('/analytics/pwa', { type, payload, timestamp: new Date().toISOString() });
  } catch (e) {
    logger.debug('PWA analytics send failed (will be ignored)', { type });
  }
}
