<?php

namespace App\Livewire;

use App\Models\Invite;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Guava\Calendar\Filament\Actions\EditAction;
use Guava\Calendar\Filament\CalendarWidget;
use Guava\Calendar\ValueObjects\CalendarEvent;
use Guava\Calendar\ValueObjects\EventClickInfo;
use Guava\Calendar\ValueObjects\FetchInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;


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

        $invites = Invite::query()->where('teacher_id', $teacher->id)
            ->get();

        $events = [];

        foreach ($invites as $invite) {

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
//                        ->action('view');
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
        $options = [];

        foreach ($this->selectedInvite->dates as $date) {

            $schoolYear = $date->schoolYear;

            if (!is_null($schoolYear->invite_id)) {
                continue;
            }

            $sortOrder = $schoolYear->sort_order;
            $moduleDates = implode(', ', $schoolYear->dates);

            $options[$date->school_year_id] = "Módulo {$sortOrder} - $moduleDates";
        }


        return EditAction::make()
            ->modalHeading('Editar Convite')
            ->modalWidth('2xl')
            ->schema([
                TextEntry::make('subject_id')
                    ->label('Disciplina')
                    ->formatStateUsing(fn() => $this->selectedInvite->subject->name),


                Select::make('choosed_id')
                    ->label('Módulo Escolhido')
                    ->native(false)
                    ->options($options)
                    ->required()
                    ->searchable(),

                Textarea::make('notes')
                    ->label('Observações')
                    ->rows(3),

                DatePicker::make('start_date')
                    ->label('Data Início')
                    ->required(),

                DatePicker::make('end_date')
                    ->label('Data Fim')
                    ->required(),
            ])
            ->fillForm(function (): array {
                if (!$this->selectedInvite) {
                    return [];
                }


                return [
                    'subject_id' => $this->selectedInvite->subject_id,
                    'notes' => $this->selectedInvite->notes,
                    'start_date' => $this->selectedInvite->start_date,
                    'end_date' => $this->selectedInvite->end_date,
                ];
            })
            ->action(function (array $data): void {
                if ($this->currentInvite) {
                    $this->currentInvite->update($data);

                    \Filament\Notifications\Notification::make()
                        ->title('Convite atualizado!')
                        ->success()
                        ->send();
                }
            });
    }

}
