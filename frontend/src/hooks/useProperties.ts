import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import api from '@/lib/api'
import { Property, SearchParams, PaginatedResponse, ApiResponse } from '@/types'

export const useProperties = (params?: SearchParams & { page?: number }) => {
  return useQuery({
    queryKey: ['properties', params],
    queryFn: async () => {
      const { data } = await api.get<ApiResponse<PaginatedResponse<Property>>>('/properties', {
        params
      })
      return data.data
    },
  })
}

export const useProperty = (id: string) => {
  return useQuery({
    queryKey: ['property', id],
    queryFn: async () => {
      const { data } = await api.get<ApiResponse<Property>>(`/properties/${id}`)
      return data.data
    },
    enabled: !!id,
  })
}

export const useFeaturedProperties = () => {
  return useQuery({
    queryKey: ['properties', 'featured'],
    queryFn: async () => {
      const { data } = await api.get<ApiResponse<Property[]>>('/properties/featured')
      return data.data
    },
  })
}

export const useSearchProperties = (params: SearchParams) => {
  return useQuery({
    queryKey: ['properties', 'search', params],
    queryFn: async () => {
      const { data } = await api.get<ApiResponse<PaginatedResponse<Property>>>('/properties/search', {
        params
      })
      return data.data
    },
    enabled: !!(params.location || params.check_in),
  })
}

export const useCreateProperty = () => {
  const queryClient = useQueryClient()
  
  return useMutation({
    mutationFn: async (propertyData: Partial<Property>) => {
      const { data } = await api.post<ApiResponse<Property>>('/properties', propertyData)
      return data.data
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['properties'] })
    },
  })
}

export const useUpdateProperty = () => {
  const queryClient = useQueryClient()
  
  return useMutation({
    mutationFn: async ({ id, ...propertyData }: Partial<Property> & { id: number }) => {
      const { data } = await api.put<ApiResponse<Property>>(`/properties/${id}`, propertyData)
      return data.data
    },
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['properties'] })
      queryClient.invalidateQueries({ queryKey: ['property', data.id.toString()] })
    },
  })
}

export const useDeleteProperty = () => {
  const queryClient = useQueryClient()
  
  return useMutation({
    mutationFn: async (id: number) => {
      await api.delete(`/properties/${id}`)
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['properties'] })
    },
  })
}