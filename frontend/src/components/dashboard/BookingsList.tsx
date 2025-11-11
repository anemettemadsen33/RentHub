import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import Link from 'next/link';
import { Calendar } from 'lucide-react';

export interface BookingItem {
  id: number;
  property: string;
  checkIn: string;
  checkOut: string;
  nights: number;
  status: 'confirmed' | 'pending' | 'cancelled';
}

interface BookingsListProps {
  bookings: BookingItem[];
  loading?: boolean;
}

export function BookingsList({ bookings, loading }: BookingsListProps) {
  return (
    <Card className="xl:col-span-2">
      <CardHeader className="flex flex-row items-center justify-between">
        <div>
          <CardTitle>Upcoming Bookings</CardTitle>
          <CardDescription>Next confirmed & pending stays</CardDescription>
        </div>
        <Button variant="outline" size="sm" asChild>
          <Link href="/dashboard/bookings">View all</Link>
        </Button>
      </CardHeader>
      <CardContent>
        {loading ? (
          <div className="space-y-3">
            {Array.from({ length: 3 }).map((_, i) => (
              <div key={i} className="h-14 w-full rounded-md bg-muted animate-pulse" />
            ))}
          </div>
        ) : bookings.length === 0 ? (
          <p className="text-sm text-muted-foreground">No upcoming bookings</p>
        ) : (
          <div className="space-y-4 max-h-72 overflow-y-auto pr-2 scrollbar-thin">
            {bookings.map(b => (
              <div
                key={b.id}
                className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border rounded-lg p-4 hover:bg-muted/50 transition"
              >
                <div className="space-y-1">
                  <h3 className="font-medium text-sm">{b.property}</h3>
                  <div className="text-xs text-muted-foreground flex flex-wrap gap-3">
                    <span className="flex items-center gap-1"><Calendar className="h-3 w-3" /> {new Date(b.checkIn).toLocaleDateString('ro-RO')} → {new Date(b.checkOut).toLocaleDateString('ro-RO')}</span>
                    <span>{b.nights} nights</span>
                    <span
                      className={
                        b.status === 'confirmed'
                          ? 'text-green-600 font-medium'
                          : b.status === 'pending'
                          ? 'text-amber-600 font-medium'
                          : 'text-red-600 font-medium'
                      }
                    >
                      {b.status}
                    </span>
                  </div>
                </div>
                <Button variant="outline" size="sm" asChild>
                  <Link href={`/bookings/${b.id}`}>Details</Link>
                </Button>
              </div>
            ))}
          </div>
        )}
      </CardContent>
    </Card>
  );
}
