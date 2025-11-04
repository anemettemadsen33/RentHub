const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL || 'https://renthub.com';

export function getCanonicalUrl(path: string): string {
  // Remove trailing slashes except for root
  const cleanPath = path === '/' ? '/' : path.replace(/\/$/, '');
  
  // Remove query parameters for canonical URL
  const pathWithoutQuery = cleanPath.split('?')[0];
  
  return `${SITE_URL}${pathWithoutQuery}`;
}

export function getAlternateUrls(path: string, locales: string[] = ['en', 'es', 'fr']) {
  return locales.map((locale) => ({
    hrefLang: locale,
    href: `${SITE_URL}/${locale}${path}`,
  }));
}

export function normalizeUrl(url: string): string {
  try {
    const urlObj = new URL(url, SITE_URL);
    // Remove trailing slash
    if (urlObj.pathname !== '/') {
      urlObj.pathname = urlObj.pathname.replace(/\/$/, '');
    }
    // Sort query parameters
    urlObj.searchParams.sort();
    return urlObj.toString();
  } catch {
    return url;
  }
}
