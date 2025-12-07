<?php

namespace Database\Factories;

use App\Models\ClassModel;
use App\Models\Invite;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClassModelFactory extends Factory
{
    protected $model = ClassModel::class;

    public function definition(): array
    {
        // 6 primeiros usuários
        $userIds = User::orderBy('id')
            ->limit(6)
            ->pluck('id')
            ->toArray();

        // 6 primeiras escolas
        $schoolIds = School::orderBy('id')
            ->limit(6)
            ->pluck('id')
            ->toArray();

        $weekday = $this->generateWeekdayPattern();

        return [
            'user_id'       => fake()->randomElement($userIds),
            'school_id'     => fake()->randomElement($schoolIds),
            'class_type_id' => fake()->numberBetween(1, 2),

            'weekday'    => $weekday,
            'start_date' => '2026-01-01',
            'end_date'   => '2026-12-31',

            'name' => null, // preenchido no configure()
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (ClassModel $class) {

            // 1) Nome da turma
            $schoolName   = optional($class->school)->name ?? 'Carisma';
            $weekdayLabel = $this->formatWeekdayLabels($class->weekday ?? []);

            $class->update([
                'name' => 'Turma ' . $schoolName . ' - ' . $weekdayLabel,
            ]);

            // 2) Criar SchoolYears (módulos)
            $numModules = $class->class_type_id === 1 ? 19 : 12;

            $this->createSchoolYearsForClass($class, $numModules);
        });
    }

    /**
     * Gera o padrão de dias da semana (sem domingo).
     */
    protected function generateWeekdayPattern(): array
    {
        $option = fake()->numberBetween(1, 4);

        return match ($option) {
            // 1 dia → sábado
            1 => [6],

            // 2 dias → combinações específicas
            2 => fake()->randomElement([
                [1, 2], // Seg - Ter
                [4, 5], // Qui - Sex
                [1, 3], // Seg - Qua
                [2, 4], // Ter - Qui
            ]),

            // 3 dias seguidos
            3 => (function () {
                $start = fake()->numberBetween(1, 4);
                return [$start, $start + 1, $start + 2];
            })(),

            // 4 dias seguidos
            4 => (function () {
                $start = fake()->numberBetween(1, 3);
                return [$start, $start + 1, $start + 2, $start + 3];
            })(),
        };
    }

    /**
     * Converte [1,3] em "Seg/Qua" etc.
     */
    protected function formatWeekdayLabels(array $weekdays): string
    {
        $map = [
            1 => 'Seg',
            2 => 'Ter',
            3 => 'Qua',
            4 => 'Qui',
            5 => 'Sex',
            6 => 'Sáb',
        ];

        $labels = array_map(
            fn ($day) => $map[$day] ?? $day,
            $weekdays
        );

        return implode('/', $labels);
    }

    /**
     * Cria os school_years de uma turma
     * e gera entre 1 e 4 invites por módulo, usando InviteFactory
     * e registrando em invite_dates.
     */
    protected function createSchoolYearsForClass(ClassModel $class, int $numModules): void
    {
        // Começa a partir de fevereiro de 2026
        $currentBaseDate = Carbon::parse('2026-02-01');
        $weekdays        = $class->weekday ?? [];
        $daysCount       = count($weekdays);

        for ($order = 1; $order <= $numModules; $order++) {

            // Datas desse módulo
            $dates = $this->generateModuleDates($weekdays, $currentBaseDate);

            // Cria o SchoolYear
            $schoolYear = SchoolYear::create([
                'class_id'   => $class->id,
                'invite_id'  => null, // vamos definir com o primeiro invite
                'dates'      => $dates,
                'sort_order' => $order,
            ]);

            // Quantos invites esse módulo vai ter? (1 a 4)
            $invitesCount  = fake()->numberBetween(1, 4);
            $firstInviteId = null;

            for ($i = 0; $i < $invitesCount; $i++) {
                // 👉 Usa InviteFactory, passando o SchoolYear
                $invite = Invite::factory()
                    ->forSchoolYear($schoolYear)
                    ->create();

                // 👉 Aqui criamos o invite_dates
                DB::table('invite_dates')->insert([
                    'invite_id'      => $invite->id,
                    'school_year_id' => $schoolYear->id,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                if ($i === 0) {
                    $firstInviteId = $invite->id;
                }
            }

            // Atualiza o school_year com o primeiro invite criado
            if ($firstInviteId !== null) {
                $schoolYear->update([
                    'invite_id' => $firstInviteId,
                ]);
            }

            // Define base do próximo módulo
            $lastDate = Carbon::parse(end($dates));
            $weekGap  = ($daysCount >= 3) ? 2 : 1;

            $currentBaseDate = $lastDate
                ->copy()
                ->addWeeks($weekGap);
        }
    }

    /**
     * Gera datas para UM módulo.
     */
    protected function generateModuleDates(array $weekdays, Carbon $baseDate): array
    {
        sort($weekdays);
        $daysCount = count($weekdays);
        $dates     = [];

        // 1 dia (sábado) → 2 sábados
        if ($daysCount === 1 && $weekdays[0] === 6) {
            $firstSaturday  = $this->nextOrSameDow($baseDate, 6);
            $secondSaturday = $firstSaturday->copy()->addWeek();

            $dates[] = $firstSaturday->toDateString();
            $dates[] = $secondSaturday->toDateString();

            return $dates;
        }

        // 2 dias na semana → 2 semanas (4 datas)
        if ($daysCount === 2) {
            $weekCount = 2;

            $weekStart = $baseDate->copy();

            for ($w = 0; $w < $weekCount; $w++) {
                $currentWeekBase = $weekStart->copy()->addWeeks($w);

                foreach ($weekdays as $dow) {
                    $date     = $this->nextOrSameDow($currentWeekBase, $dow);
                    $dates[]  = $date->toDateString();
                }
            }

            return $dates;
        }

        // 3 ou 4 dias → todos a partir da mesma base
        $weekBase = $baseDate->copy();

        foreach ($weekdays as $dow) {
            $date    = $this->nextOrSameDow($weekBase, $dow);
            $dates[] = $date->toDateString();
        }

        return $dates;
    }

    /**
     * Próximo OU mesmo dia da semana (1..6 = Seg..Sáb).
     */
    protected function nextOrSameDow(Carbon $date, int $dow): Carbon
    {
        $carbonDows = [
            1 => Carbon::MONDAY,
            2 => Carbon::TUESDAY,
            3 => Carbon::WEDNESDAY,
            4 => Carbon::THURSDAY,
            5 => Carbon::FRIDAY,
            6 => Carbon::SATURDAY,
        ];

        $targetDow = $carbonDows[$dow];

        if ($date->dayOfWeek === $targetDow) {
            return $date->copy();
        }

        return $date->copy()->next($targetDow);
    }
}
