'use client';

import { useCallback, useMemo, useRef } from 'react';

/**
 * Performance optimization utilities
 */

/**
 * Debounce hook for expensive operations
 * 
 * Usage:
 * ```tsx
 * const debouncedSearch = useDebounce((query: string) => {
 *   // Expensive search operation
 * }, 300);
 * ```
 */
export function useDebounce<T extends (...args: any[]) => void>(
  callback: T,
  delay: number
): (...args: Parameters<T>) => void {
  const timeoutRef = useRef<NodeJS.Timeout>();

  return useCallback(
    (...args: Parameters<T>) => {
      if (timeoutRef.current) {
        clearTimeout(timeoutRef.current);
      }
      timeoutRef.current = setTimeout(() => {
        callback(...args);
      }, delay);
    },
    [callback, delay]
  );
}

/**
 * Throttle hook for rate-limiting
 * 
 * Usage:
 * ```tsx
 * const throttledScroll = useThrottle(() => {
 *   // Handle scroll
 * }, 100);
 * ```
 */
export function useThrottle<T extends (...args: any[]) => void>(
  callback: T,
  delay: number
): (...args: Parameters<T>) => void {
  const lastRunRef = useRef(Date.now());

  return useCallback(
    (...args: Parameters<T>) => {
      const now = Date.now();
      if (now - lastRunRef.current >= delay) {
        callback(...args);
        lastRunRef.current = now;
      }
    },
    [callback, delay]
  );
}

/**
 * Memoized filtered and sorted list
 * 
 * Usage:
 * ```tsx
 * const { filtered, sorted } = useFilteredList(
 *   items,
 *   (item) => item.title.includes(query),
 *   (a, b) => a.price - b.price
 * );
 * ```
 */
export function useFilteredList<T>(
  items: T[],
  filterFn: (item: T) => boolean,
  sortFn?: (a: T, b: T) => number
) {
  const filtered = useMemo(() => items.filter(filterFn), [items, filterFn]);
  const sorted = useMemo(
    () => (sortFn ? [...filtered].sort(sortFn) : filtered),
    [filtered, sortFn]
  );

  return { filtered, sorted };
}

/**
 * Intersection observer for lazy loading
 * 
 * Usage:
 * ```tsx
 * const ref = useIntersectionObserver(() => {
 *   loadMore();
 * });
 * 
 * <div ref={ref}>Load more trigger</div>
 * ```
 */
export function useIntersectionObserver(
  onIntersect: () => void,
  options?: IntersectionObserverInit
) {
  const ref = useRef<HTMLDivElement>(null);

  useMemo(() => {
    if (!ref.current) return;

    const observer = new IntersectionObserver(([entry]) => {
      if (entry.isIntersecting) {
        onIntersect();
      }
    }, options);

    observer.observe(ref.current);

    return () => observer.disconnect();
  }, [onIntersect, options]);

  return ref;
}

/**
 * Virtual list hook for large lists
 * Returns visible items and container/spacer styles
 * 
 * Usage:
 * ```tsx
 * const { visibleItems, containerStyle, topSpacerStyle, bottomSpacerStyle } = 
 *   useVirtualList(items, 500, 50); // containerHeight, itemHeight
 * ```
 */
export function useVirtualList<T>(
  items: T[],
  containerHeight: number,
  itemHeight: number,
  scrollTop: number = 0
) {
  const startIndex = Math.max(0, Math.floor(scrollTop / itemHeight) - 5);
  const endIndex = Math.min(
    items.length,
    Math.ceil((scrollTop + containerHeight) / itemHeight) + 5
  );

  const visibleItems = useMemo(
    () =>
      items.slice(startIndex, endIndex).map((item, i) => ({
        item,
        index: startIndex + i,
      })),
    [items, startIndex, endIndex]
  );

  const topSpacerHeight = startIndex * itemHeight;
  const bottomSpacerHeight = (items.length - endIndex) * itemHeight;

  return {
    visibleItems,
    containerStyle: { height: containerHeight, overflow: 'auto' },
    topSpacerStyle: { height: topSpacerHeight },
    bottomSpacerStyle: { height: bottomSpacerHeight },
  };
}

/**
 * Prefetch on hover for faster navigation
 * 
 * Usage:
 * ```tsx
 * const handleHover = usePrefetch('/properties/123');
 * <Link href="/properties/123" onMouseEnter={handleHover}>...</Link>
 * ```
 */
export function usePrefetch(href: string) {
  return useCallback(() => {
    // Next.js automatically prefetches on hover for <Link>
    // This is a placeholder for custom prefetch logic if needed
    if (typeof window !== 'undefined') {
      const link = document.createElement('link');
      link.rel = 'prefetch';
      link.href = href;
      document.head.appendChild(link);
    }
  }, [href]);
}
