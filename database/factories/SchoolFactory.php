<?php

namespace Database\Factories;

use App\Models\ClassModel;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolFactory extends Factory
{
    protected $model = School::class;

    /**
     * Define o estado padrão da School.
     */
    public function definition(): array
    {
        return [
            'name' => 'Carisma ' . fake()->city(),
        ];
    }

    /**
     * Depois de criar a School, cria automaticamente algumas turmas
     * usando a ClassModelFactory, já amarradas nesta escola.
     */
    public function configure()
    {
        return $this->afterCreating(function (School $school) {


            ClassModel::factory()
                ->count(1)
                ->create([
                    'school_id' => $school->id,
                ]);
        });
    }
}
