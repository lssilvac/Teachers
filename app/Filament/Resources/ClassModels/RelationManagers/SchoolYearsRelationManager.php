<?php

namespace App\Filament\Resources\ClassModels\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SchoolYearsRelationManager extends RelationManager
{
    protected static string $relationship = 'schoolYears';

    protected static ?string $title = 'Agendamentos';

    /**
     * Cria SchoolYears faltantes para a turma atual,
     * considerando APENAS os Subjects do mesmo class_type_id.
     */

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject.name')
                    ->label('Matéria')
                    ->sortable
                    (query: function (Builder $query, string $direction) {
                        // Ordena pela coluna de Subjects com join seguro
                        $query->join('subjects', 'subjects.id', '=', 'school_years.subject_id')
                            ->orderBy('subjects.name', $direction)
                            ->select('school_years.*');
                    }),
            ])
            ->paginated(false)
            ->emptyStateHeading('Nenhum agendamento encontrado');


    }
}
