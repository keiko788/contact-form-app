<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Contact;
use App\Models\Category;


class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->value('id'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'gender' => $this->faker->numberBetween(1,3),
            'email' => $this->faker->email(),
            'tel' => $this->faker->numerify('0##########'),
            'address' => $this->faker->prefecture() . $this->faker->city() . $this->faker->streetAddress(),
            'building' => $this->faker->optional()->secondaryAddress(),
            'detail' => $this->faker->realText(60),
        ];
    }
}
