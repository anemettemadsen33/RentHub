import { Property } from '@/types'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
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
    <Card className="group overflow-hidden border-2 hover:border-primary/50 transition-all hover:shadow-xl">
      <div className="relative aspect-[4/3] overflow-hidden">
        <Image
          src={property.images?.[0] || '/placeholder-property.jpg'}
          alt={property.title}
          fill
          className="object-cover group-hover:scale-110 transition-transform duration-500"
        />
        <div className="absolute top-3 right-3">
          <Badge variant="secondary" className="bg-background/95 backdrop-blur-sm font-semibold">
            {formatPrice(property.price_per_night)}/noapte
          </Badge>
        </div>
        {averageRating > 0 && (
          <div className="absolute top-3 left-3">
            <Badge className="bg-primary/90 backdrop-blur-sm">
              <Star className="h-3 w-3 fill-white mr-1" />
              {averageRating.toFixed(1)}
            </Badge>
          </div>
        )}
      </div>

      <CardContent className="p-5">
        <h3 className="font-bold text-lg mb-2 line-clamp-1 group-hover:text-primary transition-colors">
          {property.title}
        </h3>

        <div className="flex items-center gap-1.5 text-muted-foreground mb-3">
          <MapPin className="h-4 w-4 flex-shrink-0" />
          <span className="text-sm truncate">
            {property.city}, {property.state}, {property.country}
          </span>
        </div>

        <p className="text-muted-foreground text-sm line-clamp-2 mb-4 leading-relaxed">
          {property.description}
        </p>

        <div className="flex items-center gap-4 text-sm text-muted-foreground border-t pt-3">
          <div className="flex items-center gap-1.5">
            <Bed className="h-4 w-4" />
            <span className="font-medium">{property.bedrooms}</span>
          </div>
          <div className="flex items-center gap-1.5">
            <Bath className="h-4 w-4" />
            <span className="font-medium">{property.bathrooms}</span>
          </div>
          {property.area_sqm && (
            <div className="flex items-center gap-1.5">
              <Square className="h-4 w-4" />
              <span className="font-medium">{property.area_sqm}m²</span>
            </div>
          )}
        </div>
      </CardContent>

      <CardFooter className="p-5 pt-0">
        <Link href={`/properties/${property.id}`} className="w-full">
          <Button className="w-full" size="lg">
            Vezi detalii
          </Button>
        </Link>
      </CardFooter>
    </Card>
  )
}