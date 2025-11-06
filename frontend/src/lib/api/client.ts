import axios from 'axios';

// Normalize API URL to prevent double slashes
const normalizeUrl = (url: string | undefined): string => {
  if (!url) return 'http://localhost:8000';
  return url.replace(/\/+$/, ''); // Remove trailing slashes
};

export const apiClient = axios.create({
  baseURL: normalizeUrl(process.env.NEXT_PUBLIC_API_URL) + '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
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

// Response interceptor to handle errors
apiClient.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response?.status === 401) {
      // Unauthorized - clear token and redirect to login
      localStorage.removeItem('auth_token');
      if (typeof window !== 'undefined' && !window.location.pathname.includes('/auth')) {
        window.location.href = '/auth/login';
      }
    }
    return Promise.reject(error);
  }
);
