'use client';

import { useAuth } from '@/contexts/auth-context';
import { useRouter } from 'next/navigation';
import { useEffect, Suspense } from 'react';
import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import dynamic from 'next/dynamic';
import {
  Users,
  Home,
  DollarSign,
  MessageSquare,
  Shield,
  Settings,
  BarChart3,
  Activity,
  AlertTriangle,
  CheckCircle2,
  Clock,
  TrendingUp,
} from 'lucide-react';
import Link from 'next/link';

const DynamicAdminStatsCards = dynamic(() => import('@/components/admin/stats-cards').then(m => m.AdminStatsCards), { ssr: false });
const DynamicAdminLoading = dynamic(() => import('@/components/admin/loading-skeleton').then(m => m.AdminLoadingSkeleton), { ssr: false });

export default function AdminDashboardPage() {
  const { user, loading } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!loading && (!user || user.role !== 'admin')) {
      router.push('/');
    }
  }, [user, loading, router]);

  if (loading) {
    return (
      <MainLayout>
        <div className="container mx-auto px-4 py-12">
          <DynamicAdminLoading />
        </div>
      </MainLayout>
    );
  }

  if (!user || user.role !== 'admin') {
    return null;
  }

  return (
    <MainLayout>
      <div className="container mx-auto px-4 py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold mb-2">Admin Dashboard</h1>
          <p className="text-muted-foreground">
            Manage your platform, users, and monitor system health
          </p>
        </div>

        {/* Quick Stats (dynamically loaded) */}
        <div className="mb-8">
          <Suspense fallback={<div className="h-32" />}>
            <DynamicAdminStatsCards stats={{ totalUsers: 2543, activeUsers: 312, totalRevenue: 124567, monthlyGrowth: 12 }} />
          </Suspense>
        </div>

        {/* Main Content Tabs */}
        <Tabs defaultValue="overview" className="space-y-6">
          <TabsList>
            <TabsTrigger value="overview">Overview</TabsTrigger>
            <TabsTrigger value="users">Users</TabsTrigger>
            <TabsTrigger value="properties">Properties</TabsTrigger>
            <TabsTrigger value="system">System Health</TabsTrigger>
          </TabsList>

          <TabsContent value="overview" className="space-y-6">
            <div className="grid md:grid-cols-2 gap-6">
              {/* Recent Activity */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Activity className="h-5 w-5" />
                    Recent Activity
                  </CardTitle>
                  <CardDescription>Latest platform activities</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {[
                      { action: 'New user registered', user: 'john@example.com', time: '5 mins ago' },
                      { action: 'Property listed', user: 'Sarah M.', time: '12 mins ago' },
                      { action: 'Booking created', user: 'Mike T.', time: '23 mins ago' },
                      { action: 'Payment processed', user: 'Emma W.', time: '1 hour ago' },
                    ].map((activity, i) => (
                      <div key={i} className="flex items-start gap-3 text-sm">
                        <div className="h-2 w-2 bg-primary rounded-full mt-2"></div>
                        <div className="flex-1">
                          <p className="font-medium">{activity.action}</p>
                          <p className="text-muted-foreground">
                            {activity.user} • {activity.time}
                          </p>
                        </div>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>

              {/* System Status */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <Shield className="h-5 w-5" />
                    System Status
                  </CardTitle>
                  <CardDescription>Platform health metrics</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {[
                      { service: 'API Server', status: 'operational', uptime: '99.9%' },
                      { service: 'Database', status: 'operational', uptime: '99.8%' },
                      { service: 'File Storage', status: 'operational', uptime: '100%' },
                      { service: 'Email Service', status: 'degraded', uptime: '98.2%' },
                    ].map((service, i) => (
                      <div key={i} className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                          {service.status === 'operational' ? (
                            <CheckCircle2 className="h-4 w-4 text-green-500" />
                          ) : (
                            <AlertTriangle className="h-4 w-4 text-yellow-500" />
                          )}
                          <span className="font-medium">{service.service}</span>
                        </div>
                        <span className="text-sm text-muted-foreground">{service.uptime}</span>
                      </div>
                    ))}
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Quick Actions */}
            <Card>
              <CardHeader>
                <CardTitle>Quick Actions</CardTitle>
                <CardDescription>Common administrative tasks</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="grid md:grid-cols-3 gap-4">
                  <Button asChild variant="outline" className="justify-start">
                    <Link href="/admin/users">
                      <Users className="mr-2 h-4 w-4" />
                      Manage Users
                    </Link>
                  </Button>
                  <Button asChild variant="outline" className="justify-start">
                    <Link href="/admin/properties">
                      <Home className="mr-2 h-4 w-4" />
                      Review Properties
                    </Link>
                  </Button>
                  <Button asChild variant="outline" className="justify-start">
                    <Link href="/admin/settings">
                      <Settings className="mr-2 h-4 w-4" />
                      System Settings
                    </Link>
                  </Button>
                  <Button asChild variant="outline" className="justify-start">
                    <Link href="/admin/reports">
                      <BarChart3 className="mr-2 h-4 w-4" />
                      View Reports
                    </Link>
                  </Button>
                  <Button asChild variant="outline" className="justify-start">
                    <Link href="/admin/support">
                      <MessageSquare className="mr-2 h-4 w-4" />
                      Support Tickets
                    </Link>
                  </Button>
                  <Button asChild variant="outline" className="justify-start">
                    <Link href="/admin/security">
                      <Shield className="mr-2 h-4 w-4" />
                      Security Logs
                    </Link>
                  </Button>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="users">
            <Card>
              <CardHeader>
                <CardTitle>User Management</CardTitle>
                <CardDescription>Manage platform users and permissions</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">
                  User management interface coming soon...
                </p>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="properties">
            <Card>
              <CardHeader>
                <CardTitle>Property Management</CardTitle>
                <CardDescription>Review and moderate property listings</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">
                  Property management interface coming soon...
                </p>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="system">
            <Card>
              <CardHeader>
                <CardTitle>System Health</CardTitle>
                <CardDescription>Monitor system performance and health</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="text-muted-foreground">
                  System health monitoring coming soon...
                </p>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </div>
    </MainLayout>
  );
}
