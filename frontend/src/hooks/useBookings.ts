import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { api } from '@/lib/api'
import { Property, Booking, ApiResponse } from '@/types'

// Hook pentru obținerea detaliilor unei proprietăți
export const useProperty = (id: string | number) => {
  return useQuery({
    queryKey: ['property', id],
    queryFn: async () => {
      const response = await api.get<ApiResponse<Property>>(`/properties/${id}`)
      return response.data.data
    },
    enabled: !!id
  })
}

// Hook pentru crearea unei rezervări
export const useCreateBooking = () => {
  const queryClient = useQueryClient()
  
  return useMutation({
    mutationFn: async (bookingData: {
      property_id: number
      check_in: string
      check_out: string
      guests: number
      guest_name: string
      guest_email: string
      guest_phone?: string
      special_requests?: string
    }) => {
      const response = await api.post<ApiResponse<Booking>>('/bookings', bookingData)
      return response.data.data
    },
    onSuccess: () => {
      // Invalidează cache-ul pentru rezervări
      queryClient.invalidateQueries({ queryKey: ['bookings'] })
    }
  })
}

// Hook pentru obținerea rezervărilor utilizatorului
export const useUserBookings = () => {
  return useQuery({
    queryKey: ['user-bookings'],
    queryFn: async () => {
      const response = await api.get<ApiResponse<Booking[]>>('/user/bookings')
      return response.data.data
    }
  })
}

// Hook pentru verificarea disponibilității
export const useCheckAvailability = () => {
  return useMutation({
    mutationFn: async (data: {
      property_id: number
      check_in: string
      check_out: string
    }) => {
      const response = await api.post<ApiResponse<{ available: boolean, conflicting_bookings?: Booking[] }>>(
        '/properties/check-availability', 
        data
      )
      return response.data.data
    }
  })
}