import { describe, it, expect } from 'vitest';
import { formatCurrency, formatDate, cn, truncateText } from '@/lib/utils';

describe('Utils - formatCurrency', () => {
  it('should format USD correctly', () => {
    expect(formatCurrency(100, 'USD')).toBe('$100.00');
    expect(formatCurrency(1234.56, 'USD')).toBe('$1,234.56');
  });

  it('should format EUR correctly', () => {
    expect(formatCurrency(100, 'EUR')).toContain('100');
    expect(formatCurrency(1234.56, 'EUR')).toContain('1,234.56');
  });

  it('should format RON correctly', () => {
    expect(formatCurrency(100, 'RON')).toContain('100');
  });

  it('should handle zero and negative values', () => {
    expect(formatCurrency(0, 'USD')).toBe('$0.00');
    expect(formatCurrency(-50, 'USD')).toBe('-$50.00');
  });
});

describe('Utils - formatDate', () => {
  it('should format date correctly', () => {
    const date = new Date('2025-01-15');
    const formatted = formatDate(date);
    expect(formatted).toMatch(/Jan/);
    expect(formatted).toMatch(/15/);
  });

  it('should handle different formats', () => {
    const date = new Date('2025-01-15');
    const short = formatDate(date, 'short');
    const long = formatDate(date, 'long');
    
    expect(short).toBeTruthy();
    expect(long).toBeTruthy();
    expect(long.length).toBeGreaterThan(short.length);
  });
});

describe('Utils - cn (className merger)', () => {
  it('should merge class names', () => {
    const result = cn('text-white', 'bg-black');
    expect(result).toBe('text-white bg-black');
  });

  it('should handle conditional classes', () => {
    const isActive = true;
    const result = cn('base-class', isActive && 'active-class');
    expect(result).toContain('active-class');
  });

  it('should remove duplicate classes', () => {
    const result = cn('text-white', 'text-black');
    expect(result).toBe('text-black'); // Last wins
  });
});

describe('Utils - truncateText', () => {
  it('should truncate long text', () => {
    const longText = 'This is a very long text that should be truncated';
    const result = truncateText(longText, 20);
    
    expect(result.length).toBeLessThanOrEqual(23); // 20 + '...'
    expect(result).toContain('...');
  });

  it('should not truncate short text', () => {
    const shortText = 'Short text';
    const result = truncateText(shortText, 20);
    
    expect(result).toBe(shortText);
    expect(result).not.toContain('...');
  });
});
