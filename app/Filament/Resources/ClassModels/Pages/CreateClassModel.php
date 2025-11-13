<?php

namespace App\Filament\Resources\ClassModels\Pages;

use App\Filament\Resources\ClassModels\ClassModelResource;
use App\Models\SchoolYear;
use App\Models\Subject;
use Filament\Resources\Pages\CreateRecord;

class CreateClassModel extends CreateRecord
{
    protected static string $resource = ClassModelResource::class;

    public function afterCreate(): void {
        $classModel = $this->getRecord(); // turma atual

        // Cria SchoolYear apenas para Subjects do mesmo class_type_id que ainda faltam
        Subject::query()
            ->where('class_type_id', $classModel->class_type_id)
            ->orderBy('name')
            ->get()
            ->each(function (Subject $subject) use ($classModel) {
                SchoolYear::query()->create([
                    'class_id' => $classModel->getKey(),
                    'subject_id'     => $subject->getKey(),
                    // Preencha aqui defaults obrigatórios do SchoolYear, se houver.
                ]);
            });


    }
}
