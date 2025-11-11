/**
 * Accessibility utilities for focus management and keyboard navigation
 */

/**
 * Traps focus within a container element (useful for modals, dialogs)
 */
export function trapFocus(element: HTMLElement) {
  const focusableElements = element.querySelectorAll<HTMLElement>(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
  );
  const firstElement = focusableElements[0];
  const lastElement = focusableElements[focusableElements.length - 1];

  const handleKeyDown = (e: KeyboardEvent) => {
    if (e.key !== 'Tab') return;

    if (e.shiftKey && document.activeElement === firstElement) {
      e.preventDefault();
      lastElement?.focus();
    } else if (!e.shiftKey && document.activeElement === lastElement) {
      e.preventDefault();
      firstElement?.focus();
    }
  };

  element.addEventListener('keydown', handleKeyDown);
  return () => element.removeEventListener('keydown', handleKeyDown);
}

/**
 * Announces message to screen readers using live region
 */
export function announceToScreenReader(message: string, priority: 'polite' | 'assertive' = 'polite') {
  const announcement = document.createElement('div');
  announcement.setAttribute('role', 'status');
  announcement.setAttribute('aria-live', priority);
  announcement.setAttribute('aria-atomic', 'true');
  announcement.className = 'sr-only';
  announcement.textContent = message;
  
  document.body.appendChild(announcement);
  
  setTimeout(() => {
    document.body.removeChild(announcement);
  }, 1000);
}

/**
 * Restores focus to a previously focused element
 */
export function createFocusManager() {
  let previousFocus: HTMLElement | null = null;

  return {
    save: () => {
      previousFocus = document.activeElement as HTMLElement;
    },
    restore: () => {
      previousFocus?.focus();
      previousFocus = null;
    },
  };
}

/**
 * Generates unique IDs for ARIA relationships
 */
let idCounter = 0;
export function generateAriaId(prefix: string = 'a11y'): string {
  return `${prefix}-${++idCounter}-${Date.now()}`;
}

/**
 * Checks if element is visible and focusable
 */
export function isFocusable(element: HTMLElement): boolean {
  if (element.tabIndex < 0) return false;
  if (element.offsetParent === null) return false;
  if (window.getComputedStyle(element).visibility === 'hidden') return false;
  return true;
}

/**
 * Keyboard navigation handler for list items
 */
export function handleListNavigation(
  event: React.KeyboardEvent,
  currentIndex: number,
  totalItems: number,
  onSelect: (index: number) => void
) {
  const key = event.key;
  
  switch (key) {
    case 'ArrowDown':
      event.preventDefault();
      onSelect((currentIndex + 1) % totalItems);
      break;
    case 'ArrowUp':
      event.preventDefault();
      onSelect((currentIndex - 1 + totalItems) % totalItems);
      break;
    case 'Home':
      event.preventDefault();
      onSelect(0);
      break;
    case 'End':
      event.preventDefault();
      onSelect(totalItems - 1);
      break;
    case 'Enter':
    case ' ':
      event.preventDefault();
      // Trigger activation on current item
      break;
  }
}

/**
 * Skip to main content link helper
 */
export function skipToContent(targetId: string) {
  const target = document.getElementById(targetId);
  if (target) {
    target.tabIndex = -1;
    target.focus();
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
}
