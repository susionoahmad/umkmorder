<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 10000, 200000);
        $discount = $this->faker->randomElement([0.00, 5000.00, 10000.00]);
        $shipping = 10000.00;
        $grandTotal = $subtotal - $discount + $shipping;

        return [
            'tenant_id' => 1,
            'customer_id' => 1,
            'invoice_number' => 'INV/' . $this->faker->date('Ymd') . '/' . $this->faker->numerify('####'),
            'order_date' => $this->faker->date(),
            'order_type' => $this->faker->randomElement(['delivery', 'pickup']),
            'status' => $this->faker->randomElement(['draft', 'new', 'processing', 'shipped', 'completed', 'cancelled']),
            'source' => $this->faker->randomElement(['catalog', 'whatsapp', 'manual', 'api']),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping_cost' => $shipping,
            'grand_total' => $grandTotal,
            'notes' => $this->faker->sentence(),
        ];
    }
}
