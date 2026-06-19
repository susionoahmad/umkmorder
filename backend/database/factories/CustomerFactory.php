<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1, // Default to tenant 1
            'name' => $this->faker->name(),
            'whatsapp' => '62812' . $this->faker->numerify('########'),
            'address' => $this->faker->address(),
            'notes' => $this->faker->sentence(),
            'source' => $this->faker->randomElement(['catalog', 'whatsapp', 'manual', 'import']),
        ];
    }
}
