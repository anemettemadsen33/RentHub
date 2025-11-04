'use client';

import React, { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import { authApi, User, AuthResponse } from '@/lib/api/auth';
import { useRouter } from 'next/navigation';

interface AuthContextType {
  user: User | null;
  loading: boolean;
  login: (email: string, password: string) => Promise<AuthResponse>;
  register: (data: any) => Promise<AuthResponse>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
  isAuthenticated: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);
  const router = useRouter();

  useEffect(() => {
    checkAuth();
  }, []);

  const checkAuth = async () => {
    try {
      const token = localStorage.getItem('auth_token');
      if (token) {
        const response = await authApi.me();
        if (response.data.success && response.data.data) {
          setUser(response.data.data);
        }
      }
    } catch (error) {
      console.error('Auth check failed:', error);
      localStorage.removeItem('auth_token');
    } finally {
      setLoading(false);
    }
  };

  const login = async (email: string, password: string): Promise<AuthResponse> => {
    const response = await authApi.login({ email, password });
    
    if (response.data.success && response.data.data) {
      localStorage.setItem('auth_token', response.data.data.token);
      setUser(response.data.data.user);
    }
    
    return response.data;
  };

  const register = async (data: any): Promise<AuthResponse> => {
    const response = await authApi.register(data);
    
    if (response.data.success && response.data.data) {
      localStorage.setItem('auth_token', response.data.data.token);
      setUser(response.data.data.user);
    }
    
    return response.data;
  };

  const logout = async () => {
    try {
      await authApi.logout();
    } catch (error) {
      console.error('Logout failed:', error);
    } finally {
      localStorage.removeItem('auth_token');
      setUser(null);
      router.push('/auth/login');
    }
  };

  const refreshUser = async () => {
    try {
      const response = await authApi.me();
      if (response.data.success && response.data.data) {
        setUser(response.data.data);
      }
    } catch (error) {
      console.error('Failed to refresh user:', error);
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        loading,
        login,
        register,
        logout,
        refreshUser,
        isAuthenticated: !!user,
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
