'use client';

import { useEffect, useState, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/auth-context';
import { useToast } from '@/hooks/use-toast';
import { MainLayout } from '@/components/layouts/main-layout';
import { TooltipProvider } from '@/components/ui/tooltip';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
  Shield,
  Search,
  CheckCircle,
  XCircle,
  AlertTriangle,
  Eye,
  User,
  Mail,
  Phone,
  MapPin,
  Calendar,
  DollarSign,
} from 'lucide-react';
import { guestScreeningService } from '@/lib/api-service';
import { formatDate } from '@/lib/utils';

interface GuestScreening {
  id: number;
  booking_id: number;
  guest_id: number;
  trust_score: number;
  identity_verified: boolean;
  background_check_status: 'pending' | 'passed' | 'failed' | 'reviewing';
  criminal_record_check: boolean;
  eviction_history: boolean;
  credit_score: number | null;
  employment_verified: boolean;
  previous_rental_references: number;
  risk_level: 'low' | 'medium' | 'high';
  screening_notes: string | null;
  approved_by: string | null;
  screened_at: string;
  guest: {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    avatar_url: string | null;
    verified: boolean;
    member_since: string;
  };
  booking: {
    id: number;
    check_in: string;
    check_out: string;
    total_price: number;
    status: string;
    property: {
      id: number;
      title: string;
      address: string;
    };
  };
}

export default function GuestScreeningPage() {
  const router = useRouter();
  const { user } = useAuth();
  const { toast } = useToast();
  const [screenings, setScreenings] = useState<GuestScreening[]>([]);
  const [loading, setLoading] = useState(true);
  const [searchQuery, setSearchQuery] = useState('');
  const [activeTab, setActiveTab] = useState('all');

  const fetchScreenings = useCallback(async () => {
    try {
      const response = await guestScreeningService.list();
      setScreenings(response.data || []);
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to load guest screenings',
        variant: 'destructive',
      });
    } finally {
      setLoading(false);
    }
  }, [toast]);

  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }
    fetchScreenings();
  }, [user, router, fetchScreenings]);

  const handleApproveGuest = useCallback(async (screeningId: number) => {
    try {
      await guestScreeningService.approve(screeningId, { notes: 'Approved by host' });
      toast({
        title: 'Success',
        description: 'Guest screening approved',
      });
      fetchScreenings();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to approve guest',
        variant: 'destructive',
      });
    }
  }, [toast, fetchScreenings]);

  const handleRejectGuest = useCallback(async (screeningId: number) => {
    if (!confirm('Are you sure you want to reject this guest? This action cannot be undone.')) {
      return;
    }

    try {
      await guestScreeningService.reject(screeningId, { notes: 'Rejected by host' });
      toast({
        title: 'Success',
        description: 'Guest screening rejected',
      });
      fetchScreenings();
    } catch (error: any) {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to reject guest',
        variant: 'destructive',
      });
    }
  }, [toast, fetchScreenings]);

  const getRiskColor = useCallback((risk: string) => {
    switch (risk) {
      case 'low':
        return 'bg-green-100 text-green-800';
      case 'medium':
        return 'bg-yellow-100 text-yellow-800';
      case 'high':
        return 'bg-red-100 text-red-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  }, []);

  const getBackgroundCheckColor = useCallback((status: string) => {
    switch (status) {
      case 'passed':
        return 'bg-green-100 text-green-800';
      case 'failed':
        return 'bg-red-100 text-red-800';
      case 'reviewing':
        return 'bg-blue-100 text-blue-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  }, []);

  const filteredScreenings = screenings.filter((screening) => {
    const matchesSearch =
      screening.guest.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      screening.guest.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
      screening.booking.property.title.toLowerCase().includes(searchQuery.toLowerCase());

    const matchesTab =
      activeTab === 'all' ||
      (activeTab === 'pending' && screening.background_check_status === 'pending') ||
      (activeTab === 'approved' && screening.background_check_status === 'passed') ||
      (activeTab === 'rejected' && screening.background_check_status === 'failed') ||
      (activeTab === 'high-risk' && screening.risk_level === 'high');

    return matchesSearch && matchesTab;
  });

  if (!user) {
    return null;
  }

  return (
  <TooltipProvider>
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-7xl">
        <div className="mb-6 animate-fade-in" style={{ animationDelay: '0ms' }}>
          <h1 className="text-3xl font-bold mb-2 flex items-center animate-fade-in" style={{ animationDelay: '0ms' }}>
            <Shield className="h-8 w-8 mr-3 text-primary" />
            Guest Screening Dashboard
          </h1>
          <p className="text-gray-600 animate-fade-in" style={{ animationDelay: '100ms' }}>
            Review and approve guests before their bookings are confirmed
          </p>
        </div>

        {/* Search Bar */}
        <div className="mb-6">
          <div className="relative">
            <Search className="absolute left-3 top-3 h-5 w-5 text-gray-400" />
            <Input
              placeholder="Search by guest name, email, or property..."
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-10"
            />
          </div>
        </div>

        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <Card>
            <CardContent className="pt-6">
              <div className="text-center">
                <p className="text-2xl font-bold">
                  {screenings.filter((s) => s.background_check_status === 'pending').length}
                </p>
                <p className="text-sm text-gray-600">Pending Review</p>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="text-center">
                <p className="text-2xl font-bold text-green-600">
                  {screenings.filter((s) => s.background_check_status === 'passed').length}
                </p>
                <p className="text-sm text-gray-600">Approved</p>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="text-center">
                <p className="text-2xl font-bold text-red-600">
                  {screenings.filter((s) => s.background_check_status === 'failed').length}
                </p>
                <p className="text-sm text-gray-600">Rejected</p>
              </div>
            </CardContent>
          </Card>
          <Card>
            <CardContent className="pt-6">
              <div className="text-center">
                <p className="text-2xl font-bold text-orange-600">
                  {screenings.filter((s) => s.risk_level === 'high').length}
                </p>
                <p className="text-sm text-gray-600">High Risk</p>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Tabs */}
        <Tabs value={activeTab} onValueChange={setActiveTab}>
          <TabsList>
            <TabsTrigger value="all">All Screenings</TabsTrigger>
            <TabsTrigger value="pending">Pending</TabsTrigger>
            <TabsTrigger value="approved">Approved</TabsTrigger>
            <TabsTrigger value="rejected">Rejected</TabsTrigger>
            <TabsTrigger value="high-risk">High Risk</TabsTrigger>
          </TabsList>

          <TabsContent value={activeTab} className="mt-6">
            {loading ? (
              <div className="text-center py-12">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
                <p className="text-gray-600 mt-4">Loading screenings...</p>
              </div>
            ) : filteredScreenings.length === 0 ? (
              <div className="text-center py-12">
                <Shield className="h-16 w-16 text-gray-300 mx-auto mb-4" />
                <p className="text-gray-600">No guest screenings found</p>
              </div>
            ) : (
              <div className="space-y-4">
                {filteredScreenings.map((screening) => (
                  <Card key={screening.id} className="hover:shadow-md transition-shadow">
                    <CardContent className="p-6">
                      <div className="flex items-start justify-between">
                        {/* Guest Info */}
                        <div className="flex items-start space-x-4 flex-1">
                          <Avatar className="h-12 w-12">
                            <AvatarImage src={screening.guest.avatar_url || undefined} />
                            <AvatarFallback>
                              {screening.guest.name
                                .split(' ')
                                .map((n) => n[0])
                                .join('')}
                            </AvatarFallback>
                          </Avatar>

                          <div className="flex-1">
                            <div className="flex items-center space-x-2 mb-2">
                              <h3 className="text-lg font-semibold">{screening.guest.name}</h3>
                              {screening.identity_verified && (
                                <CheckCircle className="h-5 w-5 text-green-600" />
                              )}
                              <Badge className={getRiskColor(screening.risk_level)}>
                                {screening.risk_level.toUpperCase()} RISK
                              </Badge>
                              <Badge className={getBackgroundCheckColor(screening.background_check_status)}>
                                {screening.background_check_status.toUpperCase()}
                              </Badge>
                            </div>

                            <div className="grid grid-cols-2 gap-4 text-sm text-gray-600 mb-4">
                              <div className="flex items-center">
                                <Mail className="h-4 w-4 mr-2" />
                                {screening.guest.email}
                              </div>
                              {screening.guest.phone && (
                                <div className="flex items-center">
                                  <Phone className="h-4 w-4 mr-2" />
                                  {screening.guest.phone}
                                </div>
                              )}
                              <div className="flex items-center">
                                <MapPin className="h-4 w-4 mr-2" />
                                {screening.booking.property.title}
                              </div>
                              <div className="flex items-center">
                                <Calendar className="h-4 w-4 mr-2" />
                                {formatDate(screening.booking.check_in)} -{' '}
                                {formatDate(screening.booking.check_out)}
                              </div>
                            </div>

                            {/* Trust Score & Verification */}
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                              <div className="bg-gray-50 rounded-lg p-3">
                                <p className="text-xs text-gray-600 mb-1">Trust Score</p>
                                <p className="text-2xl font-bold text-primary">
                                  {screening.trust_score}%
                                </p>
                              </div>
                              {screening.credit_score && (
                                <div className="bg-gray-50 rounded-lg p-3">
                                  <p className="text-xs text-gray-600 mb-1">Credit Score</p>
                                  <p className="text-2xl font-bold">{screening.credit_score}</p>
                                </div>
                              )}
                              <div className="bg-gray-50 rounded-lg p-3">
                                <p className="text-xs text-gray-600 mb-1">References</p>
                                <p className="text-2xl font-bold">
                                  {screening.previous_rental_references}
                                </p>
                              </div>
                              <div className="bg-gray-50 rounded-lg p-3">
                                <p className="text-xs text-gray-600 mb-1">Booking Value</p>
                                <p className="text-lg font-bold">
                                  ${screening.booking.total_price}
                                </p>
                              </div>
                            </div>

                            {/* Verification Checklist */}
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-2 mb-4">
                              <div className="flex items-center space-x-2">
                                {screening.identity_verified ? (
                                  <CheckCircle className="h-4 w-4 text-green-600" />
                                ) : (
                                  <XCircle className="h-4 w-4 text-red-600" />
                                )}
                                <span className="text-sm">Identity Verified</span>
                              </div>
                              <div className="flex items-center space-x-2">
                                {screening.employment_verified ? (
                                  <CheckCircle className="h-4 w-4 text-green-600" />
                                ) : (
                                  <XCircle className="h-4 w-4 text-red-600" />
                                )}
                                <span className="text-sm">Employment</span>
                              </div>
                              <div className="flex items-center space-x-2">
                                {!screening.criminal_record_check ? (
                                  <CheckCircle className="h-4 w-4 text-green-600" />
                                ) : (
                                  <AlertTriangle className="h-4 w-4 text-orange-600" />
                                )}
                                <span className="text-sm">Criminal Check</span>
                              </div>
                              <div className="flex items-center space-x-2">
                                {!screening.eviction_history ? (
                                  <CheckCircle className="h-4 w-4 text-green-600" />
                                ) : (
                                  <AlertTriangle className="h-4 w-4 text-orange-600" />
                                )}
                                <span className="text-sm">Eviction History</span>
                              </div>
                            </div>

                            {screening.screening_notes && (
                              <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
                                <p className="text-sm text-yellow-900">
                                  <strong>Notes:</strong> {screening.screening_notes}
                                </p>
                              </div>
                            )}
                          </div>
                        </div>

                        {/* Actions */}
                        <div className="flex flex-col space-y-2 ml-4">
                          <Button
                            size="sm"
                            variant="outline"
                            onClick={() => router.push(`/bookings/${screening.booking_id}`)}
                          >
                            <Eye className="h-4 w-4 mr-2" />
                            View Booking
                          </Button>
                          {screening.background_check_status === 'pending' && (
                            <>
                              <Button
                                size="sm"
                                variant="default"
                                onClick={() => handleApproveGuest(screening.id)}
                                className="bg-green-600 hover:bg-green-700"
                              >
                                <CheckCircle className="h-4 w-4 mr-2" />
                                Approve
                              </Button>
                              <Button
                                size="sm"
                                variant="destructive"
                                onClick={() => handleRejectGuest(screening.id)}
                              >
                                <XCircle className="h-4 w-4 mr-2" />
                                Reject
                              </Button>
                            </>
                          )}
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </div>
            )}
          </TabsContent>
        </Tabs>
      </div>
    </MainLayout>
  </TooltipProvider>
  );
}
