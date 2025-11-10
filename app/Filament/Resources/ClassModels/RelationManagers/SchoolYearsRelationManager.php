<?php

namespace App\Filament\Resources\ClassModels\RelationManagers;

use App\Models\SchoolYear;
use App\Models\Subject;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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
    protected function syncMissingSubjects(): void
    {
        $classModel = $this->getOwnerRecord(); // turma atual

        // Se a turma ainda não tem tipo definido, não sincroniza.
        if (! $classModel?->class_type_id) {
            Notification::make()
                ->title('Defina o Tipo de Turma antes de sincronizar.')
                ->warning()
                ->send();
            return;
        }

        // IDs já agendados nesta turma
        $already = $classModel->schoolYears()
            ->pluck('subject_id')
            ->all();

        // Cria SchoolYear apenas para Subjects do mesmo class_type_id que ainda faltam
        Subject::query()
            ->where('class_type_id', $classModel->class_type_id)
            ->whereNotIn('id', $already)
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

    public function table(Table $table): Table
    {
        return $table
            // Na primeira carga, se não houver registros, sincroniza com base no ClassType.
            ->modifyQueryUsing(function (Builder $query) {
                if (! $query->clone()->exists()) {
                    $this->syncMissingSubjects();
                }
            })
            ->columns([
                TextColumn::make('subject.name')
                    ->label('Matéria')
                    ->sortable(query: function (Builder $query, string $direction) {
                        // Ordena pela coluna de Subjects com join seguro
                        $query->join('subjects', 'subjects.id', '=', 'school_years.subject_id')
                            ->orderBy('subjects.name', $direction)
                            ->select('school_years.*');
                    }),
            ])
            ->paginated(false)
            ->emptyStateHeading('Nenhum agendamento encontrado')
            ->emptyStateDescription('Clique em "Sincronizar matérias" após definir o Tipo de Turma.')
            ->headerActions([
                Action::make('syncSubjects')
                    ->label('Sincronizar matérias')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function () {
                        $this->syncMissingSubjects();
                        $this->dispatch('refresh');
                    }),
            ]);
    }
}
