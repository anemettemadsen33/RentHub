/**
 * AMP (Accelerated Mobile Pages) utilities
 * Note: AMP is optional and requires additional setup
 */

export interface AMPConfig {
  enabled: boolean;
  routes: string[];
}

export const AMP_CONFIG: AMPConfig = {
  enabled: process.env.NEXT_PUBLIC_AMP_ENABLED === 'true',
  routes: [
    '/properties/[id]',
    '/properties',
    '/search',
  ],
};

/**
 * Check if current route should have AMP version
 */
export function shouldGenerateAMP(pathname: string): boolean {
  if (!AMP_CONFIG.enabled) return false;
  
  return AMP_CONFIG.routes.some((route) => {
    const pattern = route.replace(/\[.*?\]/g, '[^/]+');
    return new RegExp(`^${pattern}$`).test(pathname);
  });
}

/**
 * Generate AMP canonical link
 */
export function getAMPUrl(url: string): string {
  const ampUrl = new URL(url);
  ampUrl.searchParams.set('amp', '1');
  return ampUrl.toString();
}

/**
 * Generate canonical URL for AMP page
 */
export function getCanonicalFromAMP(url: string): string {
  const canonicalUrl = new URL(url);
  canonicalUrl.searchParams.delete('amp');
  return canonicalUrl.toString();
}

/**
 * AMP-compatible CSS utilities
 */
export const ampStyles = {
  container: `
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 16px;
  `,
  
  card: `
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 16px;
    margin-bottom: 16px;
  `,
  
  button: `
    background: #3b82f6;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 12px 24px;
    font-size: 16px;
    cursor: pointer;
  `,
  
  heading: `
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 16px;
    color: #111827;
  `,
};

/**
 * Convert regular HTML to AMP-compatible
 */
export function convertToAMP(html: string): string {
  return html
    // Replace img with amp-img
    .replace(/<img([^>]*)>/g, (match, attrs) => {
      const width = attrs.match(/width=["']?(\d+)["']?/)?.[1] || '800';
      const height = attrs.match(/height=["']?(\d+)["']?/)?.[1] || '600';
      const src = attrs.match(/src=["']?([^"']+)["']?/)?.[1] || '';
      const alt = attrs.match(/alt=["']?([^"']+)["']?/)?.[1] || '';
      
      return `<amp-img src="${src}" alt="${alt}" width="${width}" height="${height}" layout="responsive"></amp-img>`;
    })
    // Replace iframe with amp-iframe
    .replace(/<iframe([^>]*)>/g, '<amp-iframe$1>')
    .replace(/<\/iframe>/g, '</amp-iframe>')
    // Replace video with amp-video
    .replace(/<video([^>]*)>/g, '<amp-video$1>')
    .replace(/<\/video>/g, '</amp-video>')
    // Remove scripts (not allowed in AMP)
    .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
}

/**
 * Generate AMP page structure
 */
export function generateAMPPage(options: {
  title: string;
  description: string;
  canonicalUrl: string;
  schemaMarkup?: object;
  content: string;
  styles?: string;
}): string {
  const { title, description, canonicalUrl, schemaMarkup, content, styles = '' } = options;

  return `<!doctype html>
<html amp lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
    <title>${title}</title>
    <link rel="canonical" href="${canonicalUrl}">
    <meta name="description" content="${description}">
    
    <!-- AMP Runtime -->
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    
    <!-- AMP Components -->
    <script async custom-element="amp-img" src="https://cdn.ampproject.org/v0/amp-img-0.1.js"></script>
    <script async custom-element="amp-carousel" src="https://cdn.ampproject.org/v0/amp-carousel-0.2.js"></script>
    <script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
    
    ${schemaMarkup ? `<script type="application/ld+json">${JSON.stringify(schemaMarkup)}</script>` : ''}
    
    <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>
    <noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
    
    <style amp-custom>
      body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        line-height: 1.6;
        color: #333;
        margin: 0;
        padding: 0;
      }
      ${styles}
    </style>
  </head>
  <body>
    ${content}
    
    <!-- AMP Analytics -->
    <amp-analytics type="gtag" data-credentials="include">
      <script type="application/json">
      {
        "vars": {
          "gtag_id": "G-XXXXXXXXXX",
          "config": {
            "G-XXXXXXXXXX": {
              "groups": "default"
            }
          }
        }
      }
      </script>
    </amp-analytics>
  </body>
</html>`;
}

/**
 * Validate AMP compatibility
 */
export function validateAMP(html: string): { valid: boolean; errors: string[] } {
  const errors: string[] = [];

  // Check for disallowed elements
  const disallowedTags = [
    'script',
    'style',
    'form',
    'input',
    'button',
    'select',
    'textarea',
  ];

  disallowedTags.forEach((tag) => {
    const regex = new RegExp(`<${tag}[^>]*>`, 'gi');
    if (regex.test(html)) {
      errors.push(`Disallowed tag found: ${tag}`);
    }
  });

  // Check for inline styles
  if (/<[^>]+style=/i.test(html)) {
    errors.push('Inline styles not allowed in AMP');
  }

  // Check for onclick handlers
  if (/onclick=/i.test(html)) {
    errors.push('onclick handlers not allowed in AMP');
  }

  return {
    valid: errors.length === 0,
    errors,
  };
}

/**
 * AMP image specifications
 */
export const ampImageSizes = {
  thumbnail: { width: 150, height: 150 },
  small: { width: 300, height: 200 },
  medium: { width: 600, height: 400 },
  large: { width: 1200, height: 800 },
  hero: { width: 1920, height: 1080 },
};

/**
 * Generate AMP image tag
 */
export function generateAMPImage(options: {
  src: string;
  alt: string;
  width: number;
  height: number;
  layout?: 'responsive' | 'fixed' | 'intrinsic' | 'fill';
}): string {
  const { src, alt, width, height, layout = 'responsive' } = options;

  return `<amp-img 
    src="${src}" 
    alt="${alt}" 
    width="${width}" 
    height="${height}" 
    layout="${layout}"
  ></amp-img>`;
}

/**
 * AMP carousel for property images
 */
export function generateAMPCarousel(images: Array<{ src: string; alt: string }>): string {
  const imageElements = images
    .map((img) =>
      generateAMPImage({
        src: img.src,
        alt: img.alt,
        width: 1200,
        height: 800,
      })
    )
    .join('\n');

  return `<amp-carousel
    width="1200"
    height="800"
    layout="responsive"
    type="slides"
    autoplay
    delay="3000"
  >
    ${imageElements}
  </amp-carousel>`;
}
