"use client";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { DollarSign, Home, Calendar, Star, TrendingUp } from "lucide-react";

export interface HostStats {
  totalEarnings: number;
  earningsGrowth: number;
  activeProperties: number;
  totalProperties: number;
  upcomingBookings: number;
  totalBookings: number;
  averageRating: number;
  totalReviews: number;
  recentBookings?: Array<{
    id: number;
    propertyTitle: string;
    guestName: string;
    checkIn: string;
    checkOut: string;
    total: number;
  }>;
}

interface Props {
  stats: HostStats | null;
}

export function HostStatsCards({ stats }: Props) {
  return (
    <div className="grid md:grid-cols-4 gap-6 mb-8">
      <Card>
        <CardHeader className="flex flex-row items-center justify-between pb-2">
          <CardTitle className="text-sm font-medium">Total Earnings</CardTitle>
          <DollarSign className="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div className="text-2xl font-bold">${stats?.totalEarnings?.toLocaleString() || "0"}</div>
          <p className="text-xs text-muted-foreground flex items-center gap-1">
            <TrendingUp className="h-3 w-3 text-green-500" />+{stats?.earningsGrowth || 0}% this month
          </p>
        </CardContent>
      </Card>
      <Card>
        <CardHeader className="flex flex-row items-center justify-between pb-2">
          <CardTitle className="text-sm font-medium">Active Properties</CardTitle>
          <Home className="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div className="text-2xl font-bold">{stats?.activeProperties || 0}</div>
          <p className="text-xs text-muted-foreground">{stats?.totalProperties || 0} total listings</p>
        </CardContent>
      </Card>
      <Card>
        <CardHeader className="flex flex-row items-center justify-between pb-2">
          <CardTitle className="text-sm font-medium">Upcoming Bookings</CardTitle>
          <Calendar className="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div className="text-2xl font-bold">{stats?.upcomingBookings || 0}</div>
          <p className="text-xs text-muted-foreground">{stats?.totalBookings || 0} total bookings</p>
        </CardContent>
      </Card>
      <Card>
        <CardHeader className="flex flex-row items-center justify-between pb-2">
          <CardTitle className="text-sm font-medium">Avg Rating</CardTitle>
          <Star className="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div className="text-2xl font-bold flex items-center gap-1">
            {stats?.averageRating || "0.0"}
            <Star className="h-5 w-5 fill-yellow-400 text-yellow-400" />
          </div>
          <p className="text-xs text-muted-foreground">{stats?.totalReviews || 0} reviews</p>
        </CardContent>
      </Card>
    </div>
  );
}
