import axios, { AxiosError, AxiosInstance, AxiosRequestConfig, InternalAxiosRequestConfig } from 'axios';
import { logger } from './logger';

interface RetryConfig extends AxiosRequestConfig {
  _retry?: boolean;
  _retryCount?: number;
}

const MAX_RETRIES = 3;
const RETRY_DELAY = 1000; // 1 second

const apiClient = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 30000,
});

// Request interceptor to add auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor to handle errors and retries
apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const config = error.config as RetryConfig;

    // Handle 401 errors (authentication)
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      
      // Don't redirect if already on login page
      if (!window.location.pathname.includes('/auth/login')) {
        window.location.href = '/auth/login';
      }
      return Promise.reject(error);
    }

    // Retry logic for network errors and 5xx errors
    const shouldRetry = 
      !config._retry && 
      (
        !error.response || // Network error
        error.response.status >= 500 // Server error
      ) &&
      (config._retryCount || 0) < MAX_RETRIES;

    if (shouldRetry) {
      config._retry = true;
      config._retryCount = (config._retryCount || 0) + 1;

      // Wait before retrying
      await new Promise(resolve => 
        setTimeout(resolve, RETRY_DELAY * config._retryCount!)
      );

      logger.debug(`Retrying request`, { 
        attempt: config._retryCount, 
        maxRetries: MAX_RETRIES, 
        url: config.url 
      });

      return apiClient(config);
    }

    // Enhance error message
    if (error.response) {
      // Server responded with error status
      const responseData = error.response.data as any;
      const serverMessage = responseData?.message || responseData?.error;
      if (serverMessage) {
        error.message = serverMessage;
      }
    } else if (error.request) {
      // Request was made but no response
      error.message = 'Network error: Unable to reach the server. Please check your connection.';
    } else {
      // Something else happened
      error.message = error.message || 'An unexpected error occurred';
    }

    return Promise.reject(error);
  }
);

export default apiClient;

// Helper function for manual retry
export async function withRetry<T>(
  fn: () => Promise<T>,
  maxRetries: number = MAX_RETRIES,
  delay: number = RETRY_DELAY
): Promise<T> {
  let lastError: Error;
  
  for (let i = 0; i < maxRetries; i++) {
    try {
      return await fn();
    } catch (error) {
      lastError = error as Error;
      
      if (i < maxRetries - 1) {
        await new Promise(resolve => setTimeout(resolve, delay * (i + 1)));
        logger.debug(`Manual retry attempt`, { attempt: i + 1, maxRetries });
      }
    }
  }
  
  throw lastError!;
}
