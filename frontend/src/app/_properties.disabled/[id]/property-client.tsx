"use client";
import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import Image from 'next/image';
import apiClient from '@/lib/api-client';
import { Property } from '@/types';
import { useAuth } from '@/contexts/auth-context';
import { useToast } from '@/hooks/use-toast';
import { referralService, loyaltyService } from '@/lib/api-service';
import { useConversionTracking } from '@/hooks/use-conversion-tracking';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  MapPin,
  Users,
  Bed,
  Bath,
  Home,
  Star,
  Calendar,
  CheckCircle,
  Wifi,
  Car,
  Tv,
  Wind,
  Coffee,
  Waves,
} from 'lucide-react';
import { Loader2 } from 'lucide-react';
import { PropertyDetailsSkeleton } from '@/components/skeletons';

interface Props { id: string }

export default function PropertyDetailClient({ id }: Props) {
  const router = useRouter();
  const { user } = useAuth();
  const { toast } = useToast();
  const [property, setProperty] = useState<Property | null>(null);
  const [loading, setLoading] = useState(true);
  const [selectedImage, setSelectedImage] = useState(0);
  const [checkIn, setCheckIn] = useState('');
  const [checkOut, setCheckOut] = useState('');
  const [guests, setGuests] = useState('1');
  const [booking, setBooking] = useState(false);
  const [touchStart, setTouchStart] = useState<number | null>(null);
  const [touchEnd, setTouchEnd] = useState<number | null>(null);
  const [referralCode, setReferralCode] = useState('');
  const [referralApplied, setReferralApplied] = useState<{ valid: boolean; discount_percent?: number; discount_amount?: number } | null>(null);
  const [loyaltyApplied, setLoyaltyApplied] = useState<{ discount_amount: number } | null>(null);
  const [applyingReferral, setApplyingReferral] = useState(false);
  const [applyingLoyalty, setApplyingLoyalty] = useState(false);
  const { trackBookingSubmit } = useConversionTracking();

  useEffect(() => {
    fetchProperty();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [id]);

  const fetchProperty = async () => {
    try {
      const { data } = await apiClient.get(`/properties/${id}`);
      setProperty(data.data);
    } catch (error) {
      toast({ title: 'Error', description: 'Failed to load property details', variant: 'destructive' });
      router.push('/properties');
    } finally {
      setLoading(false);
    }
  };

  const handleBooking = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!user) {
      toast({ title: 'Authentication Required', description: 'Please login to book this property' });
      router.push('/auth/login');
      return;
    }
    setBooking(true);
    try {
      await apiClient.post('/bookings', {
        property_id: id,
        check_in: checkIn,
        check_out: checkOut,
        guests: parseInt(guests),
        referral_code: referralApplied?.valid ? referralCode : undefined,
        apply_loyalty: !!loyaltyApplied,
      });
      try {
        const nights = calculateNights();
        const subtotal = nights * (property?.price_per_night || 0);
        const referral = referralApplied?.valid ? (referralApplied.discount_percent ? (subtotal * (referralApplied.discount_percent / 100)) : (referralApplied.discount_amount || 0)) : 0;
        const loyalty = loyaltyApplied?.discount_amount || 0;
        const total = Math.max(0, subtotal - referral - loyalty);
        sessionStorage.setItem('last_booking_discounts', JSON.stringify({ subtotal, referral, loyalty, total }));
        // Fire conversion event (booking submitted)
        trackBookingSubmit({
          propertyId: id,
          checkIn,
          checkOut,
          guests: parseInt(guests),
          nights,
          total,
          discounts: { referral, loyalty, total },
        });
      } catch {}
      toast({ title: 'Success', description: 'Booking request submitted successfully!' });
      router.push('/bookings?confirmed=1');
    } catch (error: any) {
      toast({ title: 'Error', description: error.response?.data?.message || 'Failed to create booking', variant: 'destructive' });
    } finally {
      setBooking(false);
    }
  };

  const calculateNights = () => {
    if (!checkIn || !checkOut) return 0;
    const start = new Date(checkIn);
    const end = new Date(checkOut);
    const diff = end.getTime() - start.getTime();
    return Math.ceil(diff / (1000 * 60 * 60 * 24));
  };

  const calculateTotal = () => {
    const nights = calculateNights();
    const subtotal = nights * (property?.price_per_night || 0);
    let total = subtotal;
    if (referralApplied?.discount_percent) total = total * (1 - (referralApplied.discount_percent / 100));
    if (referralApplied?.discount_amount) total = Math.max(0, total - referralApplied.discount_amount);
    if (loyaltyApplied?.discount_amount) total = Math.max(0, total - loyaltyApplied.discount_amount);
    return Math.round(total * 100) / 100;
  };

  const amenityIcons: { [key: string]: any } = { wifi: Wifi, parking: Car, tv: Tv, ac: Wind, kitchen: Coffee, pool: Waves };

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-8">
        <PropertyDetailsSkeleton />
      </div>
    );
  }

  if (!property) {
    return (
      <div className="container mx-auto px-4 py-8">
        <p>Property not found</p>
      </div>
    );
  }

  const images = property.images || [
    'https://images.unsplash.com/photo-1568605114967-8130f3a36994',
    'https://images.unsplash.com/photo-1564013799919-ab600027ffc6',
    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688',
  ];

  const minSwipeDistance = 50;
  const onTouchStart = (e: React.TouchEvent) => { setTouchEnd(null); setTouchStart(e.targetTouches[0].clientX); };
  const onTouchMove = (e: React.TouchEvent) => { setTouchEnd(e.targetTouches[0].clientX); };
  const onTouchEnd = () => {
    if (!touchStart || !touchEnd) return;
    const distance = touchStart - touchEnd;
    const isLeftSwipe = distance > minSwipeDistance;
    const isRightSwipe = distance < -minSwipeDistance;
    if (isLeftSwipe && selectedImage < images.length - 1) setSelectedImage(selectedImage + 1);
    else if (isRightSwipe && selectedImage > 0) setSelectedImage(selectedImage - 1);
  };

  return (
    <div className="container mx-auto px-4 py-8">
  {/* Image Gallery */}
  <div className="mb-8" role="region" aria-label="Property image gallery">
        <div
          className="relative h-96 rounded-lg overflow-hidden mb-4 touch-none bg-gray-100"
          onTouchStart={onTouchStart}
          onTouchMove={onTouchMove}
          onTouchEnd={onTouchEnd}
        >
          <Image
            src={images[selectedImage]}
            alt={property.title}
            fill
            className="object-cover"
            priority
            sizes="(max-width: 1024px) 100vw, 70vw"
            placeholder="blur"
            blurDataURL="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0nMzAwJyBoZWlnaHQ9JzIwMCcgeG1sbnM9J2h0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnJz48cmVjdCB3aWR0aD0nMzAwJyBoZWlnaHQ9JzIwMCcgZmlsbD0nI2U2ZTZlNicvPjwvc3ZnPg=="
          />
          {selectedImage > 0 && (
            <button
              className="absolute left-2 top-1/2 -translate-y-1/2 bg-white/70 hover:bg-white text-gray-900 rounded-full p-2 shadow"
              onClick={() => setSelectedImage(selectedImage - 1)}
              aria-label="Previous image"
            >
              ◀
            </button>
          )}
          {selectedImage < images.length - 1 && (
            <button
              className="absolute right-2 top-1/2 -translate-y-1/2 bg-white/70 hover:bg-white text-gray-900 rounded-full p-2 shadow"
              onClick={() => setSelectedImage(selectedImage + 1)}
              aria-label="Next image"
            >
              ▶
            </button>
          )}
        </div>
        <div className="grid grid-cols-6 gap-2">
          {images.map((img, idx) => (
            <button
              key={img + idx}
              onClick={() => setSelectedImage(idx)}
              className={`relative h-20 rounded overflow-hidden ${selectedImage === idx ? 'ring-2 ring-blue-500' : ''}`}
              aria-label={`Select image ${idx + 1}`}
            >
              <Image 
                src={img} 
                alt={property.title + ' thumbnail'} 
                fill 
                className="object-cover"
                sizes="(max-width: 640px) 16vw, (max-width: 1024px) 10vw, 8vw"
                loading={idx < 4 ? 'eager' : 'lazy'}
              />
            </button>
          ))}
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Primary Details */}
        <div className="lg:col-span-2 space-y-6">
          <h1 className="text-3xl font-bold flex items-center gap-2">
            <Home className="h-8 w-8 text-blue-600" /> {property.title}
          </h1>
          <p className="text-gray-600 flex items-center gap-2"><MapPin className="h-5 w-5" /> {property.city}, {property.country}</p>

          {/* Stats */}
          <div className="flex flex-wrap gap-4 text-sm text-gray-700">
            <span className="flex items-center gap-1"><Bed className="h-4 w-4" /> {property.bedrooms} bedrooms</span>
            <span className="flex items-center gap-1"><Bath className="h-4 w-4" /> {property.bathrooms} bathrooms</span>
            <span className="flex items-center gap-1"><Star className="h-4 w-4 text-yellow-500" /> {property.rating ?? 'N/A'} rating</span>
          </div>

          {/* Description */}
          <div>
            <h2 className="text-xl font-semibold mb-2">Description</h2>
            <p className="text-gray-700 leading-relaxed whitespace-pre-line">{property.description}</p>
          </div>

            {/* Amenities */}
          {Array.isArray(property.amenities) && property.amenities.length > 0 && (
            <div>
              <h2 className="text-xl font-semibold mb-2">Amenities</h2>
              <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                {property.amenities.map((a: any) => {
                  const name = typeof a === 'string' ? a : a?.name;
                  if (!name) return null;
                  const key = name.toLowerCase();
                  const Icon = amenityIcons[key] || CheckCircle;
                  return (
                    <div key={key} className="flex items-center gap-2 p-2 rounded border bg-white shadow-sm">
                      <Icon className="h-5 w-5 text-blue-600" /> <span className="capitalize">{name}</span>
                    </div>
                  );
                })}
              </div>
            </div>
          )}
        </div>

        {/* Booking Card */}
        <div className="space-y-6">
          <Card className="sticky top-4">
            <CardHeader>
              <CardTitle className="flex items-center justify-between">
                <span>${property.price_per_night} <span className="text-sm font-normal text-gray-500">/ night</span></span>
                {/* Placeholder for verification badge (property.is_verified not in type) */}
              </CardTitle>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleBooking} className="space-y-4" aria-label="Booking form">
                <div className="grid grid-cols-2 gap-4">
                  <div>
                    <Label htmlFor="checkIn">Check In</Label>
                    <Input id="checkIn" type="date" value={checkIn} onChange={(e) => setCheckIn(e.target.value)} required />
                  </div>
                  <div>
                    <Label htmlFor="checkOut">Check Out</Label>
                    <Input id="checkOut" type="date" value={checkOut} onChange={(e) => setCheckOut(e.target.value)} required />
                  </div>
                </div>
                <div>
                  <Label htmlFor="guests">Guests</Label>
                  <Input id="guests" type="number" min={1} value={guests} onChange={(e) => setGuests(e.target.value)} required />
                </div>

                {/* Discounts */}
                <div className="space-y-3">
                  <div>
                    <Label htmlFor="referral">Referral Code</Label>
                    <div className="flex gap-2">
                      <Input id="referral" value={referralCode} onChange={(e) => setReferralCode(e.target.value)} placeholder="Enter code" />
                      <Button
                        type="button"
                        variant="secondary"
                        disabled={applyingReferral || !referralCode}
                        onClick={async () => {
                          setApplyingReferral(true);
                          try {
                            const res = await referralService.validate({ code: referralCode });
                            setReferralApplied(res);
                            toast({ title: 'Referral Applied', description: 'Discount will be reflected in total.' });
                          } catch {
                            toast({ title: 'Invalid Referral', description: 'Code could not be validated', variant: 'destructive' });
                            setReferralApplied(null);
                          } finally { setApplyingReferral(false); }
                        }}
                        aria-label="Apply referral code"
                      >
                        {applyingReferral ? (
                          <span className="inline-flex items-center gap-2"><Loader2 className="h-4 w-4 animate-spin" /> Applying…</span>
                        ) : (
                          'Apply'
                        )}
                      </Button>
                    </div>
                    {referralApplied?.valid && (
                      <p className="text-xs text-green-600 mt-1">Referral applied: {referralApplied.discount_percent ? `${referralApplied.discount_percent}%` : `$${referralApplied.discount_amount}`}</p>
                    )}
                  </div>
                  <div>
                    <Label>Loyalty Discount</Label>
                    <Button
                      type="button"
                      variant="secondary"
                      disabled={applyingLoyalty || !!loyaltyApplied}
                      onClick={async () => {
                        setApplyingLoyalty(true);
                        try {
                          const nights = calculateNights();
                          const subtotal = nights * (property?.price_per_night || 0);
                          const res = await loyaltyService.calculateDiscount({ booking_total: subtotal });
                          setLoyaltyApplied({ discount_amount: res.discount_amount || 0 });
                          toast({ title: 'Loyalty Discount Applied', description: 'Discount will be reflected in total.' });
                        } catch {
                          toast({ title: 'Not Eligible', description: 'Loyalty discount not available', variant: 'destructive' });
                          setLoyaltyApplied(null);
                        } finally { setApplyingLoyalty(false); }
                      }}
                      aria-label="Apply loyalty discount"
                    >
                      {applyingLoyalty ? (
                        <span className="inline-flex items-center gap-2"><Loader2 className="h-4 w-4 animate-spin" /> Applying…</span>
                      ) : (
                        'Apply'
                      )}
                    </Button>
                    {loyaltyApplied && (
                      <p className="text-xs text-green-600 mt-1">Loyalty discount: ${loyaltyApplied.discount_amount}</p>
                    )}
                  </div>
                </div>

                {/* Total */}
                <div className="border-t pt-4">
                  <div className="flex items-center justify-between">
                    <span className="font-medium">Total</span>
                    <span className="text-xl font-semibold" aria-live="polite">${calculateTotal()}</span>
                  </div>
                </div>
                <Button type="submit" className="w-full" disabled={booking} aria-label="Submit booking">
                  {booking ? 'Processing...' : 'Book Now'}
                </Button>
              </form>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  );
}
