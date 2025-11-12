'use client';

import React, { createContext, useContext, useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { toast } from '@/hooks/use-toast';
import { authService, type User } from '@/lib/api-service';
import { createLogger } from '@/lib/logger'

;

const authLogger = createLogger('AuthContext');

interface AuthContextType {
  user: User | null;
  login: (email: string, password: string) => Promise<void>;
  register: (name: string, email: string, password: string, passwordConfirmation: string, role?: 'tenant' | 'owner') => Promise<void>;
  logout: () => Promise<void>;
  isAuthenticated: boolean;
  isLoading: boolean;
  loading: boolean; // Alias for isLoading
  refreshUser: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    // Check if user is logged in on mount
    const token = localStorage.getItem('auth_token');
    const storedUser = localStorage.getItem('user');
    
    if (token && storedUser) {
      try {
        setUser(JSON.parse(storedUser));
        // Optionally refresh user data from server
        refreshUserSilently();
      } catch (e) {
        authLogger.error('Failed to parse stored user', e);
        localStorage.removeItem('user');
      }
    }
    setIsLoading(false);
  }, []);

  const refreshUserSilently = async () => {
    try {
      const userData = await authService.me();
      setUser(userData);
      localStorage.setItem('user', JSON.stringify(userData));
      authLogger.debug('User data refreshed silently');
    } catch (error) {
      // Silent fail - token might be expired
      authLogger.debug('Failed to refresh user (silent)', { error });
    }
  };

  const refreshUser = async () => {
    try {
      const userData = await authService.me();
      setUser(userData);
      localStorage.setItem('user', JSON.stringify(userData));
    } catch (error: any) {
      toast({
        title: 'Error',
        description: 'Failed to refresh user data',
        variant: 'destructive',
      });
      throw error;
    }
  };

  const login = async (email: string, password: string) => {
    try {
      const response = await authService.login({ email, password });
      setUser(response.user);
      
      toast({
        title: 'Success',
        description: 'Logged in successfully',
      });
      
      router.push('/dashboard');
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Login failed',
        variant: 'destructive',
      });
      throw error;
    }
  };

  const register = async (name: string, email: string, password: string, passwordConfirmation: string, role?: 'tenant' | 'owner') => {
    try {
      console.log('[AuthContext] register() invoked', { email });
      const response = await authService.register({
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
        role,
      });
      console.log('[AuthContext] register() response received', { status: 'ok', userId: response.user?.id });
      
      setUser(response.user);
      
      toast({
        title: 'Success',
        description: 'Account created successfully',
      });
      
      router.push('/dashboard');
    } catch (error: any) {
      console.error('[AuthContext] register() error', { message: error?.message, status: error?.response?.status });
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Registration failed',
        variant: 'destructive',
      });
      throw error;
    }
  };

  const logout = async () => {
    try {
      await authService.logout();
      authLogger.info('User logged out successfully');
    } catch (error) {
      authLogger.error('Logout error', error);
    } finally {
      setUser(null);
      router.push('/');
      
      toast({
        title: 'Success',
        description: 'Logged out successfully',
      });
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        login,
        register,
        logout,
        isAuthenticated: !!user,
        isLoading,
        loading: isLoading, // Alias
        refreshUser,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
