<?php

namespace App\Filament\Resources\Conversations\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ConversationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('property.title')
                    ->numeric(),
                TextEntry::make('booking.id')
                    ->numeric(),
                TextEntry::make('tenant.name')
                    ->numeric(),
                TextEntry::make('owner.name')
                    ->numeric(),
                TextEntry::make('subject'),
                TextEntry::make('last_message_at')
                    ->dateTime(),
                IconEntry::make('is_archived')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
