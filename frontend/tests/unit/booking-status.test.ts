import { describe, it, expect } from 'vitest';
import { getBookingStatusSteps } from '../../src/lib/booking-status';

describe('getBookingStatusSteps', () => {
  it('marks pending as current correctly', () => {
    const steps = getBookingStatusSteps('pending');
    expect(steps.some(s => s.current && s.key === 'pending')).toBe(true);
  });

  it('marks confirmed as current and previous done', () => {
    const steps = getBookingStatusSteps('confirmed');
    const pending = steps.find(s => s.key === 'pending');
    const confirmed = steps.find(s => s.key === 'confirmed');
    expect(pending?.done).toBe(true);
    expect(confirmed?.current).toBe(true);
  });

  it('handles cancelled marking cancelled flag', () => {
    const steps = getBookingStatusSteps('cancelled');
    expect(steps.every(s => s.cancelled)).toBe(true);
  });
});
