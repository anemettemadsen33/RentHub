import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { useAuth } from '@/hooks/useAuth'
import { useCreateBooking, useCheckAvailability } from '@/hooks/useBookings'
import { Property } from '@/types'
import { Calendar, Users, CreditCard, CheckCircle, AlertCircle } from 'lucide-react'

interface BookingFormProps {
  property: Property
  onClose?: () => void
}

export const BookingForm = ({ property, onClose }: BookingFormProps) => {
  const { user, isAuthenticated } = useAuth()
  const [formData, setFormData] = useState({
    check_in: '',
    check_out: '',
    guests: 1,
    guest_name: user?.name || '',
    guest_email: user?.email || '',
    guest_phone: '',
    special_requests: ''
  })
  const [step, setStep] = useState<'dates' | 'details' | 'payment' | 'confirmation'>('dates')
  const [errors, setErrors] = useState<{[key: string]: string}>({})

  const createBookingMutation = useCreateBooking()
  const checkAvailabilityMutation = useCheckAvailability()

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ro-RO', {
      style: 'currency',
      currency: 'RON'
    }).format(price)
  }

  const calculateNights = () => {
    if (!formData.check_in || !formData.check_out) return 0
    const start = new Date(formData.check_in)
    const end = new Date(formData.check_out)
    const timeDiff = end.getTime() - start.getTime()
    return Math.ceil(timeDiff / (1000 * 3600 * 24))
  }

  const calculateTotal = () => {
    const nights = calculateNights()
    const subtotal = nights * property.price_per_night
    const cleaningFee = property.cleaning_fee || 0
    const taxes = subtotal * 0.09 // 9% taxe
    return {
      nights,
      subtotal,
      cleaningFee,
      taxes,
      total: subtotal + cleaningFee + taxes
    }
  }

  const handleInputChange = (field: string, value: string | number) => {
    setFormData(prev => ({ ...prev, [field]: value }))
    if (errors[field]) {
      setErrors(prev => ({ ...prev, [field]: '' }))
    }
  }

  const validateDates = () => {
    const newErrors: {[key: string]: string} = {}
    
    if (!formData.check_in) {
      newErrors.check_in = 'Data de check-in este obligatorie'
    }
    
    if (!formData.check_out) {
      newErrors.check_out = 'Data de check-out este obligatorie'
    }
    
    if (formData.check_in && formData.check_out) {
      const checkIn = new Date(formData.check_in)
      const checkOut = new Date(formData.check_out)
      const today = new Date()
      today.setHours(0, 0, 0, 0)
      
      if (checkIn < today) {
        newErrors.check_in = 'Data de check-in nu poate fi în trecut'
      }
      
      if (checkOut <= checkIn) {
        newErrors.check_out = 'Data de check-out trebuie să fie după check-in'
      }
    }
    
    if (formData.guests < 1 || formData.guests > property.guests) {
      newErrors.guests = `Numărul de oaspeți trebuie să fie între 1 și ${property.guests}`
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleCheckAvailability = async () => {
    if (!validateDates()) return

    try {
      const result = await checkAvailabilityMutation.mutateAsync({
        property_id: property.id,
        check_in: formData.check_in,
        check_out: formData.check_out
      })

      if (result.available) {
        setStep('details')
      } else {
        setErrors({ dates: 'Proprietatea nu este disponibilă în perioada selectată' })
      }
    } catch (error) {
      setErrors({ dates: 'Eroare la verificarea disponibilității' })
    }
  }

  const handleSubmitBooking = async () => {
    if (!isAuthenticated) {
      setErrors({ auth: 'Trebuie să fii conectat pentru a face o rezervare' })
      return
    }

    try {
      const booking = await createBookingMutation.mutateAsync({
        property_id: property.id,
        ...formData
      })
      
      setStep('confirmation')
    } catch (error: any) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors)
      } else {
        setErrors({ general: 'Eroare la crearea rezervării' })
      }
    }
  }

  const pricing = calculateTotal()

  if (step === 'confirmation') {
    return (
      <Card>
        <CardContent className="p-6 text-center">
          <CheckCircle className="h-16 w-16 text-green-500 mx-auto mb-4" />
          <h3 className="text-2xl font-bold text-gray-900 mb-2">Rezervarea a fost confirmată!</h3>
          <p className="text-gray-600 mb-6">
            Vei primi un email de confirmare în curând cu toate detaliile.
          </p>
          <Button onClick={onClose} className="w-full">
            Închide
          </Button>
        </CardContent>
      </Card>
    )
  }

  return (
    <div className="space-y-6">
      {/* Progress Steps */}
      <div className="flex items-center justify-center space-x-4">
        {['dates', 'details', 'payment'].map((stepName, index) => (
          <div key={stepName} className="flex items-center">
            <div className={`w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium ${
              step === stepName ? 'bg-blue-600 text-white' : 
              ['dates', 'details', 'payment'].indexOf(step) > index ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600'
            }`}>
              {index + 1}
            </div>
            {index < 2 && <div className="w-12 h-0.5 bg-gray-200 mx-2"></div>}
          </div>
        ))}
      </div>

      {/* Step 1: Dates */}
      {step === 'dates' && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calendar className="h-5 w-5" />
              Selectează datele
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            {errors.dates && (
              <div className="bg-red-50 border border-red-200 text-red-600 p-3 rounded-md">
                {errors.dates}
              </div>
            )}

            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="check_in">Check-in</Label>
                <Input
                  id="check_in"
                  type="date"
                  value={formData.check_in}
                  onChange={(e) => handleInputChange('check_in', e.target.value)}
                  min={new Date().toISOString().split('T')[0]}
                />
                {errors.check_in && <p className="text-sm text-destructive">{errors.check_in}</p>}
              </div>
              <div className="space-y-2">
                <Label htmlFor="check_out">Check-out</Label>
                <Input
                  id="check_out"
                  type="date"
                  value={formData.check_out}
                  onChange={(e) => handleInputChange('check_out', e.target.value)}
                  min={formData.check_in || new Date().toISOString().split('T')[0]}
                />
                {errors.check_out && <p className="text-sm text-destructive">{errors.check_out}</p>}
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="guests">Numărul de oaspeți</Label>
              <Input
                id="guests"
                type="number"
                min="1"
                max={property.guests.toString()}
                value={formData.guests.toString()}
                onChange={(e) => handleInputChange('guests', parseInt(e.target.value) || 1)}
              />
              {errors.guests && <p className="text-sm text-destructive">{errors.guests}</p>}
            </div>

            {pricing.nights > 0 && (
              <div className="bg-gray-50 p-4 rounded-lg">
                <div className="text-sm space-y-2">
                  <div className="flex justify-between">
                    <span>{formatPrice(property.price_per_night)} × {pricing.nights} nopți</span>
                    <span>{formatPrice(pricing.subtotal)}</span>
                  </div>
                  <div className="flex justify-between font-medium text-lg">
                    <span>Total estimat</span>
                    <span>{formatPrice(pricing.total)}</span>
                  </div>
                </div>
              </div>
            )}

            <Button 
              className="w-full" 
              onClick={handleCheckAvailability}
              disabled={checkAvailabilityMutation.isPending}
            >
              {checkAvailabilityMutation.isPending ? 'Se verifică...' : 'Verifică disponibilitatea'}
            </Button>
          </CardContent>
        </Card>
      )}

      {/* Step 2: Guest Details */}
      {step === 'details' && (
        <Card>
          <CardHeader>
            <CardTitle>Detaliile oaspeților</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="guest_name">Numele complet</Label>
              <Input
                id="guest_name"
                value={formData.guest_name}
                onChange={(e) => handleInputChange('guest_name', e.target.value)}
                required
              />
              {errors.guest_name && <p className="text-sm text-destructive">{errors.guest_name}</p>}
            </div>

            <div className="space-y-2">
              <Label htmlFor="guest_email">Email</Label>
              <Input
                id="guest_email"
                type="email"
                value={formData.guest_email}
                onChange={(e) => handleInputChange('guest_email', e.target.value)}
                required
              />
              {errors.guest_email && <p className="text-sm text-destructive">{errors.guest_email}</p>}
            </div>

            <div className="space-y-2">
              <Label htmlFor="guest_phone">Telefon (opțional)</Label>
              <Input
                id="guest_phone"
                type="tel"
                value={formData.guest_phone}
                onChange={(e) => handleInputChange('guest_phone', e.target.value)}
              />
              {errors.guest_phone && <p className="text-sm text-destructive">{errors.guest_phone}</p>}
            </div>

            <div className="space-y-2">
              <Label htmlFor="special_requests">Cereri speciale (opțional)</Label>
              <textarea
                id="special_requests"
                className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                placeholder="Ex: check-in târziu, preferințe pentru camera..."
                value={formData.special_requests}
                onChange={(e) => handleInputChange('special_requests', e.target.value)}
              />
            </div>

            <div className="flex gap-3">
              <Button variant="outline" onClick={() => setStep('dates')} className="flex-1">
                Înapoi
              </Button>
              <Button onClick={() => setStep('payment')} className="flex-1">
                Continuă
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Step 3: Payment */}
      {step === 'payment' && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <CreditCard className="h-5 w-5" />
              Confirmă și plătește
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            {/* Booking Summary */}
            <div className="bg-gray-50 p-4 rounded-lg">
              <h4 className="font-medium mb-3">Rezumat rezervare</h4>
              <div className="space-y-2 text-sm">
                <div className="flex justify-between">
                  <span>Check-in</span>
                  <span>{formData.check_in}</span>
                </div>
                <div className="flex justify-between">
                  <span>Check-out</span>
                  <span>{formData.check_out}</span>
                </div>
                <div className="flex justify-between">
                  <span>Oaspeți</span>
                  <span>{formData.guests}</span>
                </div>
                <hr className="my-2" />
                <div className="flex justify-between">
                  <span>{formatPrice(property.price_per_night)} × {pricing.nights} nopți</span>
                  <span>{formatPrice(pricing.subtotal)}</span>
                </div>
                {pricing.cleaningFee > 0 && (
                  <div className="flex justify-between">
                    <span>Taxă curățenie</span>
                    <span>{formatPrice(pricing.cleaningFee)}</span>
                  </div>
                )}
                <div className="flex justify-between">
                  <span>Taxe</span>
                  <span>{formatPrice(pricing.taxes)}</span>
                </div>
                <hr className="my-2" />
                <div className="flex justify-between font-medium text-lg">
                  <span>Total</span>
                  <span>{formatPrice(pricing.total)}</span>
                </div>
              </div>
            </div>

            {/* Payment Note */}
            <div className="bg-blue-50 border border-blue-200 p-4 rounded-md">
              <div className="flex items-start gap-3">
                <AlertCircle className="h-5 w-5 text-blue-600 mt-0.5" />
                <div className="text-sm text-blue-800">
                  <p className="font-medium mb-1">Sistem de plată în dezvoltare</p>
                  <p>Momentan rezervarea va fi confirmată fără plată. Integrarea cu sistemul de plăți va fi adăugată în curând.</p>
                </div>
              </div>
            </div>

            {errors.general && (
              <div className="bg-red-50 border border-red-200 text-red-600 p-3 rounded-md">
                {errors.general}
              </div>
            )}

            {errors.auth && (
              <div className="bg-red-50 border border-red-200 text-red-600 p-3 rounded-md">
                {errors.auth}
              </div>
            )}

            <div className="flex gap-3">
              <Button variant="outline" onClick={() => setStep('details')} className="flex-1">
                Înapoi
              </Button>
              <Button 
                onClick={handleSubmitBooking} 
                disabled={createBookingMutation.isPending}
                className="flex-1"
              >
                {createBookingMutation.isPending ? 'Se procesează...' : 'Confirmă rezervarea'}
              </Button>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  )
}