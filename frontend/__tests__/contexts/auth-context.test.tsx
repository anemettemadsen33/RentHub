import { render, screen, waitFor } from '@testing-library/react';
import { vi, describe, it, expect, beforeEach } from 'vitest';
import userEvent from '@testing-library/user-event';
import { AuthProvider, useAuth } from '@/contexts/auth-context';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';

// Mock component to test useAuth hook
function TestComponent() {
  const { user, login, logout, isLoading } = useAuth();
  
  return (
    <div>
      {isLoading && <div>Loading...</div>}
      {user ? (
        <>
          <div>Logged in as: {user.email}</div>
          <button onClick={logout}>Logout</button>
        </>
      ) : (
        <div>Not logged in</div>
      )}
    </div>
  );
}

describe('AuthContext', () => {
  let queryClient: QueryClient;

  beforeEach(() => {
    queryClient = new QueryClient({
      defaultOptions: {
        queries: { retry: false },
        mutations: { retry: false },
      },
    });
    localStorage.clear();
    vi.clearAllMocks();
  });

  it('should render children correctly', () => {
    render(
      <QueryClientProvider client={queryClient}>
        <AuthProvider>
          <div>Test Child</div>
        </AuthProvider>
      </QueryClientProvider>
    );

    expect(screen.getByText('Test Child')).toBeInTheDocument();
  });

  it('should show not logged in state initially', () => {
    render(
      <QueryClientProvider client={queryClient}>
        <AuthProvider>
          <TestComponent />
        </AuthProvider>
      </QueryClientProvider>
    );

    expect(screen.getByText('Not logged in')).toBeInTheDocument();
  });

  it('should restore user from localStorage', async () => {
    const mockUser = {
      id: 1,
      name: 'Test User',
      email: 'test@example.com',
    };

    localStorage.setItem('user', JSON.stringify(mockUser));

    render(
      <QueryClientProvider client={queryClient}>
        <AuthProvider>
          <TestComponent />
        </AuthProvider>
      </QueryClientProvider>
    );

    await waitFor(() => {
      expect(screen.getByText(`Logged in as: ${mockUser.email}`)).toBeInTheDocument();
    });
  });
});
