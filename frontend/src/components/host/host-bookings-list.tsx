"use client";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

export interface BookingItem {
  id: number;
  propertyTitle: string;
  guestName: string;
  checkIn: string;
  checkOut: string;
  total: number;
}

interface Props {
  bookings: BookingItem[] | undefined;
}

export function HostBookingsList({ bookings }: Props) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>Recent Bookings</CardTitle>
        <CardDescription>Manage your property bookings</CardDescription>
      </CardHeader>
      <CardContent>
        <div className="space-y-4">
          {bookings && bookings.length > 0 ? (
            bookings.map((booking) => (
              <div key={booking.id} className="flex items-center justify-between p-4 border rounded-lg">
                <div className="flex-1">
                  <p className="font-medium">{booking.propertyTitle}</p>
                  <p className="text-sm text-muted-foreground">
                    {booking.guestName} • {booking.checkIn} - {booking.checkOut}
                  </p>
                </div>
                <div className="flex items-center gap-4">
                  <span className="font-semibold">${booking.total}</span>
                  <Button variant="outline" size="sm">View Details</Button>
                </div>
              </div>
            ))
          ) : (
            <p className="text-center text-muted-foreground py-8">No bookings yet</p>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
