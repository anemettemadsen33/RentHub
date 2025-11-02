import { Property } from '@/types'
import { Card, CardContent, CardFooter } from '@/components/ui/Card'
import { Button } from '@/components/ui/Button'
import { MapPin, Bed, Bath, Square, Star } from 'lucide-react'
import Image from 'next/image'
import Link from 'next/link'

interface PropertyCardProps {
  property: Property
}

export const PropertyCard = ({ property }: PropertyCardProps) => {
  const averageRating = property.average_rating || 0

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ro-RO', {
      style: 'currency',
      currency: 'RON'
    }).format(price)
  }

  return (
    <Card className="group hover:shadow-lg transition-shadow duration-300">
      <div className="relative aspect-[4/3] overflow-hidden rounded-t-lg">
        <Image
          src={property.images?.[0] || '/placeholder-property.jpg'}
          alt={property.title}
          fill
          className="object-cover group-hover:scale-105 transition-transform duration-300"
        />
        <div className="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-full px-2 py-1">
          <span className="text-sm font-medium text-gray-900">
            {formatPrice(property.price_per_night)}/noapte
          </span>
        </div>
      </div>

      <CardContent className="p-4">
        <div className="flex items-start justify-between mb-2">
          <h3 className="font-semibold text-lg text-gray-900 line-clamp-1">
            {property.title}
          </h3>
          {averageRating > 0 && (
            <div className="flex items-center gap-1 text-sm">
              <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
              <span className="font-medium">{averageRating.toFixed(1)}</span>
            </div>
          )}
        </div>

        <div className="flex items-center gap-1 text-gray-600 mb-3">
          <MapPin className="h-4 w-4" />
          <span className="text-sm truncate">
            {property.city}, {property.state}, {property.country}
          </span>
        </div>

        <p className="text-gray-600 text-sm line-clamp-2 mb-4">
          {property.description}
        </p>

        <div className="flex items-center gap-4 text-sm text-gray-500">
          <div className="flex items-center gap-1">
            <Bed className="h-4 w-4" />
            <span>{property.bedrooms}</span>
          </div>
          <div className="flex items-center gap-1">
            <Bath className="h-4 w-4" />
            <span>{property.bathrooms}</span>
          </div>
          {property.area_sqm && (
            <div className="flex items-center gap-1">
              <Square className="h-4 w-4" />
              <span>{property.area_sqm}m²</span>
            </div>
          )}
        </div>
      </CardContent>

      <CardFooter className="p-4 pt-0">
        <Link href={`/properties/${property.id}`} className="w-full">
          <Button className="w-full">
            Vezi detalii
          </Button>
        </Link>
      </CardFooter>
    </Card>
  )
}