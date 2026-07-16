<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'login_id' => fake()->unique()->userName(),

            'password' => Hash::make('password'),

            'role' => 'operator',

            'last_login_at' => null,

            'remember_token' => Str::random(10),
        ];
    }
}