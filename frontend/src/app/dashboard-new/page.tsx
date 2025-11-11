'use client';

import { MainLayout } from '@/components/layouts/main-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
  Building2, 
  TrendingUp, 
  TrendingDown,
  Users, 
  DollarSign,
  Calendar,
  ArrowUpRight,
  ArrowDownRight,
  MoreHorizontal,
  Activity
} from 'lucide-react';
import Link from 'next/link';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export default function ModernDashboard() {
  const stats = [
    {
      title: 'Total Revenue',
      value: '$45,231.89',
      change: '+20.1%',
      trend: 'up',
      icon: DollarSign,
    },
    {
      title: 'Active Bookings',
      value: '+2,350',
      change: '+180.1%',
      trend: 'up',
      icon: Calendar,
    },
    {
      title: 'Properties',
      value: '12',
      change: '+19%',
      trend: 'up',
      icon: Building2,
    },
    {
      title: 'Active Users',
      value: '+573',
      change: '+201',
      trend: 'up',
      icon: Users,
    },
  ];

  const recentActivity = [
    {
      id: 1,
      user: 'Olivia Martin',
      email: 'm@example.com',
      amount: '+$1,999.00',
      status: 'success',
    },
    {
      id: 2,
      user: 'Jackson Lee',
      email: 'jackson.lee@email.com',
      amount: '+$39.00',
      status: 'success',
    },
    {
      id: 3,
      user: 'Isabella Nguyen',
      email: 'isabella.nguyen@email.com',
      amount: '+$299.00',
      status: 'success',
    },
    {
      id: 4,
      user: 'William Kim',
      email: 'will@email.com',
      amount: '+$99.00',
      status: 'success',
    },
    {
      id: 5,
      user: 'Sofia Davis',
      email: 'sofia.davis@email.com',
      amount: '+$39.00',
      status: 'success',
    },
  ];

  return (
    <MainLayout>
      <div className="flex-1 space-y-4 p-4 md:p-8 pt-6">
        <div className="flex items-center justify-between space-y-2">
          <h2 className="text-3xl font-bold tracking-tight">Dashboard</h2>
          <div className="flex items-center space-x-2">
            <Button asChild>
              <Link href="/host/properties/new">Add Property</Link>
            </Button>
          </div>
        </div>

        {/* Stats Grid */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          {stats.map((stat, index) => (
            <Card key={index} className="animate-fade-in-up" style={{ animationDelay: `${index * 50}ms` }}>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium">
                  {stat.title}
                </CardTitle>
                <stat.icon className="h-4 w-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <div className="text-2xl font-bold">{stat.value}</div>
                <p className="text-xs text-muted-foreground flex items-center gap-1 mt-1">
                  {stat.trend === 'up' ? (
                    <>
                      <TrendingUp className="h-3 w-3 text-green-500" />
                      <span className="text-green-500">{stat.change}</span>
                    </>
                  ) : (
                    <>
                      <TrendingDown className="h-3 w-3 text-red-500" />
                      <span className="text-red-500">{stat.change}</span>
                    </>
                  )}
                  <span className="text-muted-foreground">from last month</span>
                </p>
              </CardContent>
            </Card>
          ))}
        </div>

        {/* Main Content Grid */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
          {/* Revenue Chart */}
          <Card className="col-span-4">
            <CardHeader>
              <CardTitle>Overview</CardTitle>
            </CardHeader>
            <CardContent className="pl-2">
              <div className="h-[350px] flex items-center justify-center border border-dashed rounded-lg">
                <div className="text-center space-y-2">
                  <Activity className="h-12 w-12 mx-auto text-muted-foreground" />
                  <p className="text-sm text-muted-foreground">Chart visualization here</p>
                  <p className="text-xs text-muted-foreground">Integrate with recharts or similar</p>
                </div>
              </div>
            </CardContent>
          </Card>

          {/* Recent Sales */}
          <Card className="col-span-4 md:col-span-3">
            <CardHeader>
              <CardTitle>Recent Sales</CardTitle>
              <CardDescription>
                You made 265 sales this month.
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-8">
                {recentActivity.map((activity) => (
                  <div key={activity.id} className="flex items-center">
                    <div className="h-9 w-9 rounded-full bg-muted flex items-center justify-center">
                      <span className="text-sm font-medium">
                        {activity.user.split(' ').map(n => n[0]).join('')}
                      </span>
                    </div>
                    <div className="ml-4 space-y-1 flex-1">
                      <p className="text-sm font-medium leading-none">{activity.user}</p>
                      <p className="text-sm text-muted-foreground">
                        {activity.email}
                      </p>
                    </div>
                    <div className="ml-auto font-medium">
                      {activity.amount}
                    </div>
                  </div>
                ))}
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Properties Table */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between">
            <div>
              <CardTitle>Your Properties</CardTitle>
              <CardDescription>
                Manage your rental properties
              </CardDescription>
            </div>
            <Button variant="outline" size="sm">
              View All
            </Button>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              {[1, 2, 3, 4].map((item) => (
                <div key={item} className="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors">
                  <div className="flex items-center gap-4">
                    <div className="h-12 w-12 rounded-lg bg-muted" />
                    <div>
                      <p className="font-medium">Modern Apartment in Downtown</p>
                      <p className="text-sm text-muted-foreground">New York, NY • 2 beds • 1 bath</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-4">
                    <div className="text-right">
                      <p className="font-medium">$1,200/night</p>
                      <Badge variant="outline">Active</Badge>
                    </div>
                    <DropdownMenu>
                      <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="icon">
                          <MoreHorizontal className="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end">
                        <DropdownMenuItem>View Details</DropdownMenuItem>
                        <DropdownMenuItem>Edit</DropdownMenuItem>
                        <DropdownMenuItem className="text-destructive">Delete</DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}
