import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { MapPin, Search, Users, Calendar } from 'lucide-react'

interface SearchFormProps {
  onSearch: (searchParams: SearchParams) => void
  className?: string
}

export interface SearchParams {
  location?: string
  checkIn?: string
  checkOut?: string
  guests?: number
  priceMin?: number
  priceMax?: number
}

export const SearchForm = ({ onSearch, className = '' }: SearchFormProps) => {
  const [formData, setFormData] = useState<SearchParams>({
    location: '',
    checkIn: '',
    checkOut: '',
    guests: 1,
    priceMin: undefined,
    priceMax: undefined
  })

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    onSearch(formData)
  }

  const handleInputChange = (field: keyof SearchParams, value: string | number | undefined) => {
    setFormData(prev => ({
      ...prev,
      [field]: value
    }))
  }

  return (
    <form onSubmit={handleSubmit} className={`bg-card rounded-lg shadow-lg p-6 border ${className}`}>
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        {/* Location */}
        <div className="lg:col-span-2">
          <div className="relative">
            <MapPin className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input
              placeholder="Unde vrei să mergi?"
              value={formData.location}
              onChange={(e) => handleInputChange('location', e.target.value)}
              className="pl-10"
            />
          </div>
        </div>

        {/* Check-in */}
        <div>
          <div className="relative">
            <Calendar className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
            <Input
              type="date"
              placeholder="Check-in"
              value={formData.checkIn}
              onChange={(e) => handleInputChange('checkIn', e.target.value)}
              className="pl-10"
            />
          </div>
        </div>

        {/* Check-out */}
        <div>
          <div className="relative">
            <Calendar className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
            <Input
              type="date"
              placeholder="Check-out"
              value={formData.checkOut}
              onChange={(e) => handleInputChange('checkOut', e.target.value)}
              className="pl-10"
            />
          </div>
        </div>

        {/* Guests */}
        <div className="flex gap-2">
          <div className="relative flex-1">
            <Users className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
            <Input
              type="number"
              min="1"
              max="20"
              placeholder="Oaspeți"
              value={formData.guests?.toString() || ''}
              onChange={(e) => handleInputChange('guests', parseInt(e.target.value) || 1)}
              className="pl-10"
            />
          </div>
        </div>
      </div>

      {/* Advanced Filters */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        <div className="space-y-2">
          <Label htmlFor="priceMin">
            Preț minim (RON/noapte)
          </Label>
          <Input
            id="priceMin"
            type="number"
            min="0"
            placeholder="0"
            value={formData.priceMin?.toString() || ''}
            onChange={(e) => handleInputChange('priceMin', e.target.value ? parseInt(e.target.value) : undefined)}
          />
        </div>

        <div className="space-y-2">
          <Label htmlFor="priceMax">
            Preț maxim (RON/noapte)
          </Label>
          <Input
            id="priceMax"
            type="number"
            min="0"
            placeholder="1000"
            value={formData.priceMax?.toString() || ''}
            onChange={(e) => handleInputChange('priceMax', e.target.value ? parseInt(e.target.value) : undefined)}
          />
        </div>
      </div>

      {/* Search Button */}
      <div className="mt-6">
        <Button type="submit" className="w-full md:w-auto">
          <Search className="h-4 w-4 mr-2" />
          Caută proprietăți
        </Button>
      </div>
    </form>
  )
}