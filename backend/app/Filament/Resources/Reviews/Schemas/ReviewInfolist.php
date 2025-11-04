<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ReviewInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('property.title')
                    ->numeric(),
                TextEntry::make('user.name')
                    ->numeric(),
                TextEntry::make('booking.id')
                    ->numeric(),
                TextEntry::make('rating')
                    ->numeric(),
                TextEntry::make('cleanliness_rating')
                    ->numeric(),
                TextEntry::make('communication_rating')
                    ->numeric(),
                TextEntry::make('check_in_rating')
                    ->numeric(),
                TextEntry::make('accuracy_rating')
                    ->numeric(),
                TextEntry::make('location_rating')
                    ->numeric(),
                TextEntry::make('value_rating')
                    ->numeric(),
                IconEntry::make('is_approved')
                    ->boolean(),
                TextEntry::make('owner_response_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
