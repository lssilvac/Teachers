<?php

namespace App\Livewire;

use App\Enums\InviteCancelReason;
use App\Models\Invite;
use App\Models\SchoolYear;
use Blade;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Guava\Calendar\Filament\Actions\EditAction;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\EventClickInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;


class TeacherCalendar extends CalendarWidget
{
    protected bool $dateClickEnabled = true;
    protected bool $eventClickEnabled = true;
    public ?Invite $selectedInvite = null;

    protected function getEvents(FetchInfo $info): Collection|array|Builder
    {

        $user = auth()->user();

        if (blank($user)) {
            return [];
        }

        $teacher = $user->teacher;

        if (blank($teacher)) {
            return [];
        }

        $invites = Invite::query()
            ->where('teacher_id', $teacher->id)
            ->with([
                'subject',
                'dates.schoolYear.class.school',
            ])
            ->get();

        $events = [];

        foreach ($invites as $invite) {

            // 🔹 1) Pula convites cancelados
            if (
                $invite->status === 'canceled'
                || $invite->status === 'rejected'
                || !is_null($invite->canceled_by)
            ) {
                continue; // não gera eventos no calendário para esse invite
            }

            $subject = $invite->subject;
            $title = null;

            foreach ($invite->dates as $inviteDate) {
                if (is_null($title)) {
                    $school = $inviteDate->schoolYear->class->school;
                    $title = "{$school->name} - {$subject->name}";
                }

                $schoolYear = $inviteDate->schoolYear;
                $itsMe = data_get($schoolYear, 'accepted.teacher.id') === $teacher->id;

                if (!is_null($schoolYear->invite_id) && !$itsMe) {
                    continue;
                }

                foreach ($schoolYear->dates as $date) {
                    $color = match (true) {
                        $itsMe => '#00FF00',
                        default => '#FF0000',
                    };

                    $events[] = CalendarEvent::make()
                        ->resourceId($invite->id)
                        ->title($title)
                        ->model(Invite::class)
                        ->key($invite->id)
                        ->backgroundColor($color)
                        ->allDay(1)
                        ->start(Carbon::parse($date)->addHours(3))
                        ->end(Carbon::parse($date)->addHours(3));
                }
            }
        }


        return $events;
    }

    protected function onEventClick(EventClickInfo $info, Model $event, ?string $action = null): void
    {
        $this->selectedInvite = Invite::find($event->id);
        $this->mountAction('edit');
    }


    public function editAction(): EditAction
    {
        // 1) Monta as opções dos módulos (SchoolYears) desse convite
        $options = [];

        foreach ($this->selectedInvite->dates as $inviteDate) {
            $schoolYear = $inviteDate->schoolYear;

            if (!is_null($schoolYear->invite_id)) {
                continue; // já tem invite associado, pula se não quiser mostrar
            }

            $sortOrder = $schoolYear->sort_order;

            $moduleDates = collect($schoolYear->dates)
                ->map(fn($date) => Carbon::parse($date)->format('d/m/Y'))
                ->implode(', ');

            $options[$inviteDate->school_year_id] = "Módulo {$sortOrder} - {$moduleDates}";
        }

        return EditAction::make()
            ->modalHeading('Editar Convite')
            ->modalWidth('2xl')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->extraModalFooterActions([
                Action::make('rejectInvite')
                    ->label('Recusar convite')
                    ->color('danger')
                    ->outlined()
                    ->size('sm')

                    // 🔹 Campos do modal de recusa
                    ->schema([
                        Select::make('cancel_reason')
                            ->label('Motivo da recusa')
                            ->options(InviteCancelReason::options())
                            ->required(),

                        Textarea::make('cancel_reason_notes')
                            ->label('Detalhes adicionais (opcional)')
                            ->rows(4),
                    ])
                    ->modalHeading('Recusar convite')
                    ->modalDescription('Confirme o motivo da recusa. Essas informações ajudam a ajustar futuros agendamentos.')
                    ->modalSubmitActionLabel('Confirmar recusa')
                    ->modalWidth('lg')
                    ->action(function (array $data): void {
                        if (!$this->selectedInvite) {
                            return;
                        }

                        $this->selectedInvite->update([
                            'status' => 'canceled',
                            'canceled_by' => auth()->id(),
                            'cancel_reason' => $data['cancel_reason'],
                            'cancel_reason_notes' => $data['cancel_reason_notes'] ?? null,
                        ]);

                        // 🔹 avisa o calendário para se atualizar
                        $this->refreshRecords();
                        $this->refreshResources();


                        Notification::make()
                            ->title('Convite recusado com sucesso.')
                            ->success()
                            ->send();
                    })

                    // 🔹 fecha também o modal “pai” (Editar Convite)
                    ->cancelParentActions(),
            ])
            ->schema([
                Wizard::make([
                    // STEP 1: Contexto do convite
                    Step::make('contexto')
                        ->label('Contexto')
                        ->schema([
                            Section::make('Sobre o convite')
                                ->schema([
                                    TextEntry::make('subject_name')
                                        ->label('Disciplina')
                                        ->state(fn() => $this->selectedInvite?->subject?->name ?? '-')
                                        ->columnSpanFull(),

                                    TextEntry::make('school_name')
                                        ->label('Unidade')
                                        ->state(function () {
                                            $firstDate = $this->selectedInvite?->dates?->first();
                                            $school = $firstDate?->schoolYear?->class?->school;

                                            return $school?->name ?? '-';
                                        })
                                        ->columnSpanFull(),

                                    TextEntry::make('location')
                                        ->label('Localização')
                                        ->state(function () {
                                            $firstDate = $this->selectedInvite?->dates?->first();
                                            $school = $firstDate?->schoolYear?->class?->school;

                                            if (!$school) {
                                                return '-';
                                            }

                                            // Ajuste esses campos para os nomes reais da sua tabela School
                                            $country = $school->country ?? 'País';
                                            $state = $school->administrative_area_level_1 ?? 'Estado';
                                            $city = $school->administrative_area_level_2 ?? 'Cidade';

                                            return "{$city} / {$state} – {$country}";
                                        })
                                        ->columnSpanFull(),

                                    TextEntry::make('schedule_summary')
                                        ->label('Cronograma')
                                        ->state(function () {
                                            $firstDate = $this->selectedInvite?->dates?->first();
                                            $schoolYear = $firstDate?->schoolYear;
                                            $class = $schoolYear?->class;

                                            if (!$schoolYear || !$class) {
                                                return 'Cronograma não definido.';
                                            }

                                            // weekday vem da tabela classes (JSON com 0–6)
                                            $weekdayIndexes = (array)$class->weekday;

                                            // Mapeia número → nome do dia
                                            $weekdayNames = [
                                                0 => 'Domingo',
                                                1 => 'Segunda-feira',
                                                2 => 'Terça-feira',
                                                3 => 'Quarta-feira',
                                                4 => 'Quinta-feira',
                                                5 => 'Sexta-feira',
                                                6 => 'Sábado',
                                            ];

                                            $selectedDays = collect($weekdayIndexes)
                                                ->map(fn($index) => $weekdayNames[$index] ?? "Dia {$index}")
                                                ->values();

                                            $daysPerWeek = $selectedDays->count();

                                            // Quantidade de encontros = quantidade de dates do módulo
                                            $weeksOrMeetings = is_array($schoolYear->dates) ? count($schoolYear->dates) : null;

                                            // Se houver horário salvo na turma (ajuste o nome da coluna se for diferente)
                                            $time = $class->time ?? null;

                                            $parts = [];

                                            if ($daysPerWeek > 0) {
                                                $parts[] = "{$daysPerWeek}x por semana ({$selectedDays->implode(', ')})";
                                            }

                                            if ($weeksOrMeetings) {
                                                $parts[] = "{$weeksOrMeetings} encontros";
                                            }

                                            if ($time) {
                                                $parts[] = "às {$time}";
                                            }

                                            return blank($parts)
                                                ? 'Cronograma não definido.'
                                                : implode(', ', $parts);
                                        })
                                        ->columnSpanFull(),

                                ]),
                        ]),

                    // STEP 2: Escolher módulo
                    Step::make('escolha_modulo')
                        ->label('Escolher módulo')
                        ->schema([
                            Section::make('Módulo')
                                ->schema([
                                    Select::make('choosed_id')
                                        ->label('Módulo escolhido')
                                        ->native(false)
                                        ->options($options)
                                        ->required()
                                        ->searchable()
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $schoolYear = SchoolYear::find($state);

                                            if (!$schoolYear || blank($schoolYear->dates)) {
                                                $set('start_date', null);
                                                $set('end_date', null);
                                                return;
                                            }

                                            $dates = collect($schoolYear->dates)->sort();

                                            $set('start_date', $dates->first());
                                            $set('end_date', $dates->last());
                                        }),

                                    Placeholder::make('module_dates_preview')
                                        ->label('Datas deste módulo')
                                        ->content(function (callable $get) {
                                            $schoolYearId = $get('choosed_id');

                                            if (!$schoolYearId) {
                                                return 'Selecione um módulo para ver as datas.';
                                            }

                                            $schoolYear = SchoolYear::find($schoolYearId);

                                            if (!$schoolYear || blank($schoolYear->dates)) {
                                                return 'Este módulo ainda não tem datas definidas.';
                                            }

                                            return collect($schoolYear->dates)
                                                ->map(fn($date) => Carbon::parse($date)->format('d/m/Y'))
                                                ->implode(' • ');
                                        })
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    // STEP 3: Revisão e confirmação
                    Step::make('revisao')
                        ->label('Revisão')
                        ->schema([
                            Section::make('Resumo do convite')
                                ->schema([
                                    TextEntry::make('review_school')
                                        ->label('Unidade')
                                        ->state(fn() => $this->selectedInvite?->dates?->first()?->schoolYear?->class?->school?->name ?? '-'),

                                    TextEntry::make('review_subject')
                                        ->label('Disciplina')
                                        ->state(fn() => $this->selectedInvite?->subject?->name ?? '-'),

                                    TextEntry::make('review_module')
                                        ->label('Módulo escolhido')
                                        ->state(function (callable $get) use ($options) {
                                            $id = $get('choosed_id');

                                            return $options[$id] ?? '-';
                                        })
                                        ->columnSpanFull(),

                                    Grid::make(2)
                                        ->schema([
                                            DatePicker::make('start_date')
                                                ->label('Data início')
                                                ->displayFormat('d/m/Y')
                                                ->disabled(),

                                            DatePicker::make('end_date')
                                                ->label('Data fim')
                                                ->displayFormat('d/m/Y')
                                                ->disabled(),
                                        ]),

                                    Textarea::make('notes')
                                        ->label('Observações')
                                        ->rows(3),
                                ]),
                        ]),


                ])
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <x-filament::button
                            type="submit"
                            size="sm"
                        >
                            Aceitar Convite
                        </x-filament::button>
                    BLADE
                    )))
            ])
            ->fillForm(function (): array {
                if (!$this->selectedInvite) {
                    return [];
                }

                return [
                    'notes' => $this->selectedInvite->notes,
                    'start_date' => $this->selectedInvite->start_date,
                    'end_date' => $this->selectedInvite->end_date,
                    // se você já tiver um school_year vinculado, preenche choosed_id aqui
                    // 'choosed_id' => $this->selectedInvite->school_year_id ?? null,
                ];
            })
            ->action(function (array $data): void {
                if (!$this->selectedInvite) {
                    return;
                }

                // Atualiza o invite
                $this->selectedInvite->update([
                    'notes' => $data['notes'] ?? null,
                    'start_date' => $data['start_date'] ?? null,
                    'end_date' => $data['end_date'] ?? null,
                    // aqui você pode gravar o school_year escolhido, se tiver esse campo
                    // 'school_year_id' => $data['choosed_id'] ?? null,
                ]);

                Notification::make()
                    ->title('Convite Aceito!')
                    ->success()
                    ->send();

                // se quiser, atualiza o calendário
                // $this->dispatch('refreshCalendar');
            });
    }


}
