<?php

namespace App\Filament\Resources\ClassModels;

use App\Filament\Resources\ClassModels\Pages\CreateClassModel;
use App\Filament\Resources\ClassModels\Pages\EditClassModel;
use App\Filament\Resources\ClassModels\Pages\ListClassModels;
use App\Filament\Resources\ClassModels\RelationManagers\SchoolYearsRelationManager;
use App\Filament\Resources\ClassModels\Schemas\ClassModelForm;
use App\Filament\Resources\ClassModels\Tables\ClassModelsTable;
use App\Models\ClassModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ClassModelResource extends Resource
{
    protected static ?string $navigationLabel                 = 'Turmas';
    protected static ?string $label                           = 'Turma';
    protected static string | UnitEnum | null $navigationGroup = 'Global';
    protected static ?string $slug                            = 'turma';
    protected static ?string $pluralLabel                     = 'Turmas Criadas';



    protected static ?string $model = ClassModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $recordTitleAttribute = 'Classes';

    public static function form(Schema $schema): Schema
    {
        return ClassModelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassModelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SchoolYearsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClassModels::route('/'),
            'create' => CreateClassModel::route('/create'),
            'edit' => EditClassModel::route('/{record}/edit'),
        ];
    }
}
