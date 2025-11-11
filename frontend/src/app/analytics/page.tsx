'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { MainLayout } from '@/components/layouts/main-layout';
import { useAuth } from '@/contexts/auth-context';
import { useToast } from '@/hooks/use-toast';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import apiClient from '@/lib/api-client';
import { API_ENDPOINTS } from '@/lib/api-endpoints';
import { DollarSign, Calendar, TrendingUp, TrendingDown } from 'lucide-react';
import {
  LineChart,
  Line,
  BarChart,
  Bar,
  PieChart,
  Pie,
  Cell,
  XAxis,
  YAxis,
  CartesianGrid,
  Tooltip,
  Legend,
  ResponsiveContainer,
} from 'recharts';

interface RevenuePoint { month: string; revenue: number; bookings: number }
interface BookingTrendPoint { date: string; bookings: number; revenue: number }
interface OccupancyPoint { rate: number; bookedNights: number; availableNights: number }
interface BookingStatusPoint { status: string; count: number }
interface DemographicPoint { name: string; value: number }

export default function GlobalAnalyticsPage() {
  const router = useRouter();
  const { user } = useAuth();
  const { toast } = useToast();

  const [range, setRange] = useState<'7' | '30' | '90' | '365'>('30');
  const [loading, setLoading] = useState(true);

  const [revenueSummary, setRevenueSummary] = useState<{ total: number; growth: number; monthly: RevenuePoint[] }>({ total: 0, growth: 0, monthly: [] });
  const [bookingTrends, setBookingTrends] = useState<BookingTrendPoint[]>([]);
  const [occupancy, setOccupancy] = useState<{ rate: number; bookedNights: number; availableNights: number }>({ rate: 0, bookedNights: 0, availableNights: 0 });
  const [bookingStatus, setBookingStatus] = useState<BookingStatusPoint[]>([]);
  const [demographics, setDemographics] = useState<{ countries: DemographicPoint[]; ageGroups: DemographicPoint[]; genders: DemographicPoint[] }>({ countries: [], ageGroups: [], genders: [] });

  useEffect(() => {
    if (!user) {
      router.push('/auth/login');
      return;
    }
    (async () => {
      setLoading(true);
      try {
        const [rev, occ, trends, demos, status] = await Promise.all([
          apiClient.get(API_ENDPOINTS.analytics.revenue(range)).then(r => r.data.data).catch(() => null),
          apiClient.get(API_ENDPOINTS.analytics.occupancy(range)).then(r => r.data.data).catch(() => null),
          apiClient.get(API_ENDPOINTS.analytics.bookingTrends(range)).then(r => r.data.data).catch(() => null),
          apiClient.get(API_ENDPOINTS.analytics.guestDemographics(range)).then(r => r.data.data).catch(() => null),
          apiClient.get(API_ENDPOINTS.analytics.bookingStatus(range)).then(r => r.data.data).catch(() => null),
        ]);

        if (rev) setRevenueSummary(rev);
        if (occ) setOccupancy(occ);
        if (trends) setBookingTrends(trends);
        if (demos) setDemographics(demos);
        if (status) setBookingStatus(status);
      } catch (err) {
        // Provide useful defaults if backend aggregate endpoints are not ready
        setRevenueSummary({
          total: 128450,
          growth: 12.4,
          monthly: [
            { month: 'Jan', revenue: 10250, bookings: 24 },
            { month: 'Feb', revenue: 11020, bookings: 22 },
            { month: 'Mar', revenue: 12400, bookings: 26 },
            { month: 'Apr', revenue: 13150, bookings: 28 },
            { month: 'May', revenue: 14200, bookings: 30 },
            { month: 'Jun', revenue: 15150, bookings: 31 },
            { month: 'Jul', revenue: 16300, bookings: 35 },
            { month: 'Aug', revenue: 17250, bookings: 36 },
            { month: 'Sep', revenue: 16020, bookings: 33 },
            { month: 'Oct', revenue: 15400, bookings: 31 },
            { month: 'Nov', revenue: 14750, bookings: 29 },
            { month: 'Dec', revenue: 16560, bookings: 34 },
          ],
        });
        setOccupancy({ rate: 78, bookedNights: 2345, availableNights: 3000 });
        setBookingTrends([
          { date: 'Week 1', bookings: 45, revenue: 7800 },
          { date: 'Week 2', bookings: 52, revenue: 8200 },
          { date: 'Week 3', bookings: 49, revenue: 8000 },
          { date: 'Week 4', bookings: 58, revenue: 9100 },
        ]);
        setDemographics({
          countries: [
            { name: 'USA', value: 38 },
            { name: 'UK', value: 18 },
            { name: 'Germany', value: 14 },
            { name: 'France', value: 10 },
            { name: 'Other', value: 20 },
          ],
          ageGroups: [
            { name: '18-24', value: 12 },
            { name: '25-34', value: 35 },
            { name: '35-44', value: 28 },
            { name: '45-54', value: 15 },
            { name: '55+', value: 10 },
          ],
          genders: [
            { name: 'Female', value: 52 },
            { name: 'Male', value: 46 },
            { name: 'Other', value: 2 },
          ],
        });
        setBookingStatus([
          { status: 'confirmed', count: 320 },
          { status: 'pending', count: 45 },
          { status: 'cancelled', count: 28 },
          { status: 'completed', count: 290 },
        ]);
        toast({ title: 'Analytics (demo)', description: 'Using sample data until backend endpoints are live.' });
      } finally {
        setLoading(false);
      }
    })();
  }, [user, range, router, toast]);

  if (loading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-8 max-w-7xl space-y-6" aria-busy="true" aria-live="polite">
          <Skeleton className="h-8 w-1/3" />
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {[1,2,3,4].map(i => <Skeleton key={i} className="h-32 w-full" />)}
          </div>
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {[1,2,3,4].map(i => <Skeleton key={i} className="h-[360px] w-full" />)}
          </div>
        </div>
      </MainLayout>
    );
  }

  const COLORS = ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'];

  const revenueDeltaIcon = revenueSummary.growth >= 0 ? <TrendingUp className="h-4 w-4 text-emerald-600" /> : <TrendingDown className="h-4 w-4 text-red-600" />;

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8 max-w-7xl">
        <div className="flex items-start justify-between mb-6 gap-4">
          <div>
            <h1 className="text-3xl font-bold mb-2">Analytics</h1>
            <p className="text-gray-600">Aggregate performance across all your properties</p>
          </div>
          <div className="flex items-center gap-3">
            <Select value={range} onValueChange={(val) => setRange(val as any)}>
              <SelectTrigger className="w-[180px]">
                <SelectValue placeholder="Select range" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="7">Last 7 days</SelectItem>
                <SelectItem value="30">Last 30 days</SelectItem>
                <SelectItem value="90">Last 90 days</SelectItem>
                <SelectItem value="365">Last 12 months</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        {/* KPI Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <Card className="animate-fade-in-up">
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold flex items-center gap-2" aria-live="polite">
                <DollarSign className="h-5 w-5 text-primary" />
                {revenueSummary.total.toLocaleString(undefined, { style: 'currency', currency: 'USD' }).replace(/^\D*/, '')}
              </div>
              <div className="text-xs text-muted-foreground flex items-center gap-2 mt-2">
                {revenueDeltaIcon}
                <span>{Math.abs(revenueSummary.growth).toFixed(1)}% vs prev period</span>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Occupancy Rate</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold flex items-center gap-2">
                <Calendar className="h-5 w-5 text-primary" />
                {occupancy.rate.toFixed(0)}%
              </div>
              <div className="text-xs text-muted-foreground mt-2">
                {occupancy.bookedNights.toLocaleString()} booked nights / {occupancy.availableNights.toLocaleString()} available
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Bookings (last period)</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold flex items-center gap-2">
                <TrendingUp className="h-5 w-5 text-primary" />
                {revenueSummary.monthly.reduce((acc, p) => acc + p.bookings, 0)}
              </div>
              <div className="text-xs text-muted-foreground mt-2">
                Bookings across all properties
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="pb-2">
              <CardTitle className="text-sm font-medium">Avg. Booking Value</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="text-2xl font-bold flex items-center gap-2">
                ${(revenueSummary.total / Math.max(1, revenueSummary.monthly.reduce((acc, p) => acc + p.bookings, 0))).toFixed(0)}
              </div>
              <div className="text-xs text-muted-foreground mt-2">
                Revenue / total bookings
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Charts */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card className="h-[360px] animate-fade-in-up">
            <CardHeader>
              <CardTitle>Revenue & Bookings</CardTitle>
            </CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={260}>
                <LineChart data={revenueSummary.monthly} margin={{ left: 8, right: 8, top: 10 }}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="month" />
                  <YAxis yAxisId="left" />
                  <YAxis yAxisId="right" orientation="right" />
                  <Tooltip />
                  <Legend />
                  <Line yAxisId="left" type="monotone" dataKey="revenue" stroke="#2563eb" strokeWidth={2} dot={false} name="Revenue" />
                  <Line yAxisId="right" type="monotone" dataKey="bookings" stroke="#10b981" strokeWidth={2} dot={false} name="Bookings" />
                </LineChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>

          <Card className="h-[360px]">
            <CardHeader>
              <CardTitle>Booking Status</CardTitle>
            </CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={260}>
                <BarChart data={bookingStatus}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="status" />
                  <YAxis />
                  <Tooltip />
                  <Legend />
                  <Bar dataKey="count" fill="#2563eb" name="Bookings" radius={[6, 6, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>

          <Card className="h-[360px]">
            <CardHeader>
              <CardTitle>Guest Demographics (Countries)</CardTitle>
            </CardHeader>
            <CardContent>
              <ResponsiveContainer width="100%" height={260}>
                <PieChart>
                  <Pie dataKey="value" data={demographics.countries as any} outerRadius={100} label>
                    {demographics.countries.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                    ))}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
            </CardContent>
          </Card>

          <Card className="h-[360px]">
            <CardHeader>
              <CardTitle>Guest Demographics (Age & Gender)</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div className="h-[260px]">
                  <ResponsiveContainer width="100%" height="100%">
                    <PieChart>
                      <Pie dataKey="value" data={demographics.ageGroups as any} outerRadius={90} label>
                        {demographics.ageGroups.map((entry, index) => (
                          <Cell key={`age-${index}`} fill={COLORS[index % COLORS.length]} />
                        ))}
                      </Pie>
                    </PieChart>
                  </ResponsiveContainer>
                </div>
                <div className="h-[260px]">
                  <ResponsiveContainer width="100%" height="100%">
                    <PieChart>
                      <Pie dataKey="value" data={demographics.genders as any} outerRadius={90} label>
                        {demographics.genders.map((entry, index) => (
                          <Cell key={`gender-${index}`} fill={COLORS[(index + 2) % COLORS.length]} />
                        ))}
                      </Pie>
                    </PieChart>
                  </ResponsiveContainer>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </MainLayout>
  );
}
