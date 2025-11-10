<?php

namespace App\Filament\Resources\ClassTypes\Pages;

use App\Filament\Resources\ClassTypes\ClassTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClassType extends EditRecord
{
    protected static string $resource = ClassTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
