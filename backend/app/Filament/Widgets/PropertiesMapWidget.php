<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use Filament\Widgets\Widget;

class PropertiesMapWidget extends Widget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 1;
    
    public function getView(): string
    {
        return 'filament.widgets.properties-map-widget';
    }

    public function getProperties(): array
    {
        $properties = Property::query()
            ->where('is_active', true)
            ->where('status', 'published')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['user'])
            ->get()
            ->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'latitude' => (float) $property->latitude,
                    'longitude' => (float) $property->longitude,
                    'price' => $property->price_per_night,
                    'type' => $property->type,
                    'bedrooms' => $property->bedrooms,
                    'image' => $property->main_image,
                    'owner' => $property->user->name,
                    'city' => $property->city,
                    'status' => $property->status,
                ];
            })
            ->toArray();

        return $properties;
    }
}
