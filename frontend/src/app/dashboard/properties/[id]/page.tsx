'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import { useParams, useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
  ArrowLeft,
  Edit,
  Eye,
  Calendar,
  DollarSign,
  BarChart3,
  Image as ImageIcon,
  Settings,
  MapPin,
  BedDouble,
  Users,
  Home,
  CheckCircle2,
  XCircle,
  Clock,
  Trash2,
} from 'lucide-react';
import apiClient from '@/lib/api-client';
import { API_ENDPOINTS } from '@/lib/api-endpoints';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { SmartImage } from '@/components/ui/smart-image';
import { toast } from 'sonner';

interface Property {
  id: number;
  title: string;
  slug: string;
  description: string;
  property_type: string;
  address: string;
  city: string;
  state: string;
  country: string;
  price: number;
  currency: string;
  bedrooms: number;
  bathrooms: number;
  max_guests: number;
  size: number;
  size_unit: string;
  status: string;
  is_available: boolean;
  amenities: string[];
  images: string[];
  bookings_count: number;
  revenue_total: number;
  created_at: string;
  updated_at: string;
}

export default function PropertyDetailsPage() {
  const params = useParams();
  const router = useRouter();
  const propertyId = params?.id as string;
  const [property, setProperty] = useState<Property | null>(null);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);
  const [blockedDates, setBlockedDates] = useState<string[]>([]);
  const [loadingBlockedDates, setLoadingBlockedDates] = useState(false);
  const [newBlockedDate, setNewBlockedDate] = useState('');
  const [addingBlockedDate, setAddingBlockedDate] = useState(false);
  const [analytics, setAnalytics] = useState<any>(null);
  const [loadingAnalytics, setLoadingAnalytics] = useState(false);

  useEffect(() => {
    if (propertyId) {
      fetchProperty();
    }
  }, [propertyId]);

  const fetchProperty = async () => {
    try {
      setLoading(true);
  const response = await apiClient.get(API_ENDPOINTS.properties.show(propertyId));
  setProperty(response.data.data || response.data);
  // Preload blocked dates in background
  fetchBlockedDates();
    } catch (error) {
      console.error('Failed to fetch property:', error);
      toast.error('Failed to load property');
      router.push('/dashboard/properties');
    } finally {
      setLoading(false);
    }
  };

  const fetchBlockedDates = async () => {
    if (!propertyId) return;
    try {
      setLoadingBlockedDates(true);
      const response = await apiClient.get(API_ENDPOINTS.properties.blockedDates(propertyId));
      const dates = response.data?.data || response.data || [];
      setBlockedDates(Array.isArray(dates) ? dates : []);
    } catch (err) {
      console.warn('Failed to fetch blocked dates:', err);
    } finally {
      setLoadingBlockedDates(false);
    }
  };

  const fetchAnalytics = async () => {
    if (!propertyId) return;
    try {
      setLoadingAnalytics(true);
      const response = await apiClient.get(API_ENDPOINTS.properties.analytics(propertyId));
      setAnalytics(response.data?.data || response.data || {});
    } catch (err) {
      console.warn('Failed to fetch analytics:', err);
      setAnalytics({});
    } finally {
      setLoadingAnalytics(false);
    }
  };

  const handleAddBlockedDate = async () => {
    if (!property || !newBlockedDate) return;
    try {
      setAddingBlockedDate(true);
      await apiClient.post(API_ENDPOINTS.properties.blockedDates(property.id), {
        date: newBlockedDate,
      });
      toast.success('Blocked date added');
      setNewBlockedDate('');
      await fetchBlockedDates();
    } catch (err) {
      console.error('Failed to add blocked date:', err);
      toast.error('Failed to add blocked date');
    } finally {
      setAddingBlockedDate(false);
    }
  };

  const handleRemoveBlockedDate = async (date: string) => {
    if (!property) return;
    try {
      await apiClient.delete(API_ENDPOINTS.properties.blockedDates(property.id), {
        data: { date },
      });
      toast.success('Blocked date removed');
      await fetchBlockedDates();
    } catch (err) {
      console.error('Failed to remove blocked date:', err);
      toast.error('Failed to remove blocked date');
    }
  };

  const handlePublish = async (shouldPublish: boolean) => {
    if (!property) return;
    
    try {
      await apiClient.post(
        shouldPublish
          ? API_ENDPOINTS.properties.publish(property.id)
          : API_ENDPOINTS.properties.unpublish(property.id)
      );
      toast.success(`Property ${shouldPublish ? 'published' : 'unpublished'} successfully`);
      fetchProperty();
    } catch (error) {
      console.error('Failed to update property status:', error);
      toast.error('Failed to update property status');
    }
  };

  const handleDelete = async () => {
    if (!property) return;
    if (!confirm('Are you sure you want to delete this property? This action cannot be undone.')) return;
    
    try {
      await apiClient.delete(API_ENDPOINTS.properties.delete(property.id));
      toast.success('Property deleted successfully');
      router.push('/dashboard/properties');
    } catch (error) {
      console.error('Failed to delete property:', error);
      toast.error('Failed to delete property');
    }
  };

  const handleImagesUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    if (!property) return;
    const files = Array.from(e.target.files || []);
    if (files.length === 0) return;

    const validFiles = files.filter((file) => file.type.startsWith('image/'));
    if (validFiles.length === 0) {
      toast.error('Please select valid image files');
      return;
    }

    const formData = new FormData();
    validFiles.forEach((file) => formData.append('images[]', file));

    try {
      setUploading(true);
      await apiClient.post(API_ENDPOINTS.properties.uploadImages(property.id), formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      toast.success('Images uploaded');
      await fetchProperty();
    } catch (err) {
      console.error('Failed to upload images:', err);
      toast.error('Failed to upload images');
    } finally {
      setUploading(false);
      e.currentTarget.value = '';
    }
  };

  const handleDeleteImage = async (index: number) => {
    if (!property) return;
    try {
      await apiClient.delete(API_ENDPOINTS.properties.deleteImage(property.id, index));
      toast.success('Image deleted');
      await fetchProperty();
    } catch (err) {
      console.error('Failed to delete image:', err);
      toast.error('Failed to delete image');
    }
  };

  const handleSetMainImage = async (index: number) => {
    if (!property) return;
    try {
      await apiClient.post(API_ENDPOINTS.properties.setMainImage(property.id), { index });
      toast.success('Main image updated');
      await fetchProperty();
    } catch (err) {
      console.error('Failed to set main image:', err);
      toast.error('Failed to set main image');
    }
  };

  const getStatusBadge = (status: string) => {
    const variants: Record<string, { variant: any; icon: any }> = {
      published: { variant: 'default', icon: CheckCircle2 },
      draft: { variant: 'secondary', icon: Clock },
      archived: { variant: 'outline', icon: XCircle },
    };
    const config = variants[status] || variants.draft;
    const Icon = config.icon;
    
    return (
      <Badge variant={config.variant} className="gap-1">
        <Icon className="h-3 w-3" />
        {status.charAt(0).toUpperCase() + status.slice(1)}
      </Badge>
    );
  };

  if (loading) {
    return (
      <div className="container mx-auto p-6">
        <div className="flex items-center justify-center min-h-[400px]">
          <div className="text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
            <p className="text-muted-foreground">Loading property...</p>
          </div>
        </div>
      </div>
    );
  }

  if (!property) {
    return (
      <div className="container mx-auto p-6">
        <div className="text-center py-12">
          <h2 className="text-2xl font-bold mb-4">Property not found</h2>
          <Link href="/dashboard/properties">
            <Button>Back to Properties</Button>
          </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="container mx-auto p-6 space-y-6">
      {/* Header */}
      <div className="flex items-start justify-between">
        <div className="space-y-1">
          <Link href="/dashboard/properties">
            <Button variant="ghost" className="gap-2 mb-2">
              <ArrowLeft className="h-4 w-4" />
              Back to Properties
            </Button>
          </Link>
          <div className="flex items-center gap-3">
            <h1 className="text-3xl font-bold">{property.title}</h1>
            {getStatusBadge(property.status)}
          </div>
          <div className="flex items-center gap-4 text-muted-foreground">
            <span className="flex items-center gap-1">
              <MapPin className="h-4 w-4" />
              {property.city}, {property.country}
            </span>
            <span>•</span>
            <span className="flex items-center gap-1">
              <Home className="h-4 w-4" />
              {property.property_type}
            </span>
          </div>
        </div>

        <div className="flex gap-2">
          <Link href={`/properties/${property.slug || property.id}`} target="_blank">
            <Button variant="outline" className="gap-2">
              <Eye className="h-4 w-4" />
              Preview
            </Button>
          </Link>
          <Link href={`/dashboard/properties/${property.id}/edit`}>
            <Button variant="outline" className="gap-2">
              <Edit className="h-4 w-4" />
              Edit
            </Button>
          </Link>
          {property.status === 'published' ? (
            <Button
              variant="outline"
              onClick={() => handlePublish(false)}
              className="gap-2"
            >
              <XCircle className="h-4 w-4" />
              Unpublish
            </Button>
          ) : (
            <Button onClick={() => handlePublish(true)} className="gap-2">
              <CheckCircle2 className="h-4 w-4" />
              Publish
            </Button>
          )}
        </div>
      </div>

      {/* Quick Stats */}
      <div className="grid gap-4 md:grid-cols-4">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Bookings</CardTitle>
            <Calendar className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{property.bookings_count || 0}</div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {property.currency} {property.revenue_total?.toLocaleString() || '0'}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Nightly Rate</CardTitle>
            <DollarSign className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              {property.currency} {property.price?.toLocaleString()}
            </div>
          </CardContent>
        </Card>
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Capacity</CardTitle>
            <Users className="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">{property.max_guests} guests</div>
            <p className="text-xs text-muted-foreground">
              {property.bedrooms} bed • {property.bathrooms} bath
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Main Content Tabs */}
      <Tabs defaultValue="overview" className="space-y-4">
        <TabsList>
          <TabsTrigger value="overview">Overview</TabsTrigger>
          <TabsTrigger value="calendar">
            <Calendar className="h-4 w-4 mr-2" />
            Calendar
          </TabsTrigger>
          <TabsTrigger value="bookings">
            <Calendar className="h-4 w-4 mr-2" />
            Bookings
          </TabsTrigger>
          <TabsTrigger value="analytics">
            <BarChart3 className="h-4 w-4 mr-2" />
            Analytics
          </TabsTrigger>
          <TabsTrigger value="images">
            <ImageIcon className="h-4 w-4 mr-2" />
            Images
          </TabsTrigger>
          <TabsTrigger value="settings">
            <Settings className="h-4 w-4 mr-2" />
            Settings
          </TabsTrigger>
        </TabsList>

        <TabsContent value="overview" className="space-y-4">
          <Card>
            <CardHeader>
              <CardTitle>Property Details</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4">
              <div>
                <h3 className="font-semibold mb-2">Description</h3>
                <p className="text-muted-foreground">{property.description}</p>
              </div>

              <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                  <p className="text-sm text-muted-foreground">Type</p>
                  <p className="font-medium capitalize">{property.property_type}</p>
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">Bedrooms</p>
                  <p className="font-medium">{property.bedrooms}</p>
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">Bathrooms</p>
                  <p className="font-medium">{property.bathrooms}</p>
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">Max Guests</p>
                  <p className="font-medium">{property.max_guests}</p>
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">Size</p>
                  <p className="font-medium">
                    {property.size} {property.size_unit}
                  </p>
                </div>
                <div>
                  <p className="text-sm text-muted-foreground">Created</p>
                  <p className="font-medium">
                    {new Date(property.created_at).toLocaleDateString()}
                  </p>
                </div>
              </div>

              {property.amenities && property.amenities.length > 0 && (
                <div>
                  <h3 className="font-semibold mb-2">Amenities</h3>
                  <div className="flex flex-wrap gap-2">
                    {property.amenities.map((amenity) => (
                      <Badge key={amenity} variant="secondary">
                        {amenity.replace(/_/g, ' ')}
                      </Badge>
                    ))}
                  </div>
                </div>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Location</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="space-y-2">
                <p className="font-medium">{property.address}</p>
                <p className="text-muted-foreground">
                  {property.city}, {property.state} {property.country}
                </p>
              </div>
            </CardContent>
          </Card>

          <Card className="border-destructive">
            <CardHeader>
              <CardTitle className="text-destructive">Danger Zone</CardTitle>
              <CardDescription>Irreversible actions</CardDescription>
            </CardHeader>
            <CardContent>
              <Button
                variant="destructive"
                onClick={handleDelete}
                className="gap-2"
              >
                <Trash2 className="h-4 w-4" />
                Delete Property
              </Button>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="calendar">
          <Card>
            <CardHeader>
              <CardTitle>Availability Calendar</CardTitle>
              <CardDescription>Manage your property's availability and pricing</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="new-blocked-date">Add Blocked Date</Label>
                  <div className="flex gap-2">
                    <Input
                      id="new-blocked-date"
                      type="date"
                      value={newBlockedDate}
                      onChange={(e) => setNewBlockedDate(e.target.value)}
                      min={new Date().toISOString().split('T')[0]}
                    />
                    <Button
                      onClick={handleAddBlockedDate}
                      disabled={!newBlockedDate || addingBlockedDate}
                      size="sm"
                    >
                      {addingBlockedDate ? 'Adding…' : 'Add'}
                    </Button>
                  </div>
                  <p className="text-xs text-muted-foreground">
                    Blocked dates prevent bookings on those days.
                  </p>
                </div>

                <div className="flex items-center justify-between">
                  <h4 className="text-sm font-medium">Blocked Dates</h4>
                  <Button size="sm" variant="outline" onClick={fetchBlockedDates} disabled={loadingBlockedDates}>
                    {loadingBlockedDates ? 'Refreshing…' : 'Refresh'}
                  </Button>
                </div>

                {loadingBlockedDates ? (
                  <div className="grid sm:grid-cols-2 md:grid-cols-3 gap-2">
                    {[...Array(3)].map((_, i) => (
                      <div key={i} className="h-8 bg-muted animate-pulse rounded-md" />
                    ))}
                  </div>
                ) : blockedDates.length === 0 ? (
                  <div className="text-center py-12">
                    <Calendar className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                    <p className="text-muted-foreground">No blocked dates</p>
                  </div>
                ) : (
                  <div className="grid sm:grid-cols-2 md:grid-cols-3 gap-2">
                    {blockedDates.map((date) => (
                      <div key={date} className="flex items-center justify-between bg-secondary rounded-md px-3 py-2">
                        <span className="text-sm font-medium">
                          {new Date(date).toLocaleDateString()}
                        </span>
                        <Button
                          size="icon"
                          variant="ghost"
                          className="h-6 w-6"
                          onClick={() => handleRemoveBlockedDate(date)}
                          aria-label={`Remove blocked date ${new Date(date).toLocaleDateString()}`}
                        >
                          <XCircle className="h-4 w-4" />
                        </Button>
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="bookings">
          <Card>
            <CardHeader>
              <CardTitle>Property Bookings</CardTitle>
              <CardDescription>View and manage bookings for this property</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="text-center py-12">
                <Calendar className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                <p className="text-muted-foreground">No bookings yet</p>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="analytics">
          <Card>
            <CardHeader>
              <CardTitle>Performance Analytics</CardTitle>
              <CardDescription>Track your property's performance</CardDescription>
              <Button
                size="sm"
                variant="outline"
                onClick={fetchAnalytics}
                disabled={loadingAnalytics}
                className="mt-2"
              >
                {loadingAnalytics ? 'Loading…' : analytics ? 'Refresh' : 'Load Analytics'}
              </Button>
            </CardHeader>
            <CardContent>
              {loadingAnalytics ? (
                <div className="space-y-4">
                  <div className="grid gap-4 md:grid-cols-3">
                    {[...Array(3)].map((_, i) => (
                      <div key={i} className="h-24 bg-muted animate-pulse rounded-lg" />
                    ))}
                  </div>
                  <div className="h-64 bg-muted animate-pulse rounded-lg" />
                </div>
              ) : analytics ? (
                <div className="space-y-6">
                  <div className="grid gap-4 md:grid-cols-3">
                    <Card>
                      <CardHeader className="pb-2">
                        <CardTitle className="text-sm font-medium text-muted-foreground">
                          Total Bookings
                        </CardTitle>
                      </CardHeader>
                      <CardContent>
                        <div className="text-2xl font-bold">
                          {analytics.total_bookings || property?.bookings_count || 0}
                        </div>
                      </CardContent>
                    </Card>
                    <Card>
                      <CardHeader className="pb-2">
                        <CardTitle className="text-sm font-medium text-muted-foreground">
                          Total Revenue
                        </CardTitle>
                      </CardHeader>
                      <CardContent>
                        <div className="text-2xl font-bold">
                          {property?.currency} {(analytics.total_revenue || property?.revenue_total || 0).toLocaleString()}
                        </div>
                      </CardContent>
                    </Card>
                    <Card>
                      <CardHeader className="pb-2">
                        <CardTitle className="text-sm font-medium text-muted-foreground">
                          Occupancy Rate
                        </CardTitle>
                      </CardHeader>
                      <CardContent>
                        <div className="text-2xl font-bold">
                          {analytics.occupancy_rate || '0'}%
                        </div>
                      </CardContent>
                    </Card>
                  </div>

                  {analytics.recent_bookings && analytics.recent_bookings.length > 0 && (
                    <div>
                      <h4 className="text-sm font-semibold mb-3">Recent Bookings</h4>
                      <div className="space-y-2">
                        {analytics.recent_bookings.slice(0, 5).map((booking: any, idx: number) => (
                          <div key={idx} className="flex items-center justify-between p-3 border rounded-md">
                            <div>
                              <p className="text-sm font-medium">{booking.guest_name || 'Guest'}</p>
                              <p className="text-xs text-muted-foreground">
                                {new Date(booking.check_in).toLocaleDateString()} - {new Date(booking.check_out).toLocaleDateString()}
                              </p>
                            </div>
                            <Badge variant={booking.status === 'confirmed' ? 'default' : 'secondary'}>
                              {booking.status}
                            </Badge>
                          </div>
                        ))}
                      </div>
                    </div>
                  )}

                  {(!analytics.recent_bookings || analytics.recent_bookings.length === 0) && (
                    <div className="text-center py-8 text-muted-foreground">
                      <BarChart3 className="h-12 w-12 mx-auto mb-3 opacity-50" />
                      <p>No booking data available yet</p>
                    </div>
                  )}
                </div>
              ) : (
                <div className="text-center py-12">
                  <BarChart3 className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                  <p className="text-muted-foreground">Click "Load Analytics" to view performance data</p>
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="images">
          <Card>
            <CardHeader>
              <CardTitle>Property Images</CardTitle>
              <CardDescription>Manage photos of your property</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-6">
                <div>
                  <Input
                    id="upload-images"
                    type="file"
                    accept="image/*"
                    multiple
                    onChange={handleImagesUpload}
                    className="hidden"
                  />
                  <Label htmlFor="upload-images">
                    <div className="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer hover:border-primary transition-colors">
                      <ImageIcon className="h-10 w-10 mx-auto text-muted-foreground mb-2" />
                      <p className="font-medium">Click to upload images</p>
                      <p className="text-xs text-muted-foreground">PNG, JPG, WEBP up to 10MB each</p>
                    </div>
                  </Label>
                  {uploading && (
                    <p className="text-xs text-muted-foreground mt-2">Uploading…</p>
                  )}
                </div>

                {loading ? (
                  <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    {[...Array(4)].map((_, i) => (
                      <div key={i} className="relative w-full aspect-square bg-muted animate-pulse rounded-md" />
                    ))}
                  </div>
                ) : property.images && property.images.length > 0 ? (
                  <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    {property.images.map((src, index) => (
                      <div key={src + index} className="relative group">
                        <div className="relative w-full aspect-square overflow-hidden rounded-md">
                          <SmartImage src={src} alt={`Property image ${index + 1}`} fill />
                        </div>
                        {index === 0 && (
                          <Badge className="absolute top-2 left-2">Main</Badge>
                        )}
                        <div className="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-md flex items-center justify-center gap-2">
                          {index > 0 && (
                            <Button size="sm" variant="secondary" onClick={() => handleSetMainImage(index)}>
                              Set main
                            </Button>
                          )}
                          <Button size="sm" variant="destructive" onClick={() => handleDeleteImage(index)}>
                            Delete
                          </Button>
                        </div>
                      </div>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-8 text-muted-foreground">No images yet. Upload to get started.</div>
                )}
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="settings">
          <Card>
            <CardHeader>
              <CardTitle>Property Settings</CardTitle>
              <CardDescription>Advanced settings and integrations</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="text-center py-12">
                <Settings className="h-12 w-12 mx-auto text-muted-foreground mb-4" />
                <p className="text-muted-foreground">Settings feature coming soon</p>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}
