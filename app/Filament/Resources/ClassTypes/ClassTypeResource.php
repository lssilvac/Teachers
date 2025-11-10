<?php

namespace App\Filament\Resources\ClassTypes;

use App\Filament\Resources\ClassTypes\Pages\CreateClassType;
use App\Filament\Resources\ClassTypes\Pages\EditClassType;
use App\Filament\Resources\ClassTypes\Pages\ListClassTypes;
use App\Filament\Resources\ClassTypes\Schemas\ClassTypeForm;
use App\Filament\Resources\ClassTypes\Tables\ClassTypesTable;
use App\Models\ClassType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ClassTypeResource extends Resource
{
    protected static ?string $navigationLabel                 = 'Cursos e Matérias';
    protected static ?string $label                           = 'Curso';
    protected static string | UnitEnum | null $navigationGroup = 'Global';
    protected static ?string $slug                            = 'cursos e matérias';
    protected static ?string $pluralLabel                     = 'Cursos Criados';


    protected static ?string $model = ClassType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ClassTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClassTypesTable::configure($table);
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
            'index' => ListClassTypes::route('/'),
            'create' => CreateClassType::route('/create'),
            'edit' => EditClassType::route('/{record}/edit'),
        ];
    }
}
