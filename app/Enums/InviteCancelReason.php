<?php

namespace App\Enums;

enum InviteCancelReason: string
{
    case SCHEDULE_CONFLICT  = 'schedule_conflict';
    case TRAVEL_IMPOSSIBLE  = 'travel_impossible';
    case HEALTH_ISSUES      = 'health_issues';
    case OTHER              = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULE_CONFLICT  => 'Incompatibilidade de agenda',
            self::TRAVEL_IMPOSSIBLE  => 'Impossibilidade de deslocamento',
            self::HEALTH_ISSUES      => 'Questões de saúde',
            self::OTHER              => 'Outro motivo',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
