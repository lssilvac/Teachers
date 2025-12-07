<?php

namespace App\Filament\Resources\ClassModels\RelationManagers;

use App\Helpers\MyFlatpickr;
use App\Models\Invite;
use App\Models\InviteDate;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\Teacher;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

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
        $classModel = $this->getOwnerRecord();


        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('Módulo')
                    ->formatStateUsing(fn($state): string => "Módulo $state"),
                TextColumn::make('accepted.teacher.name')
                    ->label('Professor')->default('Sem Professor'),
                TextColumn::make('accepted.subject.name')
                    ->label('Disciplina')
                    ->default('Sem disciplina'),

                TextColumn::make('dates')
                    ->label('Data')

            ])
            ->paginated(false)
            ->emptyStateHeading('Nenhum agendamento encontrado')
            ->headerActions([
                Action::make('invite_teacher')->label('Convidar')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->color('primary')
                    ->button()
                    ->schema([
                        Select::make('subject_id')
                            ->label('Disciplina')
                            ->native(false)
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('teachers', []))
                            ->options(Subject::query()->where('class_type_id', $classModel->class_type_id)
                                ->orderBy('name')
                                ->pluck('name', 'id')),
                        Select::make('teacher_ids')
                            ->label('Professores')
                            ->native(false)
                            ->searchable()
                            ->multiple()
                            ->options(function (Get $get) {
                                $subject = $get('subject_id');

                                if (blank($subject)) {
                                    return [];
                                }

                                return Teacher::join('teachers_x_subjects', 'teachers.id', 'teachers_x_subjects.teacher_id')
                                    ->where('subject_id', $subject)->orderBy('teachers.name')->pluck('teachers.name', 'teachers.id');
                            }),
                        Select::make('school_year_ids')->label('Módulo')
                            ->native(false)
                            ->searchable()
                            ->multiple()
                            ->options(function () use ($classModel) {
                                return array_map(fn($item) => "Módulo {$item}",
                                    SchoolYear::where('class_id', $classModel->id)->whereNull('invite_id')
                                        ->orderBy('sort_order')
                                        ->pluck('sort_order', 'id')->toArray());

                            })->afterStateHydrated(function ($state, Select $component) use ($classModel) {

                                if (blank($state)) {
                                    $ids = SchoolYear::where('class_id', $classModel->id)->whereNotNull('dates')
                                        ->whereNull('invite_id')
                                        ->orderBy('sort_order')
                                        ->pluck('id', 'id')->toArray();
                                    $component->state($ids);
                                }
                            }),
                    ])
                    ->action(function (Action $action, array $data) {

                        try {

                            DB::beginTransaction();

                            foreach ($data['teacher_ids'] as $teacherId) {
                                $invite = Invite::create([
                                    'teacher_id' => $teacherId,
                                    'subject_id' => $data['subject_id'],
                                    'status' => 'pending',
                                ]);

                                foreach ($data['school_year_ids'] as $schoolYearId) {
                                    InviteDate::create([
                                        'invite_id' => $invite->id,
                                        'school_year_id' => $schoolYearId,
                                    ]);
                                }
                            }

                            DB::commit();

                            Notification::make('invited')
                                ->body('Todos os convites foram enviados com sucesso.')
                                ->title('Convite realizado!')
                                ->duration(5)
                                ->persistent()
                                ->success()
                                ->send();

                        } catch ( \Exception $exception) {
                            logger($exception->getMessage());
                            DB::rollBack();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Definir datas')
                    ->modalHeading('Definir datas da matéria')
                    ->schema([
                        MyFlatpickr::make('dates')
                            ->label('Datas dos encontros')
                            ->multiplePicker()
                            ->showMonths()
                            ->format('Y-m-d')
                            ->displayFormat('d/m')
                            ->conjunction(' • '),
                    ]),

                Action::make('list_invites')
                    ->label('Listar convites')
                    ->icon(Heroicon::OutlinedEye)
                    ->modalHeading('Convites deste módulo')
                    ->modalSubmitAction(false) // não precisa de botão "Salvar"
                    ->modalCancelActionLabel('Fechar')
                    ->modalContent(function (SchoolYear $record) {
                        $invites = $record->invites()
                            ->with(['teacher', 'subject'])
                            ->get();

                        return view('filament.invites.list-for-school-year', [
                            'invites' => $invites,
                        ]);
                    }),
            ]);
    }
}
