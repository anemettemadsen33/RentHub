<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewBookingNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Booking $booking;
    public string $message;

    public function __construct(Booking $booking, string $message = 'New booking received')
    {
        $this->booking = $booking;
        $this->message = $message;
    }

    public function broadcastOn(): Channel
    {
        // Broadcast to property owner
        return new PrivateChannel('user.' . $this->booking->property->user_id);
    }

    public function broadcastAs(): string
    {
        return 'booking.new';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->booking->id,
            'property_id' => $this->booking->property_id,
            'property_title' => $this->booking->property->title,
            'guest_name' => $this->booking->user->name,
            'check_in' => $this->booking->check_in,
            'check_out' => $this->booking->check_out,
            'total_price' => $this->booking->total_price,
            'status' => $this->booking->status,
            'message' => $this->message,
            'created_at' => $this->booking->created_at,
        ];
    }
}
