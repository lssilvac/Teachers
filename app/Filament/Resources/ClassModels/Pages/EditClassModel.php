<?php

namespace App\Filament\Resources\ClassModels\Pages;

use App\Filament\Resources\ClassModels\ClassModelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditClassModel extends EditRecord
{
    protected static string $resource = ClassModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool {
        return true;
    }

    public function getContentTabLabel(): ?string {
        return 'Turma';
    }
}
