'use client';

import { useEffect, useState, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import Link from 'next/link';
import Image from 'next/image';
import apiClient from '@/lib/api-client';
import { Property } from '@/types';
import { useAuth } from '@/contexts/auth-context';
import { useToast } from '@/hooks/use-toast';
import { MainLayout } from '@/components/layouts/main-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
  AlertDialogAction,
} from '@/components/ui/alert-dialog';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Home,
  Plus,
  Edit,
  Trash2,
  Eye,
  DollarSign,
  Calendar,
  TrendingUp,
  BarChart3,
  Wrench,
  Key,
  CheckSquare,
  XSquare,
} from 'lucide-react';
import { formatCurrency } from '@/lib/utils';
import { API_ENDPOINTS } from '@/lib/api-endpoints';

export default function HostPropertiesPage() {
  const router = useRouter();
  const { user } = useAuth();
  const { toast } = useToast();
  const [properties, setProperties] = useState<Property[]>([]);
  const [loading, setLoading] = useState(true);
  const [selectedProperties, setSelectedProperties] = useState<Set<number>>(new Set());
  const [showBulkDialog, setShowBulkDialog] = useState(false);
  const [bulkAction, setBulkAction] = useState<string>('');
  const [bulkPriceUpdate, setBulkPriceUpdate] = useState('');
  
  const [stats, setStats] = useState({
    total: 0,
    active: 0,
    revenue: 0,
    bookings: 0,
  });

  const fetchProperties = useCallback(async () => {
    try {
      const { data } = await apiClient.get('/host/properties');
      setProperties(data.data || []);
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to load properties',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  }, [toast]);

  const fetchStats = useCallback(async () => {
    try {
      const { data } = await apiClient.get('/host/stats');
      setStats(data.data || stats);
    } catch (error) {
      console.error('Failed to load stats');
    }
  }, [stats]);

  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }
    fetchProperties();
    fetchStats();
  }, [user, router, fetchProperties, fetchStats]);

  const handleDelete = useCallback(async (id: number, propertyTitle: string) => {
    // Deletion handled by AlertDialog
    try {
      await apiClient.delete(`/host/properties/${id}`);
      toast({
        title: 'Success',
        description: `${propertyTitle} deleted successfully`,
      });
      fetchProperties();
      fetchStats();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to delete property',
        variant: 'destructive',
      });
    }
  }, [toast, fetchProperties, fetchStats]);

  const togglePropertySelection = useCallback((propertyId: number) => {
    setSelectedProperties((prev) => {
      const newSet = new Set(prev);
      if (newSet.has(propertyId)) {
        newSet.delete(propertyId);
      } else {
        newSet.add(propertyId);
      }
      return newSet;
    });
  }, []);

  const selectAll = useCallback(() => {
    if (selectedProperties.size === properties.length) {
      setSelectedProperties(new Set());
    } else {
      setSelectedProperties(new Set(properties.map((p) => p.id)));
    }
  }, [selectedProperties.size, properties]);

  const handleBulkAction = useCallback(async () => {
    if (selectedProperties.size === 0) {
      toast({
        title: 'Error',
        description: 'Please select at least one property',
        variant: 'destructive',
      });
      return;
    }

    const propertyIds = Array.from(selectedProperties);

    try {
      switch (bulkAction) {
        case 'activate':
          await apiClient.post(API_ENDPOINTS.properties.bulk.activate, { property_ids: propertyIds });
          toast({ title: 'Success', description: 'Properties activated successfully' });
          break;
        case 'deactivate':
          await apiClient.post(API_ENDPOINTS.properties.bulk.deactivate, { property_ids: propertyIds });
          toast({ title: 'Success', description: 'Properties deactivated successfully' });
          break;
        case 'delete':
          if (!confirm(`Are you sure you want to delete ${propertyIds.length} properties?`)) {
            return;
          }
          await apiClient.post(API_ENDPOINTS.properties.bulk.delete, { property_ids: propertyIds });
          toast({ title: 'Success', description: 'Properties deleted successfully' });
          break;
        case 'update_prices':
          if (!bulkPriceUpdate || isNaN(Number(bulkPriceUpdate))) {
            toast({ title: 'Error', description: 'Please enter a valid price', variant: 'destructive' });
            return;
          }
          await apiClient.post(API_ENDPOINTS.properties.bulk.updatePrices, {
            property_ids: propertyIds,
            price: Number(bulkPriceUpdate),
          });
          toast({ title: 'Success', description: 'Prices updated successfully' });
          break;
        default:
          toast({ title: 'Error', description: 'Please select an action', variant: 'destructive' });
          return;
      }

      setShowBulkDialog(false);
      setSelectedProperties(new Set());
      setBulkAction('');
      setBulkPriceUpdate('');
      fetchProperties();
      fetchStats();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Bulk operation failed',
        variant: 'destructive',
      });
    }
  }, [selectedProperties, bulkAction, bulkPriceUpdate, toast, fetchProperties, fetchStats]);

  if (!user) {
    return null;
  }

  if (loading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <div className="animate-pulse space-y-4">
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
              {[1, 2, 3, 4].map((i) => (
                <div key={i} className="h-24 bg-gray-200 rounded-lg"></div>
              ))}
            </div>
            {[1, 2].map((i) => (
              <div key={i} className="h-48 bg-gray-200 rounded-lg"></div>
            ))}
          </div>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        <div className="flex justify-between items-center mb-6">
          <div>
            <h1 className="text-3xl font-bold mb-2 animate-fade-in">My Properties</h1>
            <p className="text-gray-600 animate-fade-in" style={{ animationDelay: '100ms' }}>Manage your rental properties</p>
            <p className="sr-only" aria-live="polite">
              {stats.total} total properties, {stats.active} active, {stats.bookings} bookings, {formatCurrency(stats.revenue)} revenue
            </p>
          </div>
          <div className="flex space-x-2">
            {selectedProperties.size > 0 && (
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" onClick={() => setShowBulkDialog(true)}>
                      <CheckSquare className="h-4 w-4 mr-2" />
                      Bulk Actions ({selectedProperties.size})
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Perform actions on selected properties</TooltipContent>
                </Tooltip>
              </TooltipProvider>
            )}
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Button asChild>
                    <Link href="/host/properties/new">
                      <Plus className="h-4 w-4 mr-2" />
                      Add Property
                    </Link>
                  </Button>
                </TooltipTrigger>
                <TooltipContent>Create a new listing</TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
          <Card className="animate-fade-in-up">
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Total Properties</p>
                  <p className="text-2xl font-bold">{stats.total}</p>
                </div>
                <Home className="h-10 w-10 text-primary/20" />
              </div>
            </CardContent>
          </Card>
          <Card className="animate-fade-in-up" style={{ animationDelay: '80ms' }}>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Active Listings</p>
                  <p className="text-2xl font-bold">{stats.active}</p>
                </div>
                <Eye className="h-10 w-10 text-green-500/20" />
              </div>
            </CardContent>
          </Card>
          <Card className="animate-fade-in-up" style={{ animationDelay: '160ms' }}>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Total Bookings</p>
                  <p className="text-2xl font-bold">{stats.bookings}</p>
                </div>
                <Calendar className="h-10 w-10 text-blue-500/20" />
              </div>
            </CardContent>
          </Card>
          <Card className="animate-fade-in-up" style={{ animationDelay: '240ms' }}>
            <CardContent className="pt-6">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm text-gray-600 mb-1">Total Revenue</p>
                  <p className="text-2xl font-bold">{formatCurrency(stats.revenue)}</p>
                </div>
                <TrendingUp className="h-10 w-10 text-yellow-500/20" />
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Properties List */}
        {properties.length === 0 ? (
          <Card>
            <CardContent className="flex flex-col items-center justify-center py-12">
              <Home className="h-16 w-16 text-gray-300 mb-4" />
              <h3 className="text-xl font-semibold mb-2">No properties yet</h3>
              <p className="text-gray-600 mb-6">Start by adding your first property</p>
              <Link href="/host/properties/new">
                <Button>
                  <Plus className="h-4 w-4 mr-2" />
                  Add Your First Property
                </Button>
              </Link>
            </CardContent>
          </Card>
        ) : (
          <>
            {/* Select All */}
            <div className="flex items-center mb-4">
              <Checkbox
                checked={selectedProperties.size === properties.length}
                onCheckedChange={selectAll}
                id="select-all"
              />
              <Label htmlFor="select-all" className="ml-2 cursor-pointer">
                Select All ({properties.length})
              </Label>
            </div>

            <div className="space-y-4">
              {properties.map((property, idx) => (
                <Card key={property.id} className={`${selectedProperties.has(property.id) ? 'ring-2 ring-primary' : ''} animate-fade-in-up`} style={{ animationDelay: `${Math.min(idx, 8) * 60}ms` }}>
                  <CardContent className="p-0">
                    <div className="md:flex">
                      {/* Checkbox */}
                      <div className="flex items-center justify-center p-4 border-r">
                        <Checkbox
                          checked={selectedProperties.has(property.id)}
                          onCheckedChange={() => togglePropertySelection(property.id)}
                        />
                      </div>
                    {/* Property Image */}
                    <div className="md:w-1/4 h-48 md:h-auto bg-gray-200 relative">
                      {property.image_url && (
                        <Image
                          src={property.image_url}
                          alt={property.title}
                          fill
                          className="object-cover"
                          sizes="(max-width: 768px) 100vw, 25vw"
                        />
                      )}
                    </div>

                    {/* Property Details */}
                    <div className="flex-1 p-6">
                      <div className="flex items-start justify-between mb-4">
                        <div>
                          <h3 className="text-xl font-bold mb-1">{property.title}</h3>
                          <p className="text-gray-600 text-sm">{property.address}</p>
                        </div>
                        <span
                          className={`px-3 py-1 rounded-full text-xs font-semibold ${
                            property.status === 'available'
                              ? 'bg-green-100 text-green-800'
                              : property.status === 'booked'
                              ? 'bg-blue-100 text-blue-800'
                              : 'bg-gray-100 text-gray-800'
                          }`}
                        >
                          {property.status}
                        </span>
                      </div>

                      <p className="text-gray-700 mb-4 line-clamp-2">
                        {property.description}
                      </p>

                      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                          <p className="text-xs text-gray-600">Price</p>
                          <p className="font-semibold">
                            {formatCurrency(property.price_per_night || property.price)}/night
                          </p>
                        </div>
                        <div>
                          <p className="text-xs text-gray-600">Bedrooms</p>
                          <p className="font-semibold">{property.bedrooms}</p>
                        </div>
                        <div>
                          <p className="text-xs text-gray-600">Bathrooms</p>
                          <p className="font-semibold">{property.bathrooms}</p>
                        </div>
                        <div>
                          <p className="text-xs text-gray-600">Guests</p>
                          <p className="font-semibold">{property.max_guests || 4}</p>
                        </div>
                      </div>

                      <div className="flex flex-wrap gap-2">
                        <TooltipProvider>
                          <Tooltip>
                            <TooltipTrigger asChild>
                              <Button variant="outline" size="sm" asChild>
                                <Link href={`/properties/${property.id}`}>
                                  <Eye className="h-4 w-4 mr-2" />
                                  View
                                </Link>
                              </Button>
                            </TooltipTrigger>
                            <TooltipContent>View property details</TooltipContent>
                          </Tooltip>
                        </TooltipProvider>
                        <TooltipProvider>
                          <Tooltip>
                            <TooltipTrigger asChild>
                              <Button variant="outline" size="sm" asChild>
                                <Link href={`/properties/${property.id}/analytics`}>
                                  <BarChart3 className="h-4 w-4 mr-2" />
                                  Analytics
                                </Link>
                              </Button>
                            </TooltipTrigger>
                            <TooltipContent>View performance metrics</TooltipContent>
                          </Tooltip>
                        </TooltipProvider>
                        <TooltipProvider>
                          <Tooltip>
                            <TooltipTrigger asChild>
                              <Button variant="outline" size="sm" asChild>
                                <Link href={`/properties/${property.id}/maintenance`}>
                                  <Wrench className="h-4 w-4 mr-2" />
                                  Maintenance
                                </Link>
                              </Button>
                            </TooltipTrigger>
                            <TooltipContent>Manage maintenance tasks</TooltipContent>
                          </Tooltip>
                        </TooltipProvider>
                        <TooltipProvider>
                          <Tooltip>
                            <TooltipTrigger asChild>
                              <Button variant="outline" size="sm" asChild>
                                <Link href={`/properties/${property.id}/smart-locks`}>
                                  <Key className="h-4 w-4 mr-2" />
                                  Smart Locks
                                </Link>
                              </Button>
                            </TooltipTrigger>
                            <TooltipContent>Manage access codes</TooltipContent>
                          </Tooltip>
                        </TooltipProvider>
                        <TooltipProvider>
                          <Tooltip>
                            <TooltipTrigger asChild>
                              <Button variant="outline" size="sm" asChild>
                                <Link href={`/host/properties/${property.id}/edit`}>
                                  <Edit className="h-4 w-4 mr-2" />
                                  Edit
                                </Link>
                              </Button>
                            </TooltipTrigger>
                            <TooltipContent>Edit property details</TooltipContent>
                          </Tooltip>
                        </TooltipProvider>
                        <AlertDialog>
                          <TooltipProvider>
                            <Tooltip>
                              <TooltipTrigger asChild>
                                <AlertDialogTrigger asChild>
                                  <Button variant="destructive" size="sm">
                                    <Trash2 className="h-4 w-4 mr-2" />
                                    Delete
                                  </Button>
                                </AlertDialogTrigger>
                              </TooltipTrigger>
                              <TooltipContent>Delete this property</TooltipContent>
                            </Tooltip>
                          </TooltipProvider>
                          <AlertDialogContent>
                            <AlertDialogHeader>
                              <AlertDialogTitle>Delete Property?</AlertDialogTitle>
                              <AlertDialogDescription>
                                Are you sure you want to delete &quot;{property.title}&quot;? This action cannot be undone.
                              </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                              <AlertDialogCancel>Cancel</AlertDialogCancel>
                              <AlertDialogAction onClick={() => handleDelete(property.id, property.title)}>
                                Delete
                              </AlertDialogAction>
                            </AlertDialogFooter>
                          </AlertDialogContent>
                        </AlertDialog>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
          </>
        )}

        {/* Bulk Actions Dialog */}
        <Dialog open={showBulkDialog} onOpenChange={setShowBulkDialog}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Bulk Actions</DialogTitle>
              <DialogDescription>
                Perform actions on {selectedProperties.size} selected properties
              </DialogDescription>
            </DialogHeader>

            <div className="space-y-4">
              <div>
                <Label>Select Action</Label>
                <div className="space-y-2 mt-2">
                  <Button
                    variant={bulkAction === 'activate' ? 'default' : 'outline'}
                    className="w-full justify-start"
                    onClick={() => setBulkAction('activate')}
                  >
                    <CheckSquare className="h-4 w-4 mr-2" />
                    Activate Properties
                  </Button>
                  <Button
                    variant={bulkAction === 'deactivate' ? 'default' : 'outline'}
                    className="w-full justify-start"
                    onClick={() => setBulkAction('deactivate')}
                  >
                    <XSquare className="h-4 w-4 mr-2" />
                    Deactivate Properties
                  </Button>
                  <Button
                    variant={bulkAction === 'update_prices' ? 'default' : 'outline'}
                    className="w-full justify-start"
                    onClick={() => setBulkAction('update_prices')}
                  >
                    <DollarSign className="h-4 w-4 mr-2" />
                    Update Prices
                  </Button>
                  <Button
                    variant={bulkAction === 'delete' ? 'destructive' : 'outline'}
                    className="w-full justify-start"
                    onClick={() => setBulkAction('delete')}
                  >
                    <Trash2 className="h-4 w-4 mr-2" />
                    Delete Properties
                  </Button>
                </div>
              </div>

              {bulkAction === 'update_prices' && (
                <div>
                  <Label htmlFor="bulk-price">New Price per Night</Label>
                  <Input
                    id="bulk-price"
                    type="number"
                    placeholder="Enter new price"
                    value={bulkPriceUpdate}
                    onChange={(e) => setBulkPriceUpdate(e.target.value)}
                  />
                </div>
              )}

              <div className="flex justify-end space-x-2">
                <Button variant="outline" onClick={() => setShowBulkDialog(false)}>
                  Cancel
                </Button>
                <Button onClick={handleBulkAction} disabled={!bulkAction}>
                  Apply
                </Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </MainLayout>
  );
}
