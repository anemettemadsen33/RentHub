/**
 * Mobile-first design utilities and responsive helpers
 */

export const BREAKPOINTS = {
  xs: 320,
  sm: 640,
  md: 768,
  lg: 1024,
  xl: 1280,
  '2xl': 1536,
} as const;

export type Breakpoint = keyof typeof BREAKPOINTS;

/**
 * Check if current viewport matches breakpoint
 */
export function matchesBreakpoint(breakpoint: Breakpoint): boolean {
  if (typeof window === 'undefined') return false;
  return window.innerWidth >= BREAKPOINTS[breakpoint];
}

/**
 * Get current breakpoint
 */
export function getCurrentBreakpoint(): Breakpoint {
  if (typeof window === 'undefined') return 'xs';

  const width = window.innerWidth;
  const breakpoints = Object.entries(BREAKPOINTS).sort((a, b) => b[1] - a[1]);

  for (const [key, value] of breakpoints) {
    if (width >= value) {
      return key as Breakpoint;
    }
  }

  return 'xs';
}

/**
 * Mobile detection utilities
 */
export const device = {
  isMobile: (): boolean => {
    if (typeof window === 'undefined') return false;
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(
      navigator.userAgent
    );
  },

  isIOS: (): boolean => {
    if (typeof window === 'undefined') return false;
    return /iPhone|iPad|iPod/i.test(navigator.userAgent);
  },

  isAndroid: (): boolean => {
    if (typeof window === 'undefined') return false;
    return /Android/i.test(navigator.userAgent);
  },

  isTablet: (): boolean => {
    if (typeof window === 'undefined') return false;
    return /iPad|Android/i.test(navigator.userAgent) && window.innerWidth >= 768;
  },

  isTouchDevice: (): boolean => {
    if (typeof window === 'undefined') return false;
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
  },

  supportsHover: (): boolean => {
    if (typeof window === 'undefined') return true;
    return window.matchMedia('(hover: hover)').matches;
  },
};

/**
 * Viewport utilities
 */
export const viewport = {
  getWidth: (): number => {
    if (typeof window === 'undefined') return 0;
    return window.innerWidth;
  },

  getHeight: (): number => {
    if (typeof window === 'undefined') return 0;
    return window.innerHeight;
  },

  getOrientation: (): 'portrait' | 'landscape' => {
    if (typeof window === 'undefined') return 'portrait';
    return window.innerWidth > window.innerHeight ? 'landscape' : 'portrait';
  },

  isPortrait: (): boolean => {
    return viewport.getOrientation() === 'portrait';
  },

  isLandscape: (): boolean => {
    return viewport.getOrientation() === 'landscape';
  },
};

/**
 * Touch event utilities
 */
export class TouchHandler {
  private startX = 0;
  private startY = 0;
  private endX = 0;
  private endY = 0;
  private minSwipeDistance = 50;

  constructor(private element: HTMLElement) {
    this.handleTouchStart = this.handleTouchStart.bind(this);
    this.handleTouchEnd = this.handleTouchEnd.bind(this);
  }

  private handleTouchStart(e: TouchEvent) {
    this.startX = e.touches[0].clientX;
    this.startY = e.touches[0].clientY;
  }

  private handleTouchEnd(e: TouchEvent) {
    this.endX = e.changedTouches[0].clientX;
    this.endY = e.changedTouches[0].clientY;
    this.handleSwipe();
  }

  private handleSwipe() {
    const deltaX = this.endX - this.startX;
    const deltaY = this.endY - this.startY;

    if (Math.abs(deltaX) > Math.abs(deltaY)) {
      // Horizontal swipe
      if (Math.abs(deltaX) > this.minSwipeDistance) {
        if (deltaX > 0) {
          this.onSwipeRight();
        } else {
          this.onSwipeLeft();
        }
      }
    } else {
      // Vertical swipe
      if (Math.abs(deltaY) > this.minSwipeDistance) {
        if (deltaY > 0) {
          this.onSwipeDown();
        } else {
          this.onSwipeUp();
        }
      }
    }
  }

  onSwipeLeft() {}
  onSwipeRight() {}
  onSwipeUp() {}
  onSwipeDown() {}

  enable() {
    this.element.addEventListener('touchstart', this.handleTouchStart, { passive: true });
    this.element.addEventListener('touchend', this.handleTouchEnd, { passive: true });
  }

  disable() {
    this.element.removeEventListener('touchstart', this.handleTouchStart);
    this.element.removeEventListener('touchend', this.handleTouchEnd);
  }
}

/**
 * Prevent zoom on double-tap (iOS)
 */
export function preventDoubleClickZoom(element: HTMLElement) {
  let lastTap = 0;

  element.addEventListener('touchend', (e) => {
    const currentTime = new Date().getTime();
    const tapLength = currentTime - lastTap;

    if (tapLength < 500 && tapLength > 0) {
      e.preventDefault();
    }

    lastTap = currentTime;
  });
}

/**
 * Adaptive font sizing
 */
export function getResponsiveFontSize(baseSizePx: number): string {
  const breakpoint = getCurrentBreakpoint();
  const scale = {
    xs: 0.875,
    sm: 0.9375,
    md: 1,
    lg: 1.0625,
    xl: 1.125,
    '2xl': 1.25,
  };

  return `${baseSizePx * (scale[breakpoint] || 1)}px`;
}

/**
 * Safe area insets (for notched devices)
 */
export const safeArea = {
  top: 'env(safe-area-inset-top, 0px)',
  right: 'env(safe-area-inset-right, 0px)',
  bottom: 'env(safe-area-inset-bottom, 0px)',
  left: 'env(safe-area-inset-left, 0px)',
};

/**
 * Generate responsive styles
 */
export function responsive<T>(values: Partial<Record<Breakpoint, T>>): T | undefined {
  const breakpoint = getCurrentBreakpoint();
  const orderedBreakpoints: Breakpoint[] = ['xs', 'sm', 'md', 'lg', 'xl', '2xl'];
  const currentIndex = orderedBreakpoints.indexOf(breakpoint);

  // Find the closest defined value at or below current breakpoint
  for (let i = currentIndex; i >= 0; i--) {
    const bp = orderedBreakpoints[i];
    if (values[bp] !== undefined) {
      return values[bp];
    }
  }

  return undefined;
}

/**
 * Optimize for mobile performance
 */
export function applyMobileOptimizations() {
  if (typeof window === 'undefined' || !device.isMobile()) return;

  // Disable hover effects on mobile
  document.body.classList.add('mobile-device');

  // Add touch-action CSS
  document.body.style.touchAction = 'manipulation';

  // Prevent elastic scrolling on iOS
  if (device.isIOS()) {
    document.body.style.overscrollBehavior = 'none';
  }

  // Optimize scrolling performance
  document.addEventListener('touchstart', () => {}, { passive: true });
  document.addEventListener('touchmove', () => {}, { passive: true });
}

/**
 * Mobile menu utilities
 */
export function lockBodyScroll() {
  if (typeof document === 'undefined') return;
  document.body.style.overflow = 'hidden';
  document.body.style.paddingRight = `${window.innerWidth - document.body.offsetWidth}px`;
}

export function unlockBodyScroll() {
  if (typeof document === 'undefined') return;
  document.body.style.overflow = '';
  document.body.style.paddingRight = '';
}

/**
 * Check if reduced motion is preferred
 */
export function prefersReducedMotion(): boolean {
  if (typeof window === 'undefined') return false;
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * Battery status check
 */
export async function getBatteryInfo() {
  if (typeof navigator === 'undefined' || !('getBattery' in navigator)) {
    return null;
  }

  try {
    const battery = await (navigator as any).getBattery();
    return {
      level: battery.level,
      charging: battery.charging,
      dischargingTime: battery.dischargingTime,
      chargingTime: battery.chargingTime,
    };
  } catch {
    return null;
  }
}

/**
 * Optimize based on battery level
 */
export async function shouldOptimizeForBattery(): Promise<boolean> {
  const battery = await getBatteryInfo();
  if (!battery) return false;

  // Optimize if battery is low and not charging
  return battery.level < 0.2 && !battery.charging;
}
