"use client";
import React, { useState } from 'react';

interface BookingFormProps {
  property: { id: number; price_per_night: number; guests?: number; title?: string };
  onSubmit: (data: {
    property_id: number;
    check_in: string;
    check_out: string;
    guests: number;
  }) => void;
}

export default function BookingForm({ property, onSubmit }: BookingFormProps) {
  const [checkIn, setCheckIn] = useState('');
  const [checkOut, setCheckOut] = useState('');
  const [guests, setGuests] = useState(1);
  const maxGuests = property.guests || 4;

  const nights = (() => {
    if (!checkIn || !checkOut) return 0;
    const start = new Date(checkIn);
    const end = new Date(checkOut);
    const diff = (end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24);
    return Math.max(0, Math.floor(diff));
  })();

  const total = nights * (property.price_per_night || 0);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (guests > maxGuests) return;
    onSubmit({
      property_id: property.id,
      check_in: checkIn,
      check_out: checkOut,
      guests,
    });
  };

  return (
    <form onSubmit={handleSubmit} aria-label="Booking form">
      <div>
        <label htmlFor="check-in">Check-in</label>
        <input
          id="check-in"
          name="check-in"
          type="date"
          value={checkIn}
          onChange={(e) => setCheckIn(e.target.value)}
        />
      </div>
      <div>
        <label htmlFor="check-out">Check-out</label>
        <input
          id="check-out"
          name="check-out"
          type="date"
          value={checkOut}
          onChange={(e) => setCheckOut(e.target.value)}
        />
      </div>
      <div>
        <label htmlFor="guests">Guests</label>
        <input
          id="guests"
          name="guests"
          type="number"
          min={1}
          max={maxGuests}
          value={guests}
          onChange={(e) => setGuests(parseInt(e.target.value || '1', 10))}
        />
        {guests > maxGuests && (
          <p role="alert">Maximum {maxGuests} guests allowed</p>
        )}
      </div>
      <div>
        <span>Total: ${total}</span>
      </div>
      <button type="submit">Book Now</button>
    </form>
  );
}
