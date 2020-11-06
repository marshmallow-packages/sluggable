<?php

namespace Marshmallow\Sluggable\Tests\Database\Factories;

use Marshmallow\Sluggable\Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'age' => 33,
            'gender' => 'male',
            'status' => 'offline',
        ];
    }
}
