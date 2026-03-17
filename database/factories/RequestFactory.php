<?php

namespace Database\Factories;

use App\Models\Request;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Request>
 */
class RequestFactory extends Factory
{
	protected $model = Request::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
		return [
			'client_name' => $this->faker->name(),
			'phone' => $this->faker->phoneNumber(),
			'address' => $this->faker->address(),
			'problem_text' => $this->faker->sentence(10),
			'status' => Request::STATUS_NEW,
		];
    }
}
