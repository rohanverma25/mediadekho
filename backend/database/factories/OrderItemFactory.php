<?php

namespace Database\Factories;

use App\Models\MediaInventory;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $listPrice = $this->faker->randomFloat(2, 500, 20000);
        $discountAmount = 0;
        $unitPrice = $listPrice - $discountAmount;
        $taxPercentage = 18;
        $taxAmount = round($unitPrice * ($taxPercentage / 100), 2);
        $quantity = $this->faker->numberBetween(1, 3);

        return [
            'order_id' => Order::factory(),
            'inventory_id' => MediaInventory::factory(),
            'title' => $this->faker->company().' Ad Placement',
            'category' => $this->faker->randomElement(['Magazine', 'Airport', 'Digital']),
            'quantity' => $quantity,
            'list_price' => $listPrice,
            'discount_amount' => $discountAmount,
            'unit_price' => $unitPrice,
            'tax_percentage' => $taxPercentage,
            'tax_amount' => $taxAmount,
            'line_total' => round(($unitPrice + $taxAmount) * $quantity, 2),
        ];
    }
}
