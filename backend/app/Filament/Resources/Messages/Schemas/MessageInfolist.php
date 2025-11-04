<?php

namespace App\Filament\Resources\Messages\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MessageInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('conversation.id')
                    ->numeric(),
                TextEntry::make('sender.name')
                    ->numeric(),
                TextEntry::make('read_at')
                    ->dateTime(),
                IconEntry::make('is_system_message')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
