'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { useTranslations } from 'next-intl';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { ArrowLeft, Save, Building2, MapPin, DollarSign, Home, Users, Bed, Upload, X, MoveUp, MoveDown, Image as ImageIcon, Info } from 'lucide-react';
import apiClient from '@/lib/api-client';
import { toast } from 'sonner';
import Link from 'next/link';

interface PropertyFormData {
  title: string;
  description: string;
  property_type: string;
  address: string;
  city: string;
  state: string;
  postal_code: string;
  country: string;
  latitude?: number;
  longitude?: number;
  price: number;
  currency: string;
  bedrooms: number;
  bathrooms: number;
  max_guests: number;
  size?: number;
  size_unit: string;
  amenities: string[];
  house_rules?: string;
  check_in_time?: string;
  check_out_time?: string;
  cancellation_policy?: string;
  minimum_stay: number;
  maximum_stay?: number;
}

const PROPERTY_TYPES = [
  'apartment',
  'house',
  'villa',
  'studio',
  'loft',
  'cabin',
  'cottage',
  'bungalow',
  'townhouse',
  'condo',
];

const AMENITIES = [
  'wifi',
  'parking',
  'kitchen',
  'tv',
  'air_conditioning',
  'heating',
  'washer',
  'dryer',
  'pool',
  'gym',
  'elevator',
  'balcony',
  'garden',
  'pets_allowed',
  'smoking_allowed',
];

export default function NewPropertyPage() {
  const t = useTranslations();
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [formData, setFormData] = useState<PropertyFormData>({
    title: '',
    description: '',
    property_type: 'apartment',
    address: '',
    city: '',
    state: '',
    postal_code: '',
    country: '',
    price: 50,
    currency: 'USD',
    bedrooms: 1,
    bathrooms: 1,
    max_guests: 2,
    size: 50,
    size_unit: 'sqm',
    amenities: [],
    minimum_stay: 1,
    check_in_time: '15:00',
    check_out_time: '11:00',
  });
  const [images, setImages] = useState<File[]>([]);
  const [imagePreviewUrls, setImagePreviewUrls] = useState<string[]>([]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);

    try {
      // Create property first
      const response = await apiClient.post('/properties', formData);
      const propertyId = response.data.data?.id || response.data.id;
      
      // Upload images if any
      if (images.length > 0) {
        const formDataImages = new FormData();
        images.forEach((image, index) => {
          formDataImages.append('images[]', image);
          if (index === 0) {
            formDataImages.append('main_image_index', '0');
          }
        });
        
        try {
          await apiClient.post(`/properties/${propertyId}/images`, formDataImages, {
            headers: {
              'Content-Type': 'multipart/form-data',
            },
          });
        } catch (imageError) {
          console.error('Failed to upload images:', imageError);
          toast.warning('Property created but some images failed to upload');
        }
      }
      
      toast.success('Property created successfully');
      router.push(`/dashboard/properties/${propertyId}`);
    } catch (error: any) {
      console.error('Failed to create property:', error);
      const errorMessage = error.response?.data?.message || error.response?.data?.error || 'Failed to create property';
      
      // Log more details for debugging
      if (error.response?.status === 403) {
        console.error('Permission denied. User might not have owner/host role.');
        toast.error('You need owner/host role to create properties. Visit Dashboard Settings to change your role.', {
          duration: 6000,
          action: {
            label: 'Go to Settings',
            onClick: () => router.push('/dashboard/settings'),
          },
        });
      } else {
        toast.error(errorMessage);
      }
    } finally {
      setLoading(false);
    }
  };

  const handleChange = (field: keyof PropertyFormData, value: any) => {
    setFormData((prev) => ({ ...prev, [field]: value }));
  };

  const toggleAmenity = (amenity: string) => {
    setFormData((prev) => ({
      ...prev,
      amenities: prev.amenities.includes(amenity)
        ? prev.amenities.filter((a) => a !== amenity)
        : [...prev.amenities, amenity],
    }));
  };

  const handleImageChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const files = Array.from(e.target.files || []);
    if (files.length === 0) return;

    // Validate file types
    const validFiles = files.filter(file => {
      const isValid = file.type.startsWith('image/');
      if (!isValid) {
        toast.error(`${file.name} is not a valid image file`);
      }
      return isValid;
    });

    if (validFiles.length === 0) return;

    // Check total images count
    if (images.length + validFiles.length > 20) {
      toast.error('Maximum 20 images allowed');
      return;
    }

    // Create preview URLs
    const newPreviewUrls = validFiles.map(file => URL.createObjectURL(file));
    
    setImages(prev => [...prev, ...validFiles]);
    setImagePreviewUrls(prev => [...prev, ...newPreviewUrls]);
  };

  const removeImage = (index: number) => {
    // Revoke object URL to prevent memory leaks
    URL.revokeObjectURL(imagePreviewUrls[index]);
    
    setImages(prev => prev.filter((_, i) => i !== index));
    setImagePreviewUrls(prev => prev.filter((_, i) => i !== index));
  };

  const moveImage = (fromIndex: number, toIndex: number) => {
    setImages(prev => {
      const newImages = [...prev];
      const [movedImage] = newImages.splice(fromIndex, 1);
      newImages.splice(toIndex, 0, movedImage);
      return newImages;
    });
    
    setImagePreviewUrls(prev => {
      const newUrls = [...prev];
      const [movedUrl] = newUrls.splice(fromIndex, 1);
      newUrls.splice(toIndex, 0, movedUrl);
      return newUrls;
    });
  };

  return (
    <div className="container mx-auto p-6 max-w-4xl space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <Link href="/dashboard/properties">
            <Button variant="ghost" className="gap-2 mb-2">
              <ArrowLeft className="h-4 w-4" />
              Back to Properties
            </Button>
          </Link>
          <h1 className="text-3xl font-bold flex items-center gap-2">
            <Building2 className="h-8 w-8" />
            Add New Property
          </h1>
          <p className="text-muted-foreground mt-1">
            Fill in the details to list your property
          </p>
        </div>
      </div>

      {/* Info Alert */}
      <Alert>
        <Info className="h-4 w-4" />
        <AlertTitle>Owner/Host Role Required</AlertTitle>
        <AlertDescription>
          You need an <strong>Owner</strong> or <strong>Host</strong> role to create properties. 
          If you get a permission error, please visit{' '}
          <Link href="/dashboard/settings" className="underline font-medium">
            Dashboard Settings
          </Link>{' '}
          to update your role.
        </AlertDescription>
      </Alert>

      <form onSubmit={handleSubmit} className="space-y-6">
        {/* Basic Information */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Home className="h-5 w-5" />
              Basic Information
            </CardTitle>
            <CardDescription>General details about your property</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="title">Property Title *</Label>
              <Input
                id="title"
                value={formData.title}
                onChange={(e) => handleChange('title', e.target.value)}
                placeholder="Beautiful apartment in city center"
                required
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="description">Description *</Label>
              <Textarea
                id="description"
                value={formData.description}
                onChange={(e) => handleChange('description', e.target.value)}
                placeholder="Describe your property, highlight unique features..."
                rows={5}
                required
              />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="property_type">Property Type *</Label>
                <Select
                  value={formData.property_type}
                  onValueChange={(value) => handleChange('property_type', value)}
                >
                  <SelectTrigger id="property_type">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    {PROPERTY_TYPES.map((type) => (
                      <SelectItem key={type} value={type}>
                        {type.charAt(0).toUpperCase() + type.slice(1)}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <Label htmlFor="size">Size</Label>
                <div className="flex gap-2">
                  <Input
                    id="size"
                    type="number"
                    value={formData.size || ''}
                    onChange={(e) => handleChange('size', e.target.value ? parseFloat(e.target.value) : undefined)}
                    placeholder="100"
                  />
                  <Select
                    value={formData.size_unit}
                    onValueChange={(value) => handleChange('size_unit', value)}
                  >
                    <SelectTrigger className="w-24">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="sqm">m²</SelectItem>
                      <SelectItem value="sqft">ft²</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Location */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <MapPin className="h-5 w-5" />
              Location
            </CardTitle>
            <CardDescription>Where is your property located?</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Label htmlFor="address">Street Address *</Label>
              <Input
                id="address"
                value={formData.address}
                onChange={(e) => handleChange('address', e.target.value)}
                placeholder="123 Main Street"
                required
              />
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="city">City *</Label>
                <Input
                  id="city"
                  value={formData.city}
                  onChange={(e) => handleChange('city', e.target.value)}
                  placeholder="New York"
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="state">State/Province</Label>
                <Input
                  id="state"
                  value={formData.state}
                  onChange={(e) => handleChange('state', e.target.value)}
                  placeholder="NY"
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="postal_code">Postal Code</Label>
                <Input
                  id="postal_code"
                  value={formData.postal_code}
                  onChange={(e) => handleChange('postal_code', e.target.value)}
                  placeholder="10001"
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="country">Country *</Label>
                <Input
                  id="country"
                  value={formData.country}
                  onChange={(e) => handleChange('country', e.target.value)}
                  placeholder="United States"
                  required
                />
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Capacity & Details */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Users className="h-5 w-5" />
              Capacity & Details
            </CardTitle>
            <CardDescription>Room configuration and guest capacity</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div className="space-y-2">
                <Label htmlFor="bedrooms">Bedrooms *</Label>
                <Input
                  id="bedrooms"
                  type="number"
                  min="0"
                  value={formData.bedrooms || 0}
                  onChange={(e) => handleChange('bedrooms', e.target.value ? parseInt(e.target.value) : 0)}
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="bathrooms">Bathrooms *</Label>
                <Input
                  id="bathrooms"
                  type="number"
                  min="1"
                  step="0.5"
                  value={formData.bathrooms || 1}
                  onChange={(e) => handleChange('bathrooms', e.target.value ? parseFloat(e.target.value) : 1)}
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="max_guests">Max Guests *</Label>
                <Input
                  id="max_guests"
                  type="number"
                  min="1"
                  value={formData.max_guests || 1}
                  onChange={(e) => handleChange('max_guests', e.target.value ? parseInt(e.target.value) : 1)}
                  required
                />
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Pricing */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <DollarSign className="h-5 w-5" />
              Pricing
            </CardTitle>
            <CardDescription>Set your nightly rate</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="price">Price per Night *</Label>
                <Input
                  id="price"
                  type="number"
                  min="1"
                  step="0.01"
                  value={formData.price || ''}
                  onChange={(e) => handleChange('price', e.target.value ? parseFloat(e.target.value) : 50)}
                  required
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="currency">Currency *</Label>
                <Select
                  value={formData.currency}
                  onValueChange={(value) => handleChange('currency', value)}
                >
                  <SelectTrigger id="currency">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="USD">USD ($)</SelectItem>
                    <SelectItem value="EUR">EUR (€)</SelectItem>
                    <SelectItem value="GBP">GBP (£)</SelectItem>
                    <SelectItem value="RON">RON (lei)</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Amenities */}
        <Card>
          <CardHeader>
            <CardTitle>Amenities</CardTitle>
            <CardDescription>Select all amenities available</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
              {AMENITIES.map((amenity) => (
                <div key={amenity} className="flex items-center space-x-2">
                  <Checkbox
                    id={amenity}
                    checked={formData.amenities.includes(amenity)}
                    onCheckedChange={() => toggleAmenity(amenity)}
                  />
                  <Label htmlFor={amenity} className="cursor-pointer">
                    {amenity.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())}
                  </Label>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Check-in/out & Rules */}
        <Card>
          <CardHeader>
            <CardTitle>House Rules & Policies</CardTitle>
            <CardDescription>Set check-in times and stay requirements</CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="check_in_time">Check-in Time</Label>
                <Input
                  id="check_in_time"
                  type="time"
                  value={formData.check_in_time}
                  onChange={(e) => handleChange('check_in_time', e.target.value)}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="check_out_time">Check-out Time</Label>
                <Input
                  id="check_out_time"
                  type="time"
                  value={formData.check_out_time}
                  onChange={(e) => handleChange('check_out_time', e.target.value)}
                />
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div className="space-y-2">
                <Label htmlFor="minimum_stay">Minimum Stay (nights)</Label>
                <Input
                  id="minimum_stay"
                  type="number"
                  min="1"
                  value={formData.minimum_stay || 1}
                  onChange={(e) => handleChange('minimum_stay', e.target.value ? parseInt(e.target.value) : 1)}
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="maximum_stay">Maximum Stay (nights)</Label>
                <Input
                  id="maximum_stay"
                  type="number"
                  min="1"
                  value={formData.maximum_stay || ''}
                  onChange={(e) => handleChange('maximum_stay', e.target.value ? parseInt(e.target.value) : undefined)}
                />
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="house_rules">House Rules</Label>
              <Textarea
                id="house_rules"
                value={formData.house_rules}
                onChange={(e) => handleChange('house_rules', e.target.value)}
                placeholder="No smoking, no parties, quiet hours 10pm-8am..."
                rows={4}
              />
            </div>
          </CardContent>
        </Card>

        {/* Images Gallery */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <ImageIcon className="h-5 w-5" />
              Property Images
            </CardTitle>
            <CardDescription>
              Upload photos of your property (Max 20 images, first image will be the main photo)
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            {/* Upload Button */}
            <div>
              <Input
                id="images"
                type="file"
                accept="image/*"
                multiple
                onChange={handleImageChange}
                className="hidden"
              />
              <Label htmlFor="images">
                <div className="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-primary transition-colors">
                  <Upload className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                  <p className="text-lg font-medium mb-1">Click to upload images</p>
                  <p className="text-sm text-muted-foreground">
                    PNG, JPG, WEBP up to 10MB each ({images.length}/20)
                  </p>
                </div>
              </Label>
            </div>

            {/* Image Preview Grid */}
            {images.length > 0 && (
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                {imagePreviewUrls.map((url, index) => (
                  <div key={index} className="relative group aspect-square">
                    <img
                      src={url}
                      alt={`Preview ${index + 1}`}
                      className="w-full h-full object-cover rounded-lg"
                    />
                    
                    {/* Main Image Badge */}
                    {index === 0 && (
                      <div className="absolute top-2 left-2">
                        <Badge variant="default" className="bg-primary">
                          Main Photo
                        </Badge>
                      </div>
                    )}
                    
                    {/* Action Buttons */}
                    <div className="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center gap-2">
                      {index > 0 && (
                        <Button
                          type="button"
                          size="icon"
                          variant="secondary"
                          onClick={() => moveImage(index, index - 1)}
                          title="Move left"
                        >
                          <MoveUp className="h-4 w-4 rotate-[-90deg]" />
                        </Button>
                      )}
                      {index < images.length - 1 && (
                        <Button
                          type="button"
                          size="icon"
                          variant="secondary"
                          onClick={() => moveImage(index, index + 1)}
                          title="Move right"
                        >
                          <MoveDown className="h-4 w-4 rotate-[-90deg]" />
                        </Button>
                      )}
                      <Button
                        type="button"
                        size="icon"
                        variant="destructive"
                        onClick={() => removeImage(index)}
                        title="Remove"
                      >
                        <X className="h-4 w-4" />
                      </Button>
                    </div>
                    
                    {/* Image Number */}
                    <div className="absolute bottom-2 right-2">
                      <Badge variant="secondary" className="bg-black/70 text-white">
                        {index + 1}
                      </Badge>
                    </div>
                  </div>
                ))}
              </div>
            )}

            {images.length > 0 && (
              <p className="text-sm text-muted-foreground">
                💡 Tip: Drag images to reorder. First image is the main photo shown in listings.
              </p>
            )}
          </CardContent>
        </Card>

        {/* Submit */}
        <div className="flex gap-4 justify-end">
          <Link href="/dashboard/properties">
            <Button type="button" variant="outline" disabled={loading}>
              Cancel
            </Button>
          </Link>
          <Button type="submit" disabled={loading} className="gap-2">
            {loading ? (
              <>
                <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                Creating...
              </>
            ) : (
              <>
                <Save className="h-4 w-4" />
                Create Property
              </>
            )}
          </Button>
        </div>
      </form>
    </div>
  );
}
