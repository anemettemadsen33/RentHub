'use client';

import { useEffect, useState } from 'react';
import { useParams } from 'next/navigation';
import Image from 'next/image';
import Link from 'next/link';
import {
  MapPin as MapPinIcon,
  Heart as HeartIcon,
  Share as ShareIcon,
  Star as StarIcon,
  Calendar as CalendarIcon,
  Users as UsersIcon,
  Home as HomeIcon,
  CheckCircle as CheckCircleIcon,
} from 'lucide-react';
import { Heart as HeartSolidIcon } from 'lucide-react';

interface Property {
  id: number;
  title: string;
  description: string;
  price: number;
  address: string;
  city: string;
  latitude: number | null;
  longitude: number | null;
  bedrooms: number;
  bathrooms: number;
  square_feet: number | null;
  property_type: string;
  furnishing_status: string | null;
  floor_number: number | null;
  parking_available: boolean;
  images: Array<{ id: number; image_url: string; is_featured: boolean }>;
  amenities: Array<{ id: number; name: string; icon: string }>;
  reviews: Array<{
    id: number;
    rating: number;
    comment: string;
    user: { name: string };
    created_at: string;
  }>;
  average_rating: number;
  reviews_count: number;
  owner: {
    name: string;
    email: string;
  };
}

export default function PropertyDetailPage() {
  const params = useParams();
  const propertyId = params?.id as string;
  
  const [property, setProperty] = useState<Property | null>(null);
  const [similarProperties, setSimilarProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedImage, setSelectedImage] = useState(0);
  const [isLightboxOpen, setIsLightboxOpen] = useState(false);
  const [isFavorite, setIsFavorite] = useState(false);
  const [checkIn, setCheckIn] = useState('');
  const [checkOut, setCheckOut] = useState('');
  const [guests, setGuests] = useState(1);

  useEffect(() => {
    const fetchProperty = async () => {
      try {
        const apiUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';
        const response = await fetch(`${apiUrl}/api/properties/${propertyId}`);
        const data = await response.json();
        setProperty(data);
        
        // Fetch similar properties
        const similarResponse = await fetch(`${apiUrl}/api/properties?type=${data.property_type}&limit=3`);
        const similarData = await similarResponse.json();
        setSimilarProperties(similarData.data.filter((p: Property) => p.id !== data.id));
      } catch (error) {
        console.error('Failed to fetch property:', error);
      } finally {
        setLoading(false);
      }
    };

    if (propertyId) {
      fetchProperty();
    }
  }, [propertyId]);

  const handleShare = () => {
    if (navigator.share) {
      navigator.share({
        title: property?.title,
        text: property?.description,
        url: window.location.href,
      });
    } else {
      navigator.clipboard.writeText(window.location.href);
      alert('Link copied to clipboard!');
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (!property) {
    return (
      <div className="flex items-center justify-center min-h-screen">
        <p className="text-xl text-gray-600">Property not found</p>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Image Gallery */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div className="grid grid-cols-4 gap-2 h-[500px]">
          <div
            className="col-span-2 row-span-2 relative rounded-lg overflow-hidden cursor-pointer"
            onClick={() => {
              setSelectedImage(0);
              setIsLightboxOpen(true);
            }}
          >
            <Image
              src={property.images[0]?.image_url || '/placeholder.jpg'}
              alt={property.title}
              fill
              className="object-cover hover:scale-105 transition-transform duration-300"
            />
          </div>
          {property.images.slice(1, 5).map((image, index) => (
            <div
              key={image.id}
              className="relative rounded-lg overflow-hidden cursor-pointer"
              onClick={() => {
                setSelectedImage(index + 1);
                setIsLightboxOpen(true);
              }}
            >
              <Image
                src={image.image_url}
                alt={`${property.title} - ${index + 2}`}
                fill
                className="object-cover hover:scale-105 transition-transform duration-300"
              />
            </div>
          ))}
          {property.images.length > 5 && (
            <button
              onClick={() => setIsLightboxOpen(true)}
              className="absolute bottom-4 right-4 bg-white px-4 py-2 rounded-lg shadow-md hover:bg-gray-50 transition-colors"
            >
              Show all photos
            </button>
          )}
        </div>

        {/* Lightbox */}
        {isLightboxOpen && (
          <div className="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center">
            <button
              onClick={() => setIsLightboxOpen(false)}
              className="absolute top-4 right-4 text-white text-4xl hover:text-gray-300"
            >
              ×
            </button>
            <button
              onClick={() => setSelectedImage((prev) => (prev > 0 ? prev - 1 : property.images.length - 1))}
              className="absolute left-4 text-white text-4xl hover:text-gray-300"
            >
              ‹
            </button>
            <div className="relative w-full h-full max-w-5xl max-h-[90vh] flex items-center justify-center p-8">
              <Image
                src={property.images[selectedImage]?.image_url}
                alt={property.title}
                fill
                className="object-contain"
              />
            </div>
            <button
              onClick={() => setSelectedImage((prev) => (prev < property.images.length - 1 ? prev + 1 : 0))}
              className="absolute right-4 text-white text-4xl hover:text-gray-300"
            >
              ›
            </button>
          </div>
        )}

        {/* Property Details */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
          <div className="lg:col-span-2">
            {/* Header */}
            <div className="bg-white rounded-lg shadow-sm p-6">
              <div className="flex justify-between items-start">
                <div>
                  <h1 className="text-3xl font-bold text-gray-900">{property.title}</h1>
                  <div className="flex items-center mt-2 text-gray-600">
                    <MapPinIcon className="h-5 w-5 mr-1" />
                    <span>{property.address}, {property.city}</span>
                  </div>
                  <div className="flex items-center mt-2">
                    <StarIcon className="h-5 w-5 text-yellow-400 fill-current" />
                    <span className="ml-1 font-semibold">{property.average_rating?.toFixed(1) || 'New'}</span>
                    <span className="ml-1 text-gray-600">({property.reviews_count} reviews)</span>
                  </div>
                </div>
                <div className="flex gap-2">
                  <button
                    onClick={() => setIsFavorite(!isFavorite)}
                    className="p-2 rounded-full hover:bg-gray-100 transition-colors"
                  >
                    {isFavorite ? (
                      <HeartSolidIcon className="h-6 w-6 text-red-500" />
                    ) : (
                      <HeartIcon className="h-6 w-6 text-gray-600" />
                    )}
                  </button>
                  <button
                    onClick={handleShare}
                    className="p-2 rounded-full hover:bg-gray-100 transition-colors"
                  >
                    <ShareIcon className="h-6 w-6 text-gray-600" />
                  </button>
                </div>
              </div>

              {/* Property Info */}
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t">
                <div className="text-center">
                  <HomeIcon className="h-6 w-6 mx-auto text-gray-600" />
                  <p className="mt-2 font-semibold">{property.bedrooms} Bedrooms</p>
                </div>
                <div className="text-center">
                  <HomeIcon className="h-6 w-6 mx-auto text-gray-600" />
                  <p className="mt-2 font-semibold">{property.bathrooms} Bathrooms</p>
                </div>
                <div className="text-center">
                  <HomeIcon className="h-6 w-6 mx-auto text-gray-600" />
                  <p className="mt-2 font-semibold">{property.square_feet} sq ft</p>
                </div>
                <div className="text-center">
                  <UsersIcon className="h-6 w-6 mx-auto text-gray-600" />
                  <p className="mt-2 font-semibold">{property.property_type}</p>
                </div>
              </div>

              {/* Description */}
              <div className="mt-6 pt-6 border-t">
                <h2 className="text-2xl font-bold mb-4">About this place</h2>
                <p className="text-gray-700 leading-relaxed">{property.description}</p>
              </div>

              {/* Additional Info */}
              <div className="mt-6 pt-6 border-t grid grid-cols-2 gap-4">
                {property.furnishing_status && (
                  <div>
                    <p className="text-gray-600">Furnishing</p>
                    <p className="font-semibold">{property.furnishing_status}</p>
                  </div>
                )}
                {property.floor_number && (
                  <div>
                    <p className="text-gray-600">Floor</p>
                    <p className="font-semibold">{property.floor_number}</p>
                  </div>
                )}
                <div>
                  <p className="text-gray-600">Parking</p>
                  <p className="font-semibold">{property.parking_available ? 'Available' : 'Not Available'}</p>
                </div>
              </div>
            </div>

            {/* Amenities */}
            <div className="bg-white rounded-lg shadow-sm p-6 mt-6">
              <h2 className="text-2xl font-bold mb-4">Amenities</h2>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                {property.amenities.map((amenity) => (
                  <div key={amenity.id} className="flex items-center gap-2">
                    <CheckCircleIcon className="h-5 w-5 text-green-500" />
                    <span>{amenity.name}</span>
                  </div>
                ))}
              </div>
            </div>

            {/* Location Map */}
            {property.latitude && property.longitude && (
              <div className="bg-white rounded-lg shadow-sm p-6 mt-6">
                <h2 className="text-2xl font-bold mb-4">Location</h2>
                <div className="h-[400px] bg-gray-200 rounded-lg overflow-hidden">
                  <iframe
                    width="100%"
                    height="100%"
                    frameBorder="0"
                    style={{ border: 0 }}
                    src={`https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=${property.latitude},${property.longitude}`}
                    allowFullScreen
                  />
                </div>
              </div>
            )}

            {/* Reviews */}
            <div className="bg-white rounded-lg shadow-sm p-6 mt-6">
              <h2 className="text-2xl font-bold mb-4">Reviews</h2>
              {property.reviews.length > 0 ? (
                <div className="space-y-4">
                  {property.reviews.map((review) => (
                    <div key={review.id} className="border-b pb-4 last:border-b-0">
                      <div className="flex items-center justify-between mb-2">
                        <div>
                          <p className="font-semibold">{review.user.name}</p>
                          <p className="text-sm text-gray-500">
                            {new Date(review.created_at).toLocaleDateString()}
                          </p>
                        </div>
                        <div className="flex items-center">
                          <StarIcon className="h-5 w-5 text-yellow-400 fill-current" />
                          <span className="ml-1 font-semibold">{review.rating}</span>
                        </div>
                      </div>
                      <p className="text-gray-700">{review.comment}</p>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-gray-500">No reviews yet</p>
              )}
            </div>
          </div>

          {/* Booking Card */}
          <div className="lg:col-span-1">
            <div className="bg-white rounded-lg shadow-lg p-6 sticky top-6">
              <div className="flex items-center justify-between mb-4">
                <div>
                  <span className="text-3xl font-bold">${property.price}</span>
                  <span className="text-gray-600"> / night</span>
                </div>
              </div>

              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Check-in</label>
                  <input
                    type="date"
                    value={checkIn}
                    onChange={(e) => setCheckIn(e.target.value)}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Check-out</label>
                  <input
                    type="date"
                    value={checkOut}
                    onChange={(e) => setCheckOut(e.target.value)}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div>
                  <label htmlFor="guests-select" className="block text-sm font-medium text-gray-700 mb-1">Guests</label>
                  <select
                    id="guests-select"
                    value={guests}
                    onChange={(e) => setGuests(Number(e.target.value))}
                    className="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    {[1, 2, 3, 4, 5, 6, 7, 8].map((num) => (
                      <option key={num} value={num}>
                        {num} {num === 1 ? 'guest' : 'guests'}
                      </option>
                    ))}
                  </select>
                </div>

                <button className="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                  Reserve
                </button>

                <p className="text-center text-sm text-gray-500">You won't be charged yet</p>
              </div>

              <div className="mt-6 pt-6 border-t">
                <h3 className="font-semibold mb-2">Contact Host</h3>
                <p className="text-sm text-gray-600">{property.owner.name}</p>
                <p className="text-sm text-gray-600">{property.owner.email}</p>
              </div>
            </div>
          </div>
        </div>

        {/* Similar Properties */}
        {similarProperties.length > 0 && (
          <div className="mt-12">
            <h2 className="text-2xl font-bold mb-6">Similar Properties</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {similarProperties.map((similar) => (
                <Link key={similar.id} href={`/properties/${similar.id}`}>
                  <div className="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow cursor-pointer">
                    <div className="relative h-48">
                      <Image
                        src={similar.images[0]?.image_url || '/placeholder.jpg'}
                        alt={similar.title}
                        fill
                        className="object-cover"
                      />
                    </div>
                    <div className="p-4">
                      <h3 className="font-semibold text-lg mb-1">{similar.title}</h3>
                      <p className="text-gray-600 text-sm mb-2">{similar.city}</p>
                      <div className="flex items-center justify-between">
                        <span className="font-bold text-lg">${similar.price}/night</span>
                        <div className="flex items-center">
                          <StarIcon className="h-4 w-4 text-yellow-400 fill-current" />
                          <span className="ml-1 text-sm">{similar.average_rating?.toFixed(1) || 'New'}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
