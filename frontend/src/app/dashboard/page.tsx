'use client';

import { useEffect, useState } from 'react';
import { useRouter } from 'next/navigation';
import { useAuth } from '@/contexts/auth-context';
import { MainLayout } from '@/components/layouts/main-layout';
import { DashboardSkeleton } from '@/components/skeletons';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import Link from 'next/link';
import {
  Home,
  Calendar,
  DollarSign,
  Users,
  MessageSquare,
  CreditCard,
  Clock,
  BarChart,
  ArrowRight,
    TrendingUp,
    TrendingDown,
} from 'lucide-react';
import { StatCard } from '@/components/dashboard/StatCard';
import { BookingsList, type BookingItem } from '@/components/dashboard/BookingsList';
import { ActivityTimeline, type ActivityItem as ActivityEntry } from '@/components/dashboard/ActivityTimeline';
import { useDashboardStats } from '@/hooks/useDashboardStats';

interface StatBlock { label: string; value: string | number; description: string; icon: React.ReactNode; }

type UpcomingBooking = BookingItem;

interface PaymentReminder {
  id: number;
  invoice: string;
  amount: number;
  dueDate: string;
  status: 'pending' | 'overdue';
}

interface MessagePreview {
  id: number;
  from: string;
  excerpt: string;
  date: string; // ISO
  unread: boolean;
}

type ActivityItem = ActivityEntry;

export default function DashboardPage() {
  const { user, isAuthenticated, isLoading } = useAuth();
  const router = useRouter();
  const [stats, setStats] = useState<StatBlock[]>([]);
  const [upcoming, setUpcoming] = useState<UpcomingBooking[]>([]);
  const [payments, setPayments] = useState<PaymentReminder[]>([]);
  const [messages, setMessages] = useState<MessagePreview[]>([]);
  const [activity, setActivity] = useState<ActivityItem[]>([]);
  const [loadingData, setLoadingData] = useState(true);

  useEffect(() => {
    if (!isLoading && !isAuthenticated) {
      router.push('/auth/login');
    }
  }, [isAuthenticated, isLoading, router]);

  const { data: dashboardStats, isFetching } = useDashboardStats();

  // Map stats once data is available
  useEffect(() => {
    if (!dashboardStats) return;
    setStats([
      {
        label: 'My Properties',
        value: dashboardStats.properties,
        description: 'Active listings',
        icon: <Home className="h-4 w-4 text-muted-foreground" />,
      },
      {
        label: 'Bookings',
        value: dashboardStats.bookingsUpcoming,
        description: 'Upcoming stays',
        icon: <Calendar className="h-4 w-4 text-muted-foreground" />,
      },
      {
        label: 'Revenue',
        value: `${dashboardStats.revenueLast30 ?? '4,350'} RON`,
        description: 'Last 30 days',
        icon: <DollarSign className="h-4 w-4 text-muted-foreground" />,
      },
      {
        label: 'Guests',
        value: dashboardStats.guestsUnique,
        description: 'Total unique',
        icon: <Users className="h-4 w-4 text-muted-foreground" />,
      },
    ]);
  }, [dashboardStats]);

  // Mock data placeholders (will be replaced by dedicated hooks later)
  useEffect(() => {
    if (!isAuthenticated) return;
    setUpcoming([
        {
          id: 101,
          property: 'Luxury Apartment Downtown',
          checkIn: '2025-11-15',
          checkOut: '2025-11-18',
          nights: 3,
          status: 'confirmed',
        },
        {
          id: 102,
          property: 'Cozy Studio Near Park',
          checkIn: '2025-11-22',
          checkOut: '2025-11-24',
          nights: 2,
          status: 'pending',
        },
      ]);
      setPayments([
        {
          id: 301,
          invoice: 'INV-000145',
          amount: 1260.5,
          dueDate: '2025-11-10',
          status: 'pending',
        },
        {
          id: 302,
          invoice: 'INV-000144',
          amount: 980.0,
          dueDate: '2025-11-05',
          status: 'overdue',
        },
      ]);
      setMessages([
        {
          id: 501,
          from: 'Anna Popescu',
          excerpt: 'Bună! Voi ajunge mai târziu pentru check-in...',
          date: '2025-11-07T09:15:00Z',
          unread: true,
        },
        {
          id: 502,
          from: 'Mihai Ionescu',
          excerpt: 'Mulțumesc pentru confirmare! Ne vedem luni.',
          date: '2025-11-06T16:40:00Z',
          unread: false,
        },
      ]);
      setActivity([
        {
          id: 601,
          type: 'booking',
          title: 'New booking #101 confirmed',
          date: '2025-11-07T08:30:00Z',
          icon: <Calendar className="h-4 w-4" />,
        },
        {
          id: 602,
          type: 'payment',
          title: 'Invoice INV-000144 overdue',
          date: '2025-11-07T07:10:00Z',
          icon: <CreditCard className="h-4 w-4" />,
        },
        {
          id: 603,
          type: 'message',
          title: 'New message from Anna',
          date: '2025-11-07T06:55:00Z',
          icon: <MessageSquare className="h-4 w-4" />,
        },
      ]);
    setLoadingData(false);
  }, [isAuthenticated]);

  if (isLoading || isFetching) {
    return (
      <MainLayout>
        <DashboardSkeleton />
      </MainLayout>
    );
  }

  if (!isAuthenticated) {
    return null;
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        <div className="mb-8">
            <div className="flex items-center justify-between">
              <div>
                <h1 className="text-3xl font-bold tracking-tight">Welcome back, {user?.name}!</h1>
                <p className="text-muted-foreground mt-1">Overview of your hosting performance and guest interactions</p>
              </div>
              <Link href="/dashboard/analytics">
                <Button variant="outline" size="sm" className="gap-2">
                  <BarChart className="h-4 w-4" />
                  View Analytics
                </Button>
              </Link>
            </div>
          {/* Live region for stats summary */}
          <p className="sr-only" aria-live="polite" aria-atomic="true">
            {stats.length > 0 && `Dashboard loaded: ${stats[0]?.value} properties, ${stats[1]?.value} bookings, ${stats[2]?.value} revenue`}
          </p>
        </div>

        {/* Stats */}
          <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-8">
          {(loadingData ? Array.from({ length: 4 }).map(() => null) : stats).map((stat, idx) => (
              <Card key={idx}>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">
                    {stat ? stat.label : ''}
                  </CardTitle>
                  {stat?.icon}
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stat ? stat.value : ''}</div>
                  <p className="text-xs text-muted-foreground flex items-center gap-1 mt-1">
                    {idx === 2 ? (
                      <><TrendingUp className="h-3 w-3 text-green-500" /> +12% from last month</>
                    ) : idx === 3 ? (
                      <><TrendingDown className="h-3 w-3 text-red-500" /> -2% from last month</>
                    ) : (
                      <>{stat ? stat.description : ''}</>
                    )}
                  </p>
                </CardContent>
              </Card>
          ))}
        </div>

        <div className="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
          {/* Upcoming Bookings */}
          <BookingsList bookings={upcoming} loading={loadingData} />

          {/* Payment Reminders */}
          <Card className="animate-fade-in-up" style={{ animationDelay: '240ms' }}>
            <CardHeader>
              <CardTitle>Payment Reminders</CardTitle>
              <CardDescription>Invoices requiring attention</CardDescription>
            </CardHeader>
            <CardContent>
              {loadingData ? (
                <div className="space-y-3">
                  {Array.from({ length: 3 }).map((_, i) => (
                    <div key={i} className="h-12 w-full rounded-md bg-muted animate-pulse" />
                  ))}
                </div>
              ) : payments.length === 0 ? (
                <p className="text-sm text-muted-foreground">No pending invoices</p>
              ) : (
                <div className="space-y-3">
                  {payments.map((p, idx) => (
                    <div
                      key={p.id}
                      className="border rounded-md p-3 text-xs flex items-start justify-between gap-3 animate-fade-in-up"
                      style={{ animationDelay: `${idx * 60}ms` }}
                    >
                      <div className="space-y-1">
                        <div className="flex items-center gap-2">
                          <CreditCard className="h-3 w-3 text-muted-foreground" />
                          <span className="font-medium">{p.invoice}</span>
                        </div>
                        <div className="flex flex-wrap gap-2 text-muted-foreground">
                          <span>{p.amount.toFixed(2)} RON</span>
                          <span className="flex items-center gap-1"><Clock className="h-3 w-3" /> Due {new Date(p.dueDate).toLocaleDateString('ro-RO')}</span>
                        </div>
                        <span className={p.status === 'overdue' ? 'text-red-600 font-semibold' : 'text-amber-600 font-semibold'}>
                          {p.status === 'overdue' ? 'Overdue' : 'Pending'}
                        </span>
                      </div>
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger asChild>
                            <Button variant="outline" size="sm" asChild>
                              <Link href={`/payments/history`}>Pay</Link>
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>Pay invoice</TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        <div className="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
          {/* Recent Messages */}
          <Card className="xl:col-span-1 order-2 xl:order-1 animate-fade-in-up" style={{ animationDelay: '320ms' }}>
            <CardHeader className="flex flex-row items-center justify-between">
              <div>
                <CardTitle>Recent Messages</CardTitle>
                <CardDescription>Latest guest conversations</CardDescription>
              </div>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" size="sm" asChild>
                      <Link href="/messages">Inbox</Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>View all messages</TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </CardHeader>
            <CardContent>
              {loadingData ? (
                <div className="space-y-3">
                  {Array.from({ length: 4 }).map((_, i) => (
                    <div key={i} className="h-10 w-full rounded bg-muted animate-pulse" />
                  ))}
                </div>
              ) : messages.length === 0 ? (
                <p className="text-sm text-muted-foreground">No messages</p>
              ) : (
                <div className="space-y-4">
                  {messages.map((m, idx) => (
                    <div key={m.id} className="flex items-start justify-between gap-3 border rounded-lg p-3 hover:bg-muted/50 transition animate-fade-in-up" style={{ animationDelay: `${idx * 60}ms` }}>
                      <div className="space-y-1">
                        <div className="flex items-center gap-2">
                          <MessageSquare className="h-4 w-4 text-muted-foreground" />
                          <span className="text-sm font-medium">{m.from}</span>
                          {m.unread && <span className="h-2 w-2 rounded-full bg-primary" />}
                        </div>
                        <p className="text-xs text-muted-foreground line-clamp-1">{m.excerpt}</p>
                        <span className="text-[10px] text-muted-foreground">{new Date(m.date).toLocaleDateString('ro-RO')} {new Date(m.date).toLocaleTimeString('ro-RO', { hour: '2-digit', minute: '2-digit' })}</span>
                      </div>
                      <TooltipProvider>
                        <Tooltip>
                          <TooltipTrigger asChild>
                            <Button variant="ghost" size="sm" asChild>
                              <Link href={`/messages?open=${m.id}`}>Open</Link>
                            </Button>
                          </TooltipTrigger>
                          <TooltipContent>Open conversation</TooltipContent>
                        </Tooltip>
                      </TooltipProvider>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>

          {/* Revenue Overview */}
          <Card className="xl:col-span-2 order-1 xl:order-2 animate-fade-in-up" style={{ animationDelay: '400ms' }}>
            <CardHeader className="flex flex-row items-center justify-between">
              <div>
                <CardTitle>Revenue Overview</CardTitle>
                <CardDescription>Last 6 months performance (mock)</CardDescription>
              </div>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" size="sm" asChild>
                      <Link href="/payments/history">Details</Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>View payment history</TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </CardHeader>
            <CardContent>
              <div className="h-48 w-full flex items-end gap-2">
                {/* Simple bar chart placeholder */}
                {['3.2k','4.1k','3.8k','4.5k','4.0k','4.35k'].map((val,i)=>{
                  const heights=[60,90,80,110,95,105];
                  return (
                    <div key={i} className="flex-1 flex flex-col items-center gap-2">
                      <div className="bg-primary/20 w-full rounded-t" style={{ height: heights[i] }} />
                      <span className="text-[10px] text-muted-foreground">{val}</span>
                    </div>
                  )
                })}
              </div>
              <div className="mt-4 text-xs text-muted-foreground flex justify-between">
                {['Jun','Jul','Aug','Sep','Oct','Nov'].map(m=> <span key={m}>{m}</span>)}
              </div>
              <div className="mt-4 flex flex-wrap gap-3 text-xs">
                <span className="flex items-center gap-1"><BarChart className="h-3 w-3" />Avg / mo: <strong>4.16k RON</strong></span>
                <span className="flex items-center gap-1"><Clock className="h-3 w-3" />Last payout: <strong>Nov 02</strong></span>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Activity Timeline & Quick Actions */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <ActivityTimeline items={activity} loading={loadingData} />
          <Card className="animate-fade-in-up" style={{ animationDelay: '480ms' }}>
            <CardHeader>
              <CardTitle>Quick Actions</CardTitle>
              <CardDescription>Frequently used shortcuts</CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" className="w-full justify-between" asChild>
                      <Link href="/dashboard/properties">
                        <span className="flex items-center gap-2"><Home className="h-4 w-4" /> Manage Properties</span>
                        <ArrowRight className="h-4 w-4" />
                      </Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>View and manage your listings</TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" className="w-full justify-between" asChild>
                      <Link href="/dashboard/bookings">
                        <span className="flex items-center gap-2"><Calendar className="h-4 w-4" /> View Bookings</span>
                        <ArrowRight className="h-4 w-4" />
                      </Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Check your reservations</TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" className="w-full justify-between" asChild>
                      <Link href="/messages">
                        <span className="flex items-center gap-2"><MessageSquare className="h-4 w-4" /> Open Inbox</span>
                        <ArrowRight className="h-4 w-4" />
                      </Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>View messages</TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" className="w-full justify-between" asChild>
                      <Link href="/payments/history">
                        <span className="flex items-center gap-2"><CreditCard className="h-4 w-4" /> Payments</span>
                        <ArrowRight className="h-4 w-4" />
                      </Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Payment history and invoices</TooltipContent>
                </Tooltip>
              </TooltipProvider>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Button variant="outline" className="w-full justify-between" asChild>
                      <Link href="/profile">
                        <span className="flex items-center gap-2"><Users className="h-4 w-4" /> Profile Settings</span>
                        <ArrowRight className="h-4 w-4" />
                      </Link>
                    </Button>
                  </TooltipTrigger>
                  <TooltipContent>Update your profile</TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </CardContent>
          </Card>
        </div>
      </div>
    </MainLayout>
  );
}
