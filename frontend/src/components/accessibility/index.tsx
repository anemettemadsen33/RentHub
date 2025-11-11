import React from 'react';
import { cn } from '@/lib/utils';

/**
 * VisuallyHidden - hides content visually but keeps it accessible to screen readers
 * Use for: icon-only buttons, decorative text, SR-only labels
 */
interface VisuallyHiddenProps {
  children: React.ReactNode;
  className?: string;
}

export function VisuallyHidden({ children, className }: VisuallyHiddenProps) {
  return (
    <span
      className={cn(
        'sr-only absolute w-px h-px p-0 -m-px overflow-hidden whitespace-nowrap border-0',
        className
      )}
    >
      {children}
    </span>
  );
}

/**
 * SkipToContent - allows keyboard users to skip navigation
 * Should be the first focusable element on the page
 */
export function SkipToContent() {
  return (
    <a
      href="#main-content"
      className="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-primary focus:text-primary-foreground focus:rounded-md focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
    >
      Skip to main content
    </a>
  );
}

/**
 * FocusTrap - traps focus within a container (useful for modals, dialogs)
 */
interface FocusTrapProps {
  children: React.ReactNode;
  active?: boolean;
  className?: string;
}

export function FocusTrap({ children, active = true, className }: FocusTrapProps) {
  const containerRef = React.useRef<HTMLDivElement>(null);

  React.useEffect(() => {
    if (!active || !containerRef.current) return;

    const container = containerRef.current;
    const focusableElements = container.querySelectorAll<HTMLElement>(
      'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
    );

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    const handleTabKey = (e: KeyboardEvent) => {
      if (e.key !== 'Tab') return;

      if (e.shiftKey) {
        // Shift + Tab
        if (document.activeElement === firstElement) {
          lastElement?.focus();
          e.preventDefault();
        }
      } else {
        // Tab
        if (document.activeElement === lastElement) {
          firstElement?.focus();
          e.preventDefault();
        }
      }
    };

    container.addEventListener('keydown', handleTabKey);
    firstElement?.focus();

    return () => {
      container.removeEventListener('keydown', handleTabKey);
    };
  }, [active]);

  return (
    <div ref={containerRef} className={className}>
      {children}
    </div>
  );
}

/**
 * LiveRegion - announces dynamic content changes to screen readers
 * Use for: notifications, alerts, status messages
 */
interface LiveRegionProps {
  children: React.ReactNode;
  priority?: 'polite' | 'assertive';
  atomic?: boolean;
  relevant?: 'additions' | 'removals' | 'text' | 'all';
  className?: string;
}

export function LiveRegion({
  children,
  priority = 'polite',
  atomic = true,
  relevant = 'all',
  className,
}: LiveRegionProps) {
  return (
    <div
      role="status"
      aria-live={priority}
      aria-atomic={atomic}
      aria-relevant={relevant}
      className={className}
    >
      {children}
    </div>
  );
}

/**
 * KeyboardShortcut - displays keyboard shortcut hint
 */
interface KeyboardShortcutProps {
  keys: string[];
  description: string;
  onActivate?: () => void;
}

export function KeyboardShortcut({ keys, description, onActivate }: KeyboardShortcutProps) {
  React.useEffect(() => {
    if (!onActivate) return;

    const handleKeyPress = (e: KeyboardEvent) => {
      const modifiers = {
        ctrl: e.ctrlKey || e.metaKey,
        shift: e.shiftKey,
        alt: e.altKey,
      };

      const keysPressed = keys.every((key) => {
        if (key === 'ctrl' || key === 'cmd') return modifiers.ctrl;
        if (key === 'shift') return modifiers.shift;
        if (key === 'alt') return modifiers.alt;
        return e.key.toLowerCase() === key.toLowerCase();
      });

      if (keysPressed) {
        e.preventDefault();
        onActivate();
      }
    };

    window.addEventListener('keydown', handleKeyPress);
    return () => window.removeEventListener('keydown', handleKeyPress);
  }, [keys, onActivate]);

  return (
    <kbd className="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">
      {keys.map((key, idx) => (
        <React.Fragment key={idx}>
          {idx > 0 && <span className="mx-1">+</span>}
          <span className="capitalize">{key}</span>
        </React.Fragment>
      ))}
    </kbd>
  );
}

/**
 * ErrorAnnouncement - announces form errors to screen readers
 */
interface ErrorAnnouncementProps {
  errors: string[];
}

export function ErrorAnnouncement({ errors }: ErrorAnnouncementProps) {
  if (errors.length === 0) return null;

  return (
    <LiveRegion priority="assertive" className="sr-only">
      {errors.length === 1 ? (
        <p>Error: {errors[0]}</p>
      ) : (
        <>
          <p>{errors.length} errors found:</p>
          <ul>
            {errors.map((error, idx) => (
              <li key={idx}>{error}</li>
            ))}
          </ul>
        </>
      )}
    </LiveRegion>
  );
}

/**
 * LoadingAnnouncement - announces loading states
 */
interface LoadingAnnouncementProps {
  loading: boolean;
  loadingMessage?: string;
  completedMessage?: string;
}

export function LoadingAnnouncement({
  loading,
  loadingMessage = 'Loading...',
  completedMessage = 'Content loaded',
}: LoadingAnnouncementProps) {
  return (
    <LiveRegion priority="polite" className="sr-only">
      {loading ? loadingMessage : completedMessage}
    </LiveRegion>
  );
}
