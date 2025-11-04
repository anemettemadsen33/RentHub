<?php

namespace App\Filament\Resources\Messages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('conversation_id')
                    ->relationship('conversation', 'id')
                    ->required(),
                Select::make('sender_id')
                    ->relationship('sender', 'name')
                    ->required(),
                Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('attachments')
                    ->columnSpanFull(),
                DateTimePicker::make('read_at'),
                Toggle::make('is_system_message')
                    ->required(),
            ]);
    }
}
