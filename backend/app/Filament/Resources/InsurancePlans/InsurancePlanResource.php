<?php

namespace App\Filament\Resources\InsurancePlans;

use App\Filament\Resources\InsurancePlans\Pages\CreateInsurancePlan;
use App\Filament\Resources\InsurancePlans\Pages\EditInsurancePlan;
use App\Filament\Resources\InsurancePlans\Pages\ListInsurancePlans;
use App\Filament\Resources\InsurancePlans\Pages\ViewInsurancePlan;
use App\Filament\Resources\InsurancePlans\Schemas\InsurancePlanForm;
use App\Filament\Resources\InsurancePlans\Schemas\InsurancePlanInfolist;
use App\Filament\Resources\InsurancePlans\Tables\InsurancePlansTable;
use App\Models\InsurancePlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class InsurancePlanResource extends Resource
{
    protected static ?string $model = InsurancePlan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Insurance Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return InsurancePlanForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InsurancePlanInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InsurancePlansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInsurancePlans::route('/'),
            'create' => CreateInsurancePlan::route('/create'),
            'view' => ViewInsurancePlan::route('/{record}'),
            'edit' => EditInsurancePlan::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
