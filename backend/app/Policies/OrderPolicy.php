<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Staff-only listing view (the admin Orders module). Customers reach
     * their own orders via the ownership check in view(), not this ability.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('order.view');
    }

    public function view(User $user, Order $order): bool
    {
        return $order->user_id === $user->id || $user->can('order.view');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->can('order.manage');
    }
}
