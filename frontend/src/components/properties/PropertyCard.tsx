'use client';

import Link from 'next/link';
import { Property } from '@/lib/api/properties';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { MapPin, Bed, Bath, Users, Star, Home } from 'lucide-react';

interface PropertyCardProps {
  property: Property;
}

export default function PropertyCard({ property }: PropertyCardProps) {
  const mainImage = property.main_image || property.images?.[0];
  const hasImages = mainImage || property.images?.length;

  return (
    <Link href={`/properties/${property.id}`}>
      <Card className="group overflow-hidden border-2 hover:border-primary/50 transition-all hover:shadow-xl cursor-pointer h-full">
        {/* Property Image */}
        <div className="relative h-56 bg-muted overflow-hidden">
          {hasImages ? (
            <img
              src={mainImage}
              alt={property.title}
              className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
            />
          ) : (
            <div className="flex items-center justify-center h-full text-muted-foreground">
              <Home className="w-16 h-16" />
            </div>
          )}
          
          {/* Featured Badge */}
          {property.is_featured && (
            <div className="absolute top-3 left-3">
              <Badge className="bg-yellow-500 hover:bg-yellow-600">
                ⭐ Featured
              </Badge>
            </div>
          )}

          {/* Rating Badge */}
          {property.average_rating && (
            <div className="absolute top-3 right-3">
              <Badge variant="secondary" className="bg-background/95 backdrop-blur-sm">
                <Star className="h-3 w-3 fill-yellow-400 text-yellow-400 mr-1" />
                {property.average_rating.toFixed(1)}
                <span className="text-xs ml-1">({property.reviews_count})</span>
              </Badge>
            </div>
          )}
        </div>

        {/* Property Details */}
        <CardContent className="p-5">
          {/* Title */}
          <h3 className="text-lg font-bold mb-2 line-clamp-1 group-hover:text-primary transition-colors">
            {property.title}
          </h3>

          {/* Location */}
          <div className="flex items-center gap-1.5 text-muted-foreground mb-3">
            <MapPin className="h-4 w-4 flex-shrink-0" />
            <span className="text-sm truncate">
              {property.city}, {property.country}
            </span>
          </div>

          {/* Property Specs */}
          <div className="flex items-center gap-4 text-sm text-muted-foreground mb-4">
            <div className="flex items-center gap-1.5">
              <Bed className="h-4 w-4" />
              <span className="font-medium">{property.bedrooms}</span>
            </div>
            <div className="flex items-center gap-1.5">
              <Bath className="h-4 w-4" />
              <span className="font-medium">{property.bathrooms}</span>
            </div>
            <div className="flex items-center gap-1.5">
              <Users className="h-4 w-4" />
              <span className="font-medium">{property.guests}</span>
            </div>
          </div>

          {/* Property Type */}
          <div className="flex gap-2 mb-4">
            <Badge variant="secondary">
              {property.type.charAt(0).toUpperCase() + property.type.slice(1)}
            </Badge>
            {property.furnishing_status && (
              <Badge variant="outline">
                {property.furnishing_status}
              </Badge>
            )}
          </div>
        </CardContent>

        {/* Price */}
        <CardFooter className="p-5 pt-0 flex items-center justify-between border-t">
          <div>
            <span className="text-2xl font-bold text-primary">
              ${property.price_per_night}
            </span>
            <span className="text-sm text-muted-foreground"> /night</span>
          </div>
          <Button size="sm" className="group-hover:bg-primary group-hover:text-primary-foreground">
            View Details
          </Button>
        </CardFooter>
      </Card>
    </Link>
  );
}
