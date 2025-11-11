import { describe, it, expect, vi } from 'vitest';
import { debounce } from '@/lib/utils';

describe('debounce', () => {
  it('delays function execution', async () => {
    vi.useFakeTimers();
    const spy = vi.fn();
    const deb = debounce(spy, 200);

    deb('a');
    deb('b');
    expect(spy).not.toHaveBeenCalled();

    vi.advanceTimersByTime(199);
    expect(spy).not.toHaveBeenCalled();

    vi.advanceTimersByTime(1);
    expect(spy).toHaveBeenCalledTimes(1);
    expect(spy).toHaveBeenCalledWith('b');

    vi.useRealTimers();
  });
});
