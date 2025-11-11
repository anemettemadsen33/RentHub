'use client';

import { useCallback, useEffect, useState } from 'react';
import { useParams, useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/auth-context';
import { useToast } from '@/hooks/use-toast';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog';
import {
  Wrench,
  Plus,
  ArrowLeft,
  AlertCircle,
  CheckCircle,
  Clock,
  User,
  Calendar as CalendarIcon,
  DollarSign,
  Trash2,
} from 'lucide-react';
import apiClient from '@/lib/api-client';
import { API_ENDPOINTS } from '@/lib/api-endpoints';
import Link from 'next/link';
import { formatDate } from '@/lib/utils';

interface MaintenanceRequest {
  id: number;
  property_id: number;
  title: string;
  description: string;
  priority: 'low' | 'medium' | 'high' | 'urgent';
  status: 'pending' | 'in_progress' | 'completed' | 'cancelled';
  category: string;
  assigned_to: string | null;
  cost: number | null;
  scheduled_date: string | null;
  completed_date: string | null;
  created_at: string;
  notes: string | null;
}

interface Property {
  id: number;
  title: string;
  address: string;
}

export default function MaintenancePage() {
  const params = useParams();
  const router = useRouter();
  const { user } = useAuth();
  const { toast } = useToast();
  const [property, setProperty] = useState<Property | null>(null);
  const [requests, setRequests] = useState<MaintenanceRequest[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddDialog, setShowAddDialog] = useState(false);
  const [filterStatus, setFilterStatus] = useState<string>('all');

  const [newRequest, setNewRequest] = useState({
    title: '',
    description: '',
    priority: 'medium',
    category: 'general',
    scheduled_date: '',
  });

  const fetchPropertyAndRequests = useCallback(async () => {
    try {
      const [propertyRes, requestsRes] = await Promise.all([
        apiClient.get(API_ENDPOINTS.properties.show(params.id as string)),
        apiClient.get(API_ENDPOINTS.properties.maintenance.list(params.id as string)),
      ]);

      setProperty(propertyRes.data.data);
      setRequests(requestsRes.data.data || []);
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to load maintenance requests',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  }, [params.id, toast]);

  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }
    fetchPropertyAndRequests();
  }, [user, params.id, router, fetchPropertyAndRequests]);

  const handleCreateRequest = async () => {
    if (!newRequest.title || !newRequest.description) {
      toast({
        title: 'Error',
        description: 'Please fill in all required fields',
        variant: 'destructive',
      });
      return;
    }

    try {
      await apiClient.post(
        API_ENDPOINTS.properties.maintenance.create(params.id as string),
        newRequest
      );
      toast({
        title: 'Success',
        description: 'Maintenance request created successfully',
      });
      setShowAddDialog(false);
      setNewRequest({
        title: '',
        description: '',
        priority: 'medium',
        category: 'general',
        scheduled_date: '',
      });
      fetchPropertyAndRequests();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to create request',
        variant: 'destructive',
      });
    }
  };

  const handleUpdateStatus = async (requestId: number, status: string) => {
    try {
      await apiClient.patch(
        API_ENDPOINTS.properties.maintenance.updateStatus(params.id as string, requestId),
        { status }
      );
      toast({
        title: 'Success',
        description: 'Status updated successfully',
      });
      fetchPropertyAndRequests();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to update status',
        variant: 'destructive',
      });
    }
  };

  const handleDeleteRequest = async (requestId: number) => {
    if (!confirm('Are you sure you want to delete this maintenance request?')) {
      return;
    }

    try {
      await apiClient.delete(
        API_ENDPOINTS.properties.maintenance.delete(params.id as string, requestId)
      );
      toast({
        title: 'Success',
        description: 'Maintenance request deleted successfully',
      });
      fetchPropertyAndRequests();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to delete request',
        variant: 'destructive',
      });
    }
  };

  const getPriorityColor = (priority: string) => {
    switch (priority) {
      case 'urgent':
        return 'bg-red-100 text-red-800';
      case 'high':
        return 'bg-orange-100 text-orange-800';
      case 'medium':
        return 'bg-yellow-100 text-yellow-800';
      case 'low':
        return 'bg-blue-100 text-blue-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed':
        return 'bg-green-100 text-green-800';
      case 'in_progress':
        return 'bg-blue-100 text-blue-800';
      case 'pending':
        return 'bg-yellow-100 text-yellow-800';
      case 'cancelled':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'completed':
        return <CheckCircle className="h-4 w-4" />;
      case 'in_progress':
        return <Clock className="h-4 w-4" />;
      case 'pending':
        return <AlertCircle className="h-4 w-4" />;
      default:
        return null;
    }
  };

  const filteredRequests = requests.filter((request) =>
    filterStatus === 'all' ? true : request.status === filterStatus
  );

  if (!user) {
    return null;
  }

  if (loading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <div className="animate-pulse space-y-6">
            <div className="h-8 bg-gray-200 rounded w-1/3"></div>
            <div className="space-y-4">
              {[1, 2, 3].map((i) => (
                <div key={i} className="h-32 bg-gray-200 rounded"></div>
              ))}
            </div>
          </div>
        </div>
      </MainLayout>
    );
  }

  if (!property) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8">
          <p>Property not found</p>
        </div>
      </MainLayout>
    );
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-6xl">
        {/* Header */}
        <div className="mb-6">
          <Link href={`/properties/${params.id}`}>
            <Button variant="ghost" size="sm" className="mb-4">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Back to Property
            </Button>
          </Link>

          <div className="flex items-center justify-between">
            <div>
              <h1 className="text-3xl font-bold mb-2 flex items-center">
                <Wrench className="h-8 w-8 mr-3 text-primary" />
                Maintenance Requests
              </h1>
              <p className="text-gray-600">{property.title}</p>
            </div>

            <Button onClick={() => setShowAddDialog(true)}>
              <Plus className="h-4 w-4 mr-2" />
              New Request
            </Button>
          </div>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <Card>
            <CardContent className="pt-6">
              <div className="text-center">
                <p className="text-2xl font-bold text-yellow-600">
                  {requests.filter((r) => r.status === 'pending').length}
                </p>
                <p className="text-sm text-gray-600">Pending</p>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="text-center">
                <p className="text-2xl font-bold text-blue-600">
                  {requests.filter((r) => r.status === 'in_progress').length}
                </p>
                <p className="text-sm text-gray-600">In Progress</p>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="text-center">
                <p className="text-2xl font-bold text-green-600">
                  {requests.filter((r) => r.status === 'completed').length}
                </p>
                <p className="text-sm text-gray-600">Completed</p>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="text-center">
                <p className="text-2xl font-bold text-red-600">
                  {requests.filter((r) => r.priority === 'urgent').length}
                </p>
                <p className="text-sm text-gray-600">Urgent</p>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Filter */}
        <div className="mb-6">
          <Select value={filterStatus} onValueChange={setFilterStatus}>
            <SelectTrigger className="w-48">
              <SelectValue placeholder="Filter by status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Requests</SelectItem>
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="in_progress">In Progress</SelectItem>
              <SelectItem value="completed">Completed</SelectItem>
              <SelectItem value="cancelled">Cancelled</SelectItem>
            </SelectContent>
          </Select>
        </div>

        {/* Requests List */}
        {filteredRequests.length === 0 ? (
          <Card>
            <CardContent className="text-center py-12">
              <Wrench className="h-16 w-16 text-gray-300 mx-auto mb-4" />
              <h3 className="text-lg font-semibold mb-2">No Maintenance Requests</h3>
              <p className="text-gray-600 mb-4">
                {filterStatus === 'all'
                  ? 'Create your first maintenance request'
                  : `No ${filterStatus.replace('_', ' ')} requests`}
              </p>
              {filterStatus === 'all' && (
                <Button onClick={() => setShowAddDialog(true)}>
                  <Plus className="h-4 w-4 mr-2" />
                  Create Request
                </Button>
              )}
            </CardContent>
          </Card>
        ) : (
          <div className="space-y-4">
            {filteredRequests.map((request) => (
              <Card key={request.id} className="hover:shadow-md transition-shadow">
                <CardContent className="p-6">
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <div className="flex items-center space-x-3 mb-3">
                        <h3 className="text-lg font-semibold">{request.title}</h3>
                        <Badge className={getPriorityColor(request.priority)}>
                          {request.priority.toUpperCase()}
                        </Badge>
                        <Badge className={getStatusColor(request.status)}>
                          {getStatusIcon(request.status)}
                          <span className="ml-1">{request.status.replace('_', ' ').toUpperCase()}</span>
                        </Badge>
                      </div>

                      <p className="text-gray-600 mb-4">{request.description}</p>

                      <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div className="flex items-center text-gray-600">
                          <CalendarIcon className="h-4 w-4 mr-2" />
                          <div>
                            <p className="text-xs">Created</p>
                            <p className="font-medium">{formatDate(request.created_at)}</p>
                          </div>
                        </div>

                        {request.scheduled_date && (
                          <div className="flex items-center text-gray-600">
                            <Clock className="h-4 w-4 mr-2" />
                            <div>
                              <p className="text-xs">Scheduled</p>
                              <p className="font-medium">{formatDate(request.scheduled_date)}</p>
                            </div>
                          </div>
                        )}

                        {request.assigned_to && (
                          <div className="flex items-center text-gray-600">
                            <User className="h-4 w-4 mr-2" />
                            <div>
                              <p className="text-xs">Assigned to</p>
                              <p className="font-medium">{request.assigned_to}</p>
                            </div>
                          </div>
                        )}

                        {request.cost && (
                          <div className="flex items-center text-gray-600">
                            <DollarSign className="h-4 w-4 mr-2" />
                            <div>
                              <p className="text-xs">Cost</p>
                              <p className="font-medium">${request.cost}</p>
                            </div>
                          </div>
                        )}
                      </div>

                      {request.notes && (
                        <div className="mt-4 p-3 bg-gray-50 rounded-lg">
                          <p className="text-sm text-gray-700">
                            <strong>Notes:</strong> {request.notes}
                          </p>
                        </div>
                      )}
                    </div>

                    <div className="flex flex-col space-y-2 ml-4">
                      {request.status !== 'completed' && request.status !== 'cancelled' && (
                        <>
                          {request.status === 'pending' && (
                            <Button
                              size="sm"
                              variant="outline"
                              onClick={() => handleUpdateStatus(request.id, 'in_progress')}
                            >
                              Start Work
                            </Button>
                          )}
                          {request.status === 'in_progress' && (
                            <Button
                              size="sm"
                              variant="default"
                              className="bg-green-600 hover:bg-green-700"
                              onClick={() => handleUpdateStatus(request.id, 'completed')}
                            >
                              Mark Complete
                            </Button>
                          )}
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => handleUpdateStatus(request.id, 'cancelled')}
                          >
                            Cancel
                          </Button>
                        </>
                      )}
                      <Button
                        size="sm"
                        variant="destructive"
                        onClick={() => handleDeleteRequest(request.id)}
                      >
                        <Trash2 className="h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}

        {/* Add Request Dialog */}
        <Dialog open={showAddDialog} onOpenChange={setShowAddDialog}>
          <DialogContent className="max-w-2xl">
            <DialogHeader>
              <DialogTitle>Create Maintenance Request</DialogTitle>
              <DialogDescription>
                Submit a new maintenance request for this property
              </DialogDescription>
            </DialogHeader>

            <div className="space-y-4">
              <div>
                <Label htmlFor="title">Title *</Label>
                <Input
                  id="title"
                  placeholder="e.g., Fix leaking faucet in bathroom"
                  value={newRequest.title}
                  onChange={(e) => setNewRequest({ ...newRequest, title: e.target.value })}
                />
              </div>

              <div>
                <Label htmlFor="description">Description *</Label>
                <Textarea
                  id="description"
                  placeholder="Describe the issue in detail..."
                  value={newRequest.description}
                  onChange={(e) => setNewRequest({ ...newRequest, description: e.target.value })}
                  rows={4}
                />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="priority">Priority</Label>
                  <Select
                    value={newRequest.priority}
                    onValueChange={(value) => setNewRequest({ ...newRequest, priority: value })}
                  >
                    <SelectTrigger id="priority">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="low">Low</SelectItem>
                      <SelectItem value="medium">Medium</SelectItem>
                      <SelectItem value="high">High</SelectItem>
                      <SelectItem value="urgent">Urgent</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div>
                  <Label htmlFor="category">Category</Label>
                  <Select
                    value={newRequest.category}
                    onValueChange={(value) => setNewRequest({ ...newRequest, category: value })}
                  >
                    <SelectTrigger id="category">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="general">General</SelectItem>
                      <SelectItem value="plumbing">Plumbing</SelectItem>
                      <SelectItem value="electrical">Electrical</SelectItem>
                      <SelectItem value="hvac">HVAC</SelectItem>
                      <SelectItem value="appliance">Appliance</SelectItem>
                      <SelectItem value="cleaning">Cleaning</SelectItem>
                      <SelectItem value="landscaping">Landscaping</SelectItem>
                      <SelectItem value="other">Other</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <div>
                <Label htmlFor="scheduled_date">Scheduled Date (Optional)</Label>
                <Input
                  id="scheduled_date"
                  type="date"
                  value={newRequest.scheduled_date}
                  onChange={(e) =>
                    setNewRequest({ ...newRequest, scheduled_date: e.target.value })
                  }
                />
              </div>

              <div className="flex justify-end space-x-2">
                <Button variant="outline" onClick={() => setShowAddDialog(false)}>
                  Cancel
                </Button>
                <Button onClick={handleCreateRequest}>Create Request</Button>
              </div>
            </div>
          </DialogContent>
        </Dialog>
      </div>
    </MainLayout>
  );
}
