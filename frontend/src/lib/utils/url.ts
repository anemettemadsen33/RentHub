/**
 * Normalize API URL to prevent double slashes
 * Removes trailing slashes from URLs and provides a default fallback
 * 
 * @param url - The URL to normalize (typically from environment variable)
 * @returns Normalized URL without trailing slashes
 */
export const normalizeApiUrl = (url: string | undefined): string => {
  if (!url) return 'http://localhost:8000';
  return url.replace(/\/+$/, ''); // Remove trailing slashes
};
