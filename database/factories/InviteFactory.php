<?php

namespace Database\Factories;

use App\Models\Invite;
use App\Models\SchoolYear;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class InviteFactory extends Factory
{
    protected $model = Invite::class;

    /**
     * SchoolYear associado a este invite (opcional).
     * Usamos para:
     * - filtrar os subjects conforme o class_type da turma
     */
    protected ?SchoolYear $schoolYear = null;

    /**
     * Define que este invite é para um SchoolYear específico.
     * Ex.: Invite::factory()->forSchoolYear($schoolYear)->create();
     */
    public function forSchoolYear(SchoolYear $schoolYear): static
    {
        // Clonamos a factory para não vazar o schoolYear para outros usos
        $clone = clone $this;
        $clone->schoolYear = $schoolYear;

        return $clone;
    }

    public function definition(): array
    {
        if (! $this->schoolYear) {
            throw new Exception('InviteFactory precisa de um SchoolYear. Use forSchoolYear($schoolYear).');
        }

        // 1) Descobrir o class_type da turma desse SchoolYear
        $class       = $this->schoolYear->class;
        $classTypeId = $class->class_type_id ?? null;

        if (! $classTypeId) {
            throw new Exception('SchoolYear não está associado a uma turma com class_type_id.');
        }

        // 2) Montar a lista de subjects permitidos para esse class_type
        if ($classTypeId === 1) {
            // class_type 1 → subjects 1..18 e 31
            $allowedSubjects = array_merge(range(1, 18), [31]);
        } elseif ($classTypeId === 2) {
            // class_type 2 → subjects 19..30
            $allowedSubjects = range(19, 30);
        } else {
            throw new Exception("class_type_id {$classTypeId} não suportado na InviteFactory.");
        }

        // 3) Escolher UM par (teacher_id, subject_id) da pivot
        //    que respeite esse conjunto de matérias permitidas
        $pivotRow = DB::table('teachers_x_subjects')
            ->whereIn('subject_id', $allowedSubjects)
            ->inRandomOrder()
            ->first();

        if (! $pivotRow) {
            throw new Exception(
                'Nenhum professor encontrado em teachers_x_subjects com matérias compatíveis com o class_type ' . $classTypeId
            );
        }

        // Agora SIM: subject manda, professor vem junto
        $teacherId = $pivotRow->teacher_id;
        $subjectId = $pivotRow->subject_id;

        return [
            'teacher_id'  => $teacherId,
            'subject_id'  => $subjectId,
            'status'      => 'pending',
            'canceled_by' => null,
            'reason'      => null,
        ];
    }
}
