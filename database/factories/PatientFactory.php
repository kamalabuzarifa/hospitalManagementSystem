<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'dob' => $this->faker->dateTimeBetween('-80 years', '-5 years'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'medical_history' => $this->faker->sentences(3, true),
        ];
    }
}
