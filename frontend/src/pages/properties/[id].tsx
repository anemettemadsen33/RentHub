import { useState } from 'react'
import { useRouter } from 'next/router'
import Head from 'next/head'
import Image from 'next/image'
import { ReactElement } from 'react'
import { Layout } from '@/components/Layout'
import { Button } from '@/components/ui/Button'
import { Card, CardContent } from '@/components/ui/Card'
import { Modal } from '@/components/ui/Modal'
import { BookingForm } from '@/components/BookingForm'
import { useQuery } from '@tanstack/react-query'
import { api } from '@/lib/api'
import { Property, ApiResponse } from '@/types'
import { 
  MapPin, 
  Users, 
  Bed, 
  Bath, 
  Square, 
  Star, 
  Calendar, 
  Share2,
  Heart,
  ArrowLeft,
  CheckCircle,
  Wifi,
  Car,
  Snowflake,
  ChefHat
} from 'lucide-react'
import Link from 'next/link'

// Component pentru galeria foto
const ImageGallery = ({ images, title }: { images: string[], title: string }) => {
  const [currentImage, setCurrentImage] = useState(0)
  const [isModalOpen, setIsModalOpen] = useState(false)

  const placeholderImages = [
    '/placeholder-property-1.jpg',
    '/placeholder-property-2.jpg',
    '/placeholder-property-3.jpg',
    '/placeholder-property-4.jpg',
  ]

  const displayImages = images && images.length > 0 ? images : placeholderImages

  return (
    <>
      <div className="grid grid-cols-4 gap-2 h-96">
        {/* Imaginea principală */}
        <div 
          className="col-span-2 row-span-2 relative cursor-pointer rounded-lg overflow-hidden"
          onClick={() => setIsModalOpen(true)}
        >
          <Image
            src={displayImages[0]}
            alt={title}
            fill
            className="object-cover hover:scale-105 transition-transform duration-300"
          />
        </div>

        {/* Imaginile secundare */}
        {displayImages.slice(1, 5).map((image, index) => (
          <div 
            key={index}
            className="relative cursor-pointer rounded-lg overflow-hidden"
            onClick={() => {
              setCurrentImage(index + 1)
              setIsModalOpen(true)
            }}
          >
            <Image
              src={image}
              alt={`${title} - ${index + 2}`}
              fill
              className="object-cover hover:scale-105 transition-transform duration-300"
            />
            {index === 3 && displayImages.length > 5 && (
              <div className="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                <span className="text-white font-semibold">
                  +{displayImages.length - 4} poze
                </span>
              </div>
            )}
          </div>
        ))}
      </div>

      {/* Modal pentru galerie */}
      <Modal
        isOpen={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        size="xl"
        title="Galerie foto"
      >
        <div className="space-y-4">
          <div className="relative h-96">
            <Image
              src={displayImages[currentImage]}
              alt={`${title} - ${currentImage + 1}`}
              fill
              className="object-contain"
            />
          </div>
          
          <div className="flex justify-center space-x-2 overflow-x-auto pb-2">
            {displayImages.map((image, index) => (
              <button
                key={index}
                onClick={() => setCurrentImage(index)}
                className={`flex-shrink-0 relative w-16 h-16 rounded-lg overflow-hidden border-2 transition-colors ${
                  currentImage === index ? 'border-blue-500' : 'border-gray-200'
                }`}
              >
                <Image
                  src={image}
                  alt={`${title} thumbnail ${index + 1}`}
                  fill
                  className="object-cover"
                />
              </button>
            ))}
          </div>
        </div>
      </Modal>
    </>
  )
}

// Component pentru amenități
const AmenityIcon = ({ slug }: { slug: string }) => {
  const icons: { [key: string]: ReactElement } = {
    wifi: <Wifi className="h-5 w-5" />,
    parking: <Car className="h-5 w-5" />,
    'air-conditioning': <Snowflake className="h-5 w-5" />,
    kitchen: <ChefHat className="h-5 w-5" />
  }
  
  return icons[slug] || <CheckCircle className="h-5 w-5" />
}

export default function PropertyDetailPage() {
  const router = useRouter()
  const { id } = router.query
  const [isBookingModalOpen, setIsBookingModalOpen] = useState(false)

  // Fetch property details
  const { data: property, isLoading, error } = useQuery({
    queryKey: ['property', id],
    queryFn: async () => {
      const response = await api.get<ApiResponse<Property>>(`/properties/${id}`)
      return response.data.data
    },
    enabled: !!id
  })

  if (isLoading) {
    return (
      <Layout>
        <div className="min-h-screen bg-gray-50 flex items-center justify-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
      </Layout>
    )
  }

  if (error || !property) {
    return (
      <Layout>
        <div className="min-h-screen bg-gray-50 flex items-center justify-center">
          <div className="text-center">
            <h1 className="text-2xl font-bold text-gray-900 mb-2">Proprietatea nu a fost găsită</h1>
            <p className="text-gray-600 mb-4">Ne pare rău, nu am putut găsi proprietatea căutată.</p>
            <Link href="/properties">
              <Button>
                <ArrowLeft className="h-4 w-4 mr-2" />
                Înapoi la proprietăți
              </Button>
            </Link>
          </div>
        </div>
      </Layout>
    )
  }

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ro-RO', {
      style: 'currency',
      currency: 'RON'
    }).format(price)
  }

  return (
    <Layout>
      <Head>
        <title>{property.title} - RentHub</title>
        <meta name="description" content={property.description} />
      </Head>

      <div className="min-h-screen bg-gray-50">
        {/* Breadcrumb */}
        <div className="bg-white border-b">
          <div className="container mx-auto px-4 py-4">
            <div className="flex items-center gap-2 text-sm text-gray-600">
              <Link href="/" className="hover:text-blue-600">Acasă</Link>
              <span>/</span>
              <Link href="/properties" className="hover:text-blue-600">Proprietăți</Link>
              <span>/</span>
              <span className="text-gray-900">{property.title}</span>
            </div>
          </div>
        </div>

        <div className="container mx-auto px-4 py-8">
          {/* Header */}
          <div className="flex justify-between items-start mb-6">
            <div>
              <h1 className="text-3xl font-bold text-gray-900 mb-2">{property.title}</h1>
              <div className="flex items-center gap-4 text-gray-600">
                <div className="flex items-center gap-1">
                  <MapPin className="h-4 w-4" />
                  <span>{property.city}, {property.state}, {property.country}</span>
                </div>
                {property.average_rating && (
                  <div className="flex items-center gap-1">
                    <Star className="h-4 w-4 fill-yellow-400 text-yellow-400" />
                    <span className="font-medium">{property.average_rating.toFixed(1)}</span>
                    <span>({property.reviews_count} recenzii)</span>
                  </div>
                )}
              </div>
            </div>
            
            <div className="flex items-center gap-2">
              <Button variant="outline" size="sm">
                <Share2 className="h-4 w-4 mr-2" />
                Partajează
              </Button>
              <Button variant="outline" size="sm">
                <Heart className="h-4 w-4 mr-2" />
                Salvează
              </Button>
            </div>
          </div>

          {/* Image Gallery */}
          <ImageGallery images={property.images || []} title={property.title} />

          {/* Main Content */}
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
            {/* Left Column */}
            <div className="lg:col-span-2 space-y-8">
              {/* Property Info */}
              <Card>
                <CardContent className="p-6">
                  <div className="flex items-center justify-between mb-4">
                    <h2 className="text-xl font-semibold">Detalii proprietate</h2>
                    <span className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                      {property.type}
                    </span>
                  </div>
                  
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div className="flex items-center gap-2">
                      <Users className="h-5 w-5 text-gray-400" />
                      <span>{property.guests} oaspeți</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Bed className="h-5 w-5 text-gray-400" />
                      <span>{property.bedrooms} dormitoare</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Bath className="h-5 w-5 text-gray-400" />
                      <span>{property.bathrooms} băi</span>
                    </div>
                    {property.area_sqm && (
                      <div className="flex items-center gap-2">
                        <Square className="h-5 w-5 text-gray-400" />
                        <span>{property.area_sqm}m²</span>
                      </div>
                    )}
                  </div>

                  <p className="text-gray-700 leading-relaxed">{property.description}</p>
                </CardContent>
              </Card>

              {/* Amenities */}
              {property.amenities && property.amenities.length > 0 && (
                <Card>
                  <CardContent className="p-6">
                    <h2 className="text-xl font-semibold mb-4">Facilități</h2>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                      {property.amenities.map((amenity) => (
                        <div key={amenity.id} className="flex items-center gap-3">
                          <AmenityIcon slug={amenity.slug} />
                          <span>{amenity.name}</span>
                        </div>
                      ))}
                    </div>
                  </CardContent>
                </Card>
              )}

              {/* Location */}
              <Card>
                <CardContent className="p-6">
                  <h2 className="text-xl font-semibold mb-4">Locație</h2>
                  <div className="space-y-2 text-gray-700">
                    <p><strong>Adresa:</strong> {property.street_address}</p>
                    <p><strong>Oraș:</strong> {property.city}, {property.state}</p>
                    <p><strong>Țară:</strong> {property.country}</p>
                    {property.postal_code && (
                      <p><strong>Cod poștal:</strong> {property.postal_code}</p>
                    )}
                  </div>
                  
                  {/* Placeholder pentru hartă */}
                  <div className="mt-4 h-64 bg-gray-200 rounded-lg flex items-center justify-center">
                    <p className="text-gray-500">Hartă - va fi implementată</p>
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Right Column - Booking Card */}
            <div className="lg:col-span-1">
              <Card className="sticky top-4">
                <CardContent className="p-6">
                  <div className="text-center mb-6">
                    <div className="text-3xl font-bold text-gray-900">
                      {formatPrice(property.price_per_night)}
                    </div>
                    <div className="text-gray-600">pe noapte</div>
                  </div>

                  {property.cleaning_fee && (
                    <div className="text-sm text-gray-600 mb-4">
                      Taxă de curățenie: {formatPrice(property.cleaning_fee)}
                    </div>
                  )}

                  <Button 
                    className="w-full mb-4"
                    onClick={() => setIsBookingModalOpen(true)}
                  >
                    <Calendar className="h-4 w-4 mr-2" />
                    Rezervă acum
                  </Button>

                  <div className="text-xs text-gray-500 text-center">
                    Nu vei fi taxat încă
                  </div>

                  {/* Calculul prețului */}
                  <div className="border-t pt-4 mt-4 space-y-2 text-sm">
                    <div className="flex justify-between">
                      <span>Preț pe noapte</span>
                      <span>{formatPrice(property.price_per_night)}</span>
                    </div>
                    {property.cleaning_fee && (
                      <div className="flex justify-between">
                        <span>Taxă curățenie</span>
                        <span>{formatPrice(property.cleaning_fee)}</span>
                      </div>
                    )}
                    <div className="border-t pt-2 font-medium">
                      <div className="flex justify-between">
                        <span>Total</span>
                        <span>{formatPrice(property.price_per_night + (property.cleaning_fee || 0))}</span>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </div>
        </div>

        {/* Booking Modal */}
        <Modal
          isOpen={isBookingModalOpen}
          onClose={() => setIsBookingModalOpen(false)}
          title="Rezervă proprietatea"
          size="xl"
        >
          <BookingForm 
            property={property} 
            onClose={() => setIsBookingModalOpen(false)} 
          />
        </Modal>
      </div>
    </Layout>
  )
}