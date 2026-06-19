<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'name' => $this->faker->words(3, true),
            'sku' => strtoupper($this->faker->bothify('??-####')),
            'price' => $this->faker->randomFloat(2, 5000, 100000),
            'description' => $this->faker->paragraph(),
            'is_active' => true,
        ];
    }
}
