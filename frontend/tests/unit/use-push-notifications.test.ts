import { describe, it, expect, vi } from 'vitest';
import { renderHook, act } from '@testing-library/react';
import { usePushNotifications } from '@/hooks/use-push-notifications';

class MockNotification {
  static permission: NotificationPermission = 'default';
  static requestPermission: () => Promise<NotificationPermission> = vi
    .fn()
    .mockResolvedValue('granted' as NotificationPermission);
  public title: string;
  public options: any;
  public onclick?: (e: any) => void;
  constructor(title: string, options: any) {
    this.title = title;
    this.options = options;
  }
  close = vi.fn();
}

function setup(permission: NotificationPermission = 'default') {
  MockNotification.permission = permission;
  return renderHook(() => usePushNotifications({ notificationImpl: MockNotification as any }));
}

describe('usePushNotifications (DI)', () => {
  it('reports support when implementation injected', () => {
    const { result } = setup();
    expect(result.current.isSupported).toBe(true);
    expect(result.current.permission).toBe('default');
  });

  it('requests permission and updates to granted', async () => {
    const { result } = setup('default');
    await act(async () => {
      const granted = await result.current.requestPermission();
      expect(granted).toBe(true);
    });
    expect(result.current.permission).toBe('granted');
  });

  it('returns false when permission denied', async () => {
    (MockNotification.requestPermission as any).mockResolvedValueOnce('denied');
    const { result } = setup('default');
    await act(async () => {
      const granted = await result.current.requestPermission();
      expect(granted).toBe(false);
    });
    expect(result.current.permission).toBe('denied');
  });

  it('creates a notification when permission already granted', async () => {
    const ctor = vi.fn();
    class CtorNotification extends MockNotification {
      constructor(title: string, options: any) {
        super(title, options);
        ctor(title);
      }
    }
    const { result } = renderHook(() => usePushNotifications({ notificationImpl: CtorNotification as any }));
    await act(async () => {
      await result.current.showNotification({ title: 'Hello', body: 'World' });
    });
    expect(ctor).toHaveBeenCalledWith('Hello');
  });
});
