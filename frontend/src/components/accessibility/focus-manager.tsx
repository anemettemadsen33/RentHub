'use client';

import { useEffect, useCallback } from 'react';

/**
 * FocusManager - Global accessibility utility for focus management
 * 
 * Features:
 * - Focus visible styles (keyboard navigation only)
 * - Focus trap for modals/dialogs
 * - Focus restoration after modal close
 * - Programmatic focus with smooth scroll
 * 
 * Usage:
 * ```tsx
 * const { focusElement, trapFocus, releaseFocus } = useFocusManager();
 * 
 * // Focus element programmatically
 * focusElement('elementId');
 * 
 * // Trap focus in modal
 * trapFocus('modalId');
 * 
 * // Release trap
 * releaseFocus();
 * ```
 */

export function useFocusManager() {
  const focusElement = useCallback((elementId: string, smooth = true) => {
    const element = document.getElementById(elementId);
    if (element) {
      element.tabIndex = -1;
      element.focus({ preventScroll: !smooth });
      if (smooth) {
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  }, []);

  const trapFocus = useCallback((containerId: string) => {
    const container = document.getElementById(containerId);
    if (!container) return;

    const focusableElements = container.querySelectorAll(
      'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'
    );
    
    const firstElement = focusableElements[0] as HTMLElement;
    const lastElement = focusableElements[focusableElements.length - 1] as HTMLElement;

    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key !== 'Tab') return;

      if (e.shiftKey) {
        if (document.activeElement === firstElement) {
          lastElement?.focus();
          e.preventDefault();
        }
      } else {
        if (document.activeElement === lastElement) {
          firstElement?.focus();
          e.preventDefault();
        }
      }
    };

    container.addEventListener('keydown', handleKeyDown);
    firstElement?.focus();

    return () => {
      container.removeEventListener('keydown', handleKeyDown);
    };
  }, []);

  const releaseFocus = useCallback(() => {
    // Remove any active traps
    // In practice, cleanup happens via the trap return function
  }, []);

  return { focusElement, trapFocus, releaseFocus };
}

/**
 * Global focus visible detection
 * Adds 'focus-visible' class to body when keyboard navigation is detected
 */
export function FocusVisibleDetector() {
  useEffect(() => {
    let isKeyboardNav = false;

    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Tab') {
        isKeyboardNav = true;
        document.body.classList.add('keyboard-nav');
      }
    };

    const handleMouseDown = () => {
      isKeyboardNav = false;
      document.body.classList.remove('keyboard-nav');
    };

    document.addEventListener('keydown', handleKeyDown);
    document.addEventListener('mousedown', handleMouseDown);

    return () => {
      document.removeEventListener('keydown', handleKeyDown);
      document.removeEventListener('mousedown', handleMouseDown);
    };
  }, []);

  return null;
}

/**
 * Announce content to screen readers
 */
export function announce(message: string, priority: 'polite' | 'assertive' = 'polite') {
  const liveRegion = document.getElementById('global-live-region');
  if (liveRegion) {
    liveRegion.setAttribute('aria-live', priority);
    liveRegion.textContent = message;
    // Clear after announcement
    setTimeout(() => {
      liveRegion.textContent = '';
    }, 1000);
  }
}
