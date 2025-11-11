# Accessibility (A11y) Implementation Guide

This document outlines the accessibility improvements implemented across the RentHub frontend application.

## Key Features

### 1. **Keyboard Navigation**
- **Skip to Content Link**: Added at the top of every page, visible on focus for keyboard users
- **Conversation List**: Full keyboard navigation with Enter/Space to select conversations
- **Filter Panel**: All buttons support keyboard interaction with aria-pressed states
- **Focus Management**: Proper tabindex and focus indicators throughout

### 2. **Screen Reader Support**
- **ARIA Landmarks**: Proper semantic HTML with role attributes
  - `role="main"` on main content area
  - `role="region"` on filter panel
  - `role="log"` on chat messages (live region)
  - `role="group"` for related controls
- **ARIA Labels**: Descriptive labels for all interactive elements
  - Button purposes clearly announced
  - Form inputs with proper labels
  - Dynamic content changes announced
- **Live Regions**: Real-time announcements for:
  - New chat messages
  - Filter changes
  - Connection status

### 3. **Focus Management**
- Visible focus indicators on all interactive elements
- Focus trap utilities for modals/dialogs
- Restore focus after closing overlays
- Logical tab order throughout the application

### 4. **Utility Functions** (`lib/a11y-utils.ts`)
```typescript
// Announce messages to screen readers
announceToScreenReader(message: string, priority: 'polite' | 'assertive')

// Trap focus within container (for modals)
trapFocus(element: HTMLElement)

// Focus management helpers
createFocusManager()

// Keyboard navigation for lists
handleListNavigation(event, currentIndex, totalItems, onSelect)
```

## Components Enhanced

### Messages Page
- ✅ Keyboard navigation for conversation list
- ✅ ARIA labels for all buttons (voice call, video call, archive, attach file, send)
- ✅ Screen reader announcements for new messages
- ✅ Proper role="log" for messages area with aria-live="polite"
- ✅ Aria-current for selected conversation
- ✅ Accessible file attachment removal

### Filter Panel
- ✅ ARIA labels for all filter sections
- ✅ aria-pressed states for toggle buttons (property types, amenities)
- ✅ Proper grouping with role="group"
- ✅ Screen reader announcements for active filter count
- ✅ Proper label associations with inputs

### Main Layout
- ✅ Skip to content link (hidden, visible on focus)
- ✅ Proper landmark regions (header, main, footer)
- ✅ Semantic HTML structure

## Testing Guidelines

### Manual Testing Checklist
1. **Keyboard Only Navigation**:
   - [ ] Can access all interactive elements via Tab key
   - [ ] Enter/Space activate buttons and links
   - [ ] Arrow keys work in lists and select elements
   - [ ] Can close modals with Escape key
   - [ ] Skip link works (Tab once on page load)

2. **Screen Reader Testing** (NVDA/JAWS/VoiceOver):
   - [ ] All images have alt text
   - [ ] Form inputs properly labeled
   - [ ] Button purposes clearly announced
   - [ ] Dynamic content changes announced
   - [ ] Headings in logical order

3. **Visual Testing**:
   - [ ] Focus indicators visible and clear
   - [ ] Color contrast meets WCAG AA (4.5:1 for normal text)
   - [ ] Text resizable to 200% without loss of functionality
   - [ ] No content relies solely on color

### Automated Testing (Future)
Consider adding:
- `@axe-core/react` for runtime accessibility audits
- `jest-axe` for unit test accessibility checks
- Lighthouse CI in deployment pipeline

## WCAG 2.1 Compliance

Current implementation targets **WCAG 2.1 Level AA** compliance:

### Perceivable
- ✅ Text alternatives for non-text content
- ✅ Sufficient color contrast
- ✅ Semantic HTML structure

### Operable
- ✅ Keyboard accessible
- ✅ Sufficient time for interactions
- ✅ Clear focus indicators
- ✅ Descriptive page titles
- ✅ Logical navigation order

### Understandable
- ✅ Consistent navigation
- ✅ Predictable functionality
- ✅ Input assistance with labels
- ✅ Error messages are clear

### Robust
- ✅ Valid HTML
- ✅ Proper ARIA usage
- ✅ Compatible with assistive technologies

## Known Limitations & Future Improvements

1. **Maps**: Mapbox/Leaflet accessibility could be enhanced with:
   - Keyboard controls for pan/zoom
   - Text alternatives for map markers
   - List view alternative for map data

2. **Chat**: Consider adding:
   - Message grouping by date with proper headings
   - Emoji picker keyboard support
   - Typing indicator for screen readers

3. **Filters**: Could improve with:
   - Instant feedback count ("X properties found")
   - Clear all filters keyboard shortcut

4. **General**:
   - Add skip links for repeating content blocks
   - Implement reduced motion preferences
   - Add high contrast mode support

## Resources

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [ARIA Authoring Practices](https://www.w3.org/WAI/ARIA/apg/)
- [WebAIM Checklist](https://webaim.org/standards/wcag/checklist)
- [Inclusive Components](https://inclusive-components.design/)

## Maintenance

When adding new components:
1. Use semantic HTML first
2. Add ARIA only when HTML semantics insufficient
3. Test with keyboard only
4. Test with screen reader
5. Verify color contrast
6. Check focus indicators

For questions or improvements, refer to the [a11y-utils.ts](../src/lib/a11y-utils.ts) utility file.
