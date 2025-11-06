import axios from 'axios';

// Normalize API URL to prevent double slashes
const normalizeUrl = (url: string | undefined): string => {
  if (!url) return 'http://localhost:8000';
  return url.replace(/\/+$/, ''); // Remove trailing slashes
};

export const apiClient = axios.create({
  baseURL: (normalizeUrl(process.env.NEXT_PUBLIC_API_URL) || 'http://localhost') + '/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add auth token interceptor
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle response errors
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Redirect to login or refresh token
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);
