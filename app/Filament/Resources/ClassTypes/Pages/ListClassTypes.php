<?php

namespace App\Filament\Resources\ClassTypes\Pages;

use App\Filament\Resources\ClassTypes\ClassTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClassTypes extends ListRecords
{
    protected static string $resource = ClassTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
