<?php

namespace App\Filament\Resources\CleaningSchedules\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CleaningScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('property.title')
                    ->numeric(),
                TextEntry::make('serviceProvider.name')
                    ->numeric(),
                TextEntry::make('created_by')
                    ->numeric(),
                TextEntry::make('schedule_type'),
                TextEntry::make('frequency'),
                TextEntry::make('day_of_month')
                    ->numeric(),
                TextEntry::make('preferred_time')
                    ->time(),
                TextEntry::make('duration_hours')
                    ->numeric(),
                TextEntry::make('service_type'),
                TextEntry::make('start_date')
                    ->date(),
                TextEntry::make('end_date')
                    ->date(),
                IconEntry::make('active')
                    ->boolean(),
                TextEntry::make('last_executed_at')
                    ->dateTime(),
                TextEntry::make('next_execution_at')
                    ->dateTime(),
                IconEntry::make('auto_book')
                    ->boolean(),
                TextEntry::make('book_days_in_advance')
                    ->numeric(),
                IconEntry::make('notify_provider')
                    ->boolean(),
                IconEntry::make('notify_owner')
                    ->boolean(),
                TextEntry::make('reminder_hours_before')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
                TextEntry::make('deleted_at')
                    ->dateTime(),
            ]);
    }
}
