<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeacherSubjectSeeder extends Seeder
{
    public function run(): void
    {
        // (Opcional) Limpar a tabela antes de popular, se você quiser sempre recomeçar do zero
        // DB::table('teachers_x_subjects')->truncate();

        // Pega todos os IDs de professores que existem no banco
        $teacherIds = DB::table('teachers')->pluck('id');

        // Lista de matérias disponíveis (IDs de 1 a 31)
        $allSubjectIds = range(1, 31);

        foreach ($teacherIds as $teacherId) {

            // Embaralha a lista e pega 4 matérias diferentes
            $subjectIds = collect($allSubjectIds)
                ->shuffle()
                ->take(4);

            foreach ($subjectIds as $subjectId) {
                DB::table('teachers_x_subjects')->insert([
                    'teacher_id' => $teacherId,
                    'subject_id' => $subjectId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
