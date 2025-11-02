import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '@/lib/api'
import { User, ApiResponse } from '@/types'

interface LoginCredentials {
  email: string
  password: string
}

interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  role?: 'guest' | 'owner'
}

interface AuthResponse {
  user: User
  token: string
}

export const useLogin = () => {
  return useMutation({
    mutationFn: async (credentials: LoginCredentials) => {
      const { data } = await api.post<ApiResponse<AuthResponse>>('/login', credentials)
      
      // Store token and user in localStorage
      if (typeof window !== 'undefined') {
        localStorage.setItem('auth_token', data.data.token)
        localStorage.setItem('user', JSON.stringify(data.data.user))
      }
      
      return data.data
    },
  })
}

export const useRegister = () => {
  return useMutation({
    mutationFn: async (userData: RegisterData) => {
      const { data } = await api.post<ApiResponse<AuthResponse>>('/register', userData)
      
      // Store token and user in localStorage
      if (typeof window !== 'undefined') {
        localStorage.setItem('auth_token', data.data.token)
        localStorage.setItem('user', JSON.stringify(data.data.user))
      }
      
      return data.data
    },
  })
}

export const useLogout = () => {
  const queryClient = useQueryClient()
  
  return useMutation({
    mutationFn: async () => {
      await api.post('/logout')
    },
    onSettled: () => {
      // Clear local storage
      if (typeof window !== 'undefined') {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
      }
      
      // Clear all queries
      queryClient.clear()
    },
  })
}

export const useUser = () => {
  return useQuery({
    queryKey: ['user'],
    queryFn: async () => {
      const { data } = await api.get<ApiResponse<User>>('/user')
      return data.data
    },
    enabled: typeof window !== 'undefined' && !!localStorage.getItem('auth_token'),
  })
}

export const useUpdateProfile = () => {
  const queryClient = useQueryClient()
  
  return useMutation({
    mutationFn: async (userData: Partial<User>) => {
      const { data } = await api.put<ApiResponse<User>>('/user', userData)
      
      // Update user in localStorage
      if (typeof window !== 'undefined') {
        localStorage.setItem('user', JSON.stringify(data.data))
      }
      
      return data.data
    },
    onSuccess: (data) => {
      queryClient.setQueryData(['user'], data)
    },
  })
}

// Custom hook to get current user from localStorage
export const useCurrentUser = () => {
  if (typeof window === 'undefined') return null
  const userString = localStorage.getItem('user')
  return userString ? JSON.parse(userString) as User : null
}

// Custom hook to check if user is authenticated
export const useIsAuthenticated = () => {
  if (typeof window === 'undefined') return false
  return !!localStorage.getItem('auth_token')
}

// Main auth hook that provides all authentication functionality
export const useAuth = () => {
  const loginMutation = useLogin()
  const registerMutation = useRegister()
  const logoutMutation = useLogout()
  const updateProfileMutation = useUpdateProfile()
  const userQuery = useUser()
  
  const user = useCurrentUser()
  const isAuthenticated = useIsAuthenticated()
  
  const login = (credentials: LoginCredentials) => {
    return loginMutation.mutateAsync(credentials)
  }
  
  const register = (userData: RegisterData) => {
    return registerMutation.mutateAsync(userData)
  }
  
  const logout = () => {
    return logoutMutation.mutateAsync()
  }
  
  const updateProfile = (userData: Partial<User>) => {
    return updateProfileMutation.mutateAsync(userData)
  }
  
  return {
    user,
    isAuthenticated,
    login,
    register,
    logout,
    updateProfile,
    isLoading: loginMutation.isPending || registerMutation.isPending || logoutMutation.isPending,
    error: loginMutation.error || registerMutation.error || logoutMutation.error || userQuery.error
  }
}