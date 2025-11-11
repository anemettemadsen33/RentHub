declare module 'next-pwa' {
  interface PWAOptions {
    dest: string;
    register?: boolean;
    skipWaiting?: boolean;
    disable?: boolean;
    cacheOnFrontEndNav?: boolean;
    [key: string]: any;
  }
  export default function withPWA(options: PWAOptions): (nextConfig: any) => any;
}
