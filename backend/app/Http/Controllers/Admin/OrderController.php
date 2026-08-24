<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Staff can only move a paid order to one of these — `paid` itself is
     * never staff-settable, that must always come from a verified Razorpay
     * signature (OrderController@verify in the customer API).
     */
    private const STAFF_SETTABLE_STATUSES = ['cancelled', 'refunded'];

    public function index(): View
    {
        $this->authorize('viewAny', Order::class);

        return view('admin.orders.index');
    }

    public function data(): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::query()
            ->with('user')
            ->withCount('items')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->user?->name ?? 'Guest',
                'customer_email' => $order->user?->email,
                'items_count' => $order->items_count,
                'grand_total' => (float) $order->grand_total,
                'status' => $order->status,
                'created_at' => $order->created_at->format('Y-m-d H:i'),
            ]);

        return response()->json(['data' => $orders]);
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load(['items.inventory', 'user']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', self::STAFF_SETTABLE_STATUSES)],
        ]);

        $order->update($data);

        return response()->json(['message' => 'Order updated.', 'order' => $order]);
    }
}
