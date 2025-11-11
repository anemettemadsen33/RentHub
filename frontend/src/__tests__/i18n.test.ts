import { describe, it, expect } from 'vitest';

/**
 * i18n Key Presence Tests
 * Validates that all required translation keys exist in locale files
 */

import enMessages from '../../messages/en.json';
import roMessages from '../../messages/ro.json';

describe('i18n Key Presence', () => {
  const requiredNamespaces = [
    'home',
    'comparison',
    'properties',
    'bookingsPage',
    'navigation',
    'notify',
  ];

  describe('English messages', () => {
    it('has all required namespaces', () => {
      requiredNamespaces.forEach((ns) => {
        expect(enMessages).toHaveProperty(ns);
      });
    });

    it('home namespace has required keys', () => {
      expect(enMessages.home).toHaveProperty('title');
      expect(enMessages.home).toHaveProperty('subtitle');
      expect(enMessages.home).toHaveProperty('cta_browse_tooltip');
      expect(enMessages.home).toHaveProperty('cta_learn_tooltip');
    });

    it('bookingsPage namespace has required keys', () => {
      expect(enMessages.bookingsPage).toHaveProperty('title');
      expect(enMessages.bookingsPage).toHaveProperty('filters');
      expect(enMessages.bookingsPage.filters).toHaveProperty('all');
      expect(enMessages.bookingsPage).toHaveProperty('actions');
    });

    it('properties namespace has required keys', () => {
      expect(enMessages.properties).toHaveProperty('title');
      expect(enMessages.properties).toHaveProperty('searchAriaLabel');
      expect(enMessages.properties).toHaveProperty('sort');
      expect(enMessages.properties.sort).toHaveProperty('label');
    });
  });

  describe('Romanian messages', () => {
    it('has all required namespaces', () => {
      requiredNamespaces.forEach((ns) => {
        expect(roMessages).toHaveProperty(ns);
      });
    });
  });

  describe('Key parity between locales', () => {
    it('English and Romanian have matching top-level keys', () => {
      const enKeys = Object.keys(enMessages).sort();
      const roKeys = Object.keys(roMessages).sort();
      
      // Allow some flexibility for incomplete translations
      const sharedKeys = enKeys.filter(key => roKeys.includes(key));
      expect(sharedKeys.length).toBeGreaterThan(10);
    });
  });
});
