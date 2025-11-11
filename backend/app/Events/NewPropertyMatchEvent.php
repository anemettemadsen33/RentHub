<?php

namespace App\Events;

use App\Models\Property;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewPropertyMatchEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public array $properties;
    public string $searchName;

    public function __construct(int $userId, array $properties, string $searchName)
    {
        $this->userId = $userId;
        $this->properties = $properties;
        $this->searchName = $searchName;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'property.match';
    }

    public function broadcastWith(): array
    {
        return [
            'search_name' => $this->searchName,
            'property_count' => count($this->properties),
            'properties' => array_map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'city' => $property->city,
                    'price_per_night' => $property->price_per_night,
                    'image_url' => $property->image_url ?? $property->images[0] ?? null,
                ];
            }, array_slice($this->properties, 0, 3)),
        ];
    }
}
