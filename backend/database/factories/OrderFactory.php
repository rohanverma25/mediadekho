<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 500, 50000);
        $discountTotal = 0;
        $taxTotal = round($subtotal * 0.18, 2);
        $grandTotal = round($subtotal - $discountTotal + $taxTotal, 2);

        return [
            'user_id' => User::factory(),
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'grand_total' => $grandTotal,
            'currency' => 'INR',
            'status' => 'pending',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => 'paid',
            'razorpay_order_id' => 'order_'.$this->faker->bothify('##########'),
            'razorpay_payment_id' => 'pay_'.$this->faker->bothify('##########'),
            'razorpay_signature' => $this->faker->sha256(),
            'paid_at' => now(),
        ]);
    }
}
