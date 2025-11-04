<?php

namespace App\Filament\Resources\Wishlists\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WishlistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('User'),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Wishlist Name'),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull()
                    ->label('Description'),
                Toggle::make('is_public')
                    ->label('Public Wishlist')
                    ->helperText('Public wishlists can be shared via link')
                    ->default(false),
                TextInput::make('share_token')
                    ->disabled()
                    ->label('Share Token')
                    ->helperText('Auto-generated when wishlist is created'),
            ]);
    }
}
