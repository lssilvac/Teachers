<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => null,
            'description' => fake()->paragraph(),
            'picture' => fake()->imageUrl(),
            'birth_date' => fake()->date(),
            'email' => null,
            'street_number' => fake()->buildingNumber(),
            'route' => fake()->streetName(),
            'sublocality_level_1' => fake()->citySuffix(),
            'locality' => fake()->city(),
            'administrative_area_level_1' => fake()->stateAbbr(),
            'administrative_area_level_2' => fake()->state(),
            'country' => fake()->countryCode(),
            'postal_code' => fake()->postcode(),
            'place_id' => fake()->uuid(),
            'formatted_address' => fake()->address(),
            'latitude' => fake()->latitude(-90, 90),
            'longitude' => fake()->longitude(-180, 180),
            'google_search' => fake()->url(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Teacher $teacher) {

            // 1) Preencher nome e e-mail com o do User
            $teacher->update([
                'name'  => $teacher->user->name,
                'email' => $teacher->user->email,
            ]);

            // 2) Escolher 4 matérias aleatórias entre 1 e 31
            $subjectIds = collect(range(1, 31))
                ->shuffle()
                ->take(4);

            // 3) Criar as linhas na tabela pivot
            foreach ($subjectIds as $id) {
                DB::table('teachers_x_subjects')->insert([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
