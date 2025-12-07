<?php

namespace App\Filament\Resources\ClassModels\Pages;

use App\Filament\Resources\ClassModels\ClassModelResource;
use App\Models\SchoolYear;
use App\Models\Subject;
use Filament\Resources\Pages\CreateRecord;

class CreateClassModel extends CreateRecord
{
    protected static string $resource = ClassModelResource::class;

    public function afterCreate(): void
    {
        $record = $this->getRecord();
        $total = Subject::query()
            ->where('class_type_id', $record->class_type_id)
            ->count();

        for ($i = 1; $i <= $total; $i++) {
            SchoolYear::query()->create([
                'class_id' => $record->getKey(),
                'sort_order' => $i
            ]);
        }
    }
}
