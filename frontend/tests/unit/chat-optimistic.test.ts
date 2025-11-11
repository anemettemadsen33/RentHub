import { describe, it, expect, vi, beforeEach } from 'vitest';
import { renderHook, act } from '@testing-library/react';
import type { Message } from '@/types/extended';

// Hoisted mock: define factory returning plain objects only (no class reference before init)
const emitSpy = vi.fn();
const handlers: Record<string, Function[]> = {};
function register(event: string, cb: Function) {
  handlers[event] = handlers[event] || [];
  handlers[event].push(cb);
}
function trigger(event: string, payload?: any) {
  (handlers[event] || []).forEach(fn => fn(payload));
}
vi.mock('socket.io-client', () => {
  return {
    io: vi.fn(() => ({
      emit: emitSpy,
      on: register,
      disconnect: vi.fn(),
    })),
  };
});

import { useChat } from '@/hooks/use-chat';

function makeMessage(partial: Partial<Message>): Message {
  return {
    id: partial.id ?? 1,
    client_id: partial.client_id,
    status: (partial as any).status,
    conversation_id: partial.conversation_id ?? 10,
    sender_id: partial.sender_id ?? 5,
    recipient_id: partial.recipient_id ?? 6,
    message: partial.message ?? 'Hello',
    read: partial.read ?? false,
    created_at: partial.created_at ?? new Date().toISOString(),
    attachments: (partial as any).attachments,
  } as Message;
}

describe('useChat optimistic basics', () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it('emits send-message when emitSendMessage called with saved message', () => {
  const { result } = renderHook(() => useChat({ userId: 5, activeConversationId: 10 }));
    const saved = makeMessage({ id: 99, conversation_id: 10, sender_id: 5, message: 'Persisted', read: false });
    act(() => {
      result.current.emitSendMessage(10, saved);
    });
    // Ensure our mock socket emit was used
  expect(emitSpy).toHaveBeenCalledWith('send-message', { conversationId: 10, message: saved });
  });

  it('subscribes to new-message and routes to onMessage when active conversation matches', () => {
    const onMessage = vi.fn();
    const { result } = renderHook(() =>
      useChat({ userId: 5, activeConversationId: 10, onMessage })
    );
    // Access mocked socket
    const incoming = makeMessage({ id: 42, conversation_id: 10, message: 'Incoming' });
    act(() => {
      trigger('new-message', incoming);
    });
    expect(onMessage).toHaveBeenCalledWith(incoming);
  });

  it('routes background messages to onBackgroundMessage when conversation differs', () => {
    const onBackgroundMessage = vi.fn();
    const { result } = renderHook(() =>
      useChat({ userId: 5, activeConversationId: 10, onBackgroundMessage })
    );
    const background = makeMessage({ id: 77, conversation_id: 11, message: 'Other convo' });
    act(() => {
      trigger('new-message', background);
    });
    expect(onBackgroundMessage).toHaveBeenCalledWith(background);
  });

  it('throttles typing events (~1200ms)', () => {
    const { result } = renderHook(() =>
      useChat({ userId: 5, activeConversationId: 10 })
    );
    act(() => {
      result.current.emitTyping();
      result.current.emitTyping();
    });
    // Only first should emit
    expect(emitSpy).toHaveBeenCalledTimes(1);
  });
});
