'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import apiClient from '@/lib/api-client';
import { useAuth } from '@/contexts/auth-context';
import { useToast } from '@/hooks/use-toast';
import { MainLayout } from '@/components/layouts/main-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Textarea } from '@/components/ui/textarea';
import { Home, ArrowLeft, Loader2 } from 'lucide-react';
import Link from 'next/link';

export default function NewPropertyPage() {
  const router = useRouter();
  const { user } = useAuth();
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    type: 'apartment',
    bedrooms: 1,
    bathrooms: 1,
    max_guests: 2,
    price_per_night: 0,
    address: '',
    city: '',
    country: '',
    amenities: [] as string[],
  });

  const amenitiesList = [
    'wifi',
    'parking',
    'tv',
    'ac',
    'kitchen',
    'pool',
    'gym',
    'heating',
    'washer',
    'dryer',
  ];

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!user) {
      toast({
        title: 'Error',
        description: 'You must be logged in to add a property',
        variant: 'destructive',
      });
      router.push('/auth/login');
      return;
    }

    setLoading(true);
    try {
      const { data } = await apiClient.post('/host/properties', formData);
      toast({
        title: 'Success',
        description: 'Property added successfully!',
      });
      router.push('/host/properties');
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to add property',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  };

  const toggleAmenity = (amenity: string) => {
    setFormData((prev) => ({
      ...prev,
      amenities: prev.amenities.includes(amenity)
        ? prev.amenities.filter((a) => a !== amenity)
        : [...prev.amenities, amenity],
    }));
  };

  if (!user) {
    return null;
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-4xl">
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger asChild>
              <Button variant="outline" size="sm" className="mb-4" asChild>
                <Link href="/host/properties">
                  <ArrowLeft className="h-4 w-4 mr-2" />
                  Back to Properties
                </Link>
              </Button>
            </TooltipTrigger>
            <TooltipContent>Return to property list</TooltipContent>
          </Tooltip>
        </TooltipProvider>

        <div className="mb-6">
          <h1 className="text-3xl font-bold mb-2 animate-fade-in">Add New Property</h1>
          <p className="text-gray-600 animate-fade-in" style={{ animationDelay: '100ms' }}>List your property for rent</p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Basic Information */}
          <Card className="animate-fade-in-up">
            <CardHeader>
              <CardTitle>Basic Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <Label htmlFor="title">Property Title *</Label>
                <Input
                  id="title"
                  value={formData.title}
                  onChange={(e) => setFormData({ ...formData, title: e.target.value })}
                  placeholder="Beautiful 2-bedroom apartment in downtown"
                  required
                />
              </div>

              <div>
                <Label htmlFor="description">Description *</Label>
                <Textarea
                  id="description"
                  value={formData.description}
                  onChange={(e) => setFormData({ ...formData, description: e.target.value })}
                  placeholder="Describe your property..."
                  rows={4}
                  required
                />
              </div>

              <div>
                <Label htmlFor="type">Property Type *</Label>
                <select
                  id="type"
                  value={formData.type}
                  onChange={(e) => setFormData({ ...formData, type: e.target.value })}
                  className="w-full px-3 py-2 border border-input rounded-md focus:outline-none focus:ring-2 focus:ring-ring"
                  required
                >
                  <option value="apartment">Apartment</option>
                  <option value="house">House</option>
                  <option value="villa">Villa</option>
                  <option value="studio">Studio</option>
                </select>
              </div>
            </CardContent>
          </Card>

          {/* Property Details */}
          <Card className="animate-fade-in-up" style={{ animationDelay: '120ms' }}>
            <CardHeader>
              <CardTitle>Property Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <Label htmlFor="bedrooms">Bedrooms *</Label>
                  <Input
                    id="bedrooms"
                    type="number"
                    min="1"
                    value={formData.bedrooms}
                    onChange={(e) =>
                      setFormData({ ...formData, bedrooms: parseInt(e.target.value) })
                    }
                    required
                  />
                </div>
                <div>
                  <Label htmlFor="bathrooms">Bathrooms *</Label>
                  <Input
                    id="bathrooms"
                    type="number"
                    min="1"
                    value={formData.bathrooms}
                    onChange={(e) =>
                      setFormData({ ...formData, bathrooms: parseInt(e.target.value) })
                    }
                    required
                  />
                </div>
                <div>
                  <Label htmlFor="max_guests">Max Guests *</Label>
                  <Input
                    id="max_guests"
                    type="number"
                    min="1"
                    value={formData.max_guests}
                    onChange={(e) =>
                      setFormData({ ...formData, max_guests: parseInt(e.target.value) })
                    }
                    required
                  />
                </div>
              </div>

              <div>
                <Label htmlFor="price_per_night">Price per Night (USD) *</Label>
                <Input
                  id="price_per_night"
                  type="number"
                  min="0"
                  step="0.01"
                  value={formData.price_per_night}
                  onChange={(e) =>
                    setFormData({ ...formData, price_per_night: parseFloat(e.target.value) })
                  }
                  placeholder="100.00"
                  required
                />
              </div>
            </CardContent>
          </Card>

          {/* Location */}
          <Card className="animate-fade-in-up" style={{ animationDelay: '240ms' }}>
            <CardHeader>
              <CardTitle>Location</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <Label htmlFor="address">Street Address *</Label>
                <Input
                  id="address"
                  value={formData.address}
                  onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                  placeholder="123 Main Street"
                  required
                />
              </div>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="city">City *</Label>
                  <Input
                    id="city"
                    value={formData.city}
                    onChange={(e) => setFormData({ ...formData, city: e.target.value })}
                    placeholder="New York"
                    required
                  />
                </div>
                <div>
                  <Label htmlFor="country">Country *</Label>
                  <Input
                    id="country"
                    value={formData.country}
                    onChange={(e) => setFormData({ ...formData, country: e.target.value })}
                    placeholder="United States"
                    required
                  />
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Amenities */}
          <Card className="animate-fade-in-up" style={{ animationDelay: '360ms' }}>
            <CardHeader>
              <CardTitle>Amenities</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                {amenitiesList.map((amenity) => (
                  <div key={amenity} className="flex items-center">
                    <input
                      type="checkbox"
                      id={amenity}
                      checked={formData.amenities.includes(amenity)}
                      onChange={() => toggleAmenity(amenity)}
                      className="h-4 w-4 mr-2"
                    />
                    <label htmlFor={amenity} className="capitalize cursor-pointer">
                      {amenity}
                    </label>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>

          {/* Submit */}
          <div className="flex gap-4 animate-fade-in-up" style={{ animationDelay: '480ms' }}>
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button type="submit" disabled={loading} className="flex-1">
                    {loading ? (
                      <>
                        <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                        Adding Property...
                      </>
                    ) : (
                      <>
                        <Home className="h-4 w-4 mr-2" />
                        Add Property
                      </>
                    )}
                  </Button>
                </TooltipTrigger>
                <TooltipContent>Create and publish listing</TooltipContent>
              </Tooltip>
            </TooltipProvider>
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button
                    type="button"
                    variant="outline"
                    onClick={() => router.push('/host/properties')}
                    className="flex-1"
                    disabled={loading}
                  >
                    Cancel
                  </Button>
                </TooltipTrigger>
                <TooltipContent>Discard changes</TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>
        </form>
      </div>
    </MainLayout>
  );
}
