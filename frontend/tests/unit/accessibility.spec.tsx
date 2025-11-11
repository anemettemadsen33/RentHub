import { describe, it, expect } from 'vitest';
import { breadcrumbSets } from '@/lib/breadcrumbs';

// Minimal a11y-related smoke test validating dynamic breadcrumb title rendering

describe('Breadcrumbs data', () => {
  it('includes dynamic property title as titleOverride', () => {
    const title = 'Ocean View Apartment';
    const items = breadcrumbSets.propertyReviews('123', title);
    // The third item is the property item with titleOverride
    const propertyItem = items[2] as any;
    expect(propertyItem.titleOverride).toBe(title);
  });
});
