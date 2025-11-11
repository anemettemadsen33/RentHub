import { describe, it, expect } from 'vitest';
import { DashboardStatsSchema } from '@/lib/schemas';
import { parse } from '@/lib/api-client';
import { z } from 'zod';

describe('API client schemas', () => {
  it('validates dashboard stats schema (happy path)', () => {
    const data = {
      properties: 3,
      bookingsUpcoming: 2,
      revenueLast30: 1234.56,
      guestsUnique: 7,
    };
    const parsed = DashboardStatsSchema.parse(data);
    expect(parsed.properties).toBe(3);
  });

  it('fails for invalid dashboard stats payload', () => {
    const bad = {
      properties: 'three', // invalid type
      bookingsUpcoming: 2,
      revenueLast30: 100,
      guestsUnique: 5,
    } as any;
    expect(() => DashboardStatsSchema.parse(bad)).toThrowError(/Expected number/);
  });

  it('parse helper surfaces issues', async () => {
    const schema = z.object({ a: z.string() });
    await expect(parse(schema as any, { a: 1 })).rejects.toThrow(/Schema validation failed/);
  });
});
