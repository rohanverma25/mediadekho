<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\RazorpayApiException;
use App\Exceptions\RazorpayNotConfiguredException;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\MediaInventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use App\Services\PricingService;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function __construct(
        private readonly PricingService $pricing,
        private readonly RazorpayService $razorpay,
    ) {
    }

    /**
     * Creates a pending Order + its Razorpay counterpart from the caller's
     * cart. Every line amount is re-resolved server-side via PricingService
     * — the client only ever supplies inventory_id/quantity, never a price,
     * matching the same "never trust the client's number" guarantee the
     * pricing endpoints already enforce.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_id' => ['required', 'integer', 'exists:media_inventory,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user('sanctum');
        $unavailable = [];
        $resolvedItems = [];

        foreach ($validated['items'] as $line) {
            $inventory = MediaInventory::query()->with('category')->find($line['inventory_id']);
            $price = $inventory ? $this->pricing->priceForUser($inventory, $user) : ['available' => false];

            if (! $inventory || ! ($price['available'] ?? false)) {
                $unavailable[] = $line['inventory_id'];

                continue;
            }

            $resolvedItems[] = [
                'inventory' => $inventory,
                'quantity' => $line['quantity'],
                'price' => $price,
            ];
        }

        if (! empty($unavailable)) {
            return response()->json([
                'message' => 'Some items in your cart are no longer available.',
                'errors' => ['items' => $unavailable],
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($resolvedItems, $user) {
                $subtotal = 0;
                $discountTotal = 0;
                $taxTotal = 0;
                $grandTotal = 0;

                foreach ($resolvedItems as $line) {
                    $qty = $line['quantity'];
                    $price = $line['price'];
                    $subtotal += $price['list_price'] * $qty;
                    $discountTotal += $price['discount_amount'] * $qty;
                    $taxTotal += $price['tax_amount'] * $qty;
                    $grandTotal += $price['final_price'] * $qty;
                }

                $order = Order::query()->create([
                    'user_id' => $user->id,
                    'subtotal' => round($subtotal, 2),
                    'discount_total' => round($discountTotal, 2),
                    'tax_total' => round($taxTotal, 2),
                    'grand_total' => round($grandTotal, 2),
                    'status' => 'pending',
                ]);

                foreach ($resolvedItems as $line) {
                    $inventory = $line['inventory'];
                    $qty = $line['quantity'];
                    $price = $line['price'];

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'inventory_id' => $inventory->id,
                        'title' => $inventory->title,
                        'category' => $inventory->category?->name,
                        'quantity' => $qty,
                        'list_price' => $price['list_price'],
                        'discount_amount' => $price['discount_amount'],
                        'unit_price' => $price['price'],
                        'tax_percentage' => $price['tax_percentage'],
                        'tax_amount' => $price['tax_amount'],
                        'line_total' => round($price['final_price'] * $qty, 2),
                    ]);
                }

                $razorpayOrder = $this->razorpay->createOrder(
                    (int) round($order->grand_total * 100),
                    $order->order_number,
                );

                $order->update(['razorpay_order_id' => $razorpayOrder['id']]);

                return $order;
            });
        } catch (RazorpayNotConfiguredException|RazorpayApiException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        return response()->json([
            'razorpay_key_id' => Setting::current()->razorpay_key_id,
            'razorpay_order_id' => $order->razorpay_order_id,
            'amount' => (int) round($order->grand_total * 100),
            'currency' => $order->currency,
            'order' => ['id' => $order->id, 'order_number' => $order->order_number],
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->where('user_id', $request->user('sanctum')->id)
            ->with('items')
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => OrderResource::collection($orders->items()),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, Order $order): OrderResource
    {
        Gate::forUser($request->user('sanctum'))->authorize('view', $order);

        $order->load('items.inventory');

        return new OrderResource($order);
    }

    /**
     * The ONLY path that can ever move an order to `paid` — always via a
     * verified HMAC signature, never by trusting a client-reported success.
     */
    public function verify(Request $request, Order $order): JsonResponse
    {
        Gate::forUser($request->user('sanctum'))->authorize('view', $order);

        $validated = $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        if ($validated['razorpay_order_id'] !== $order->razorpay_order_id) {
            return response()->json(['message' => 'Payment does not match this order.'], 422);
        }

        $isValid = $this->razorpay->verifySignature(
            $validated['razorpay_order_id'],
            $validated['razorpay_payment_id'],
            $validated['razorpay_signature'],
        );

        if (! $isValid) {
            $order->update(['status' => 'failed']);

            return response()->json(['message' => 'Payment verification failed.'], 422);
        }

        $order->update([
            'status' => 'paid',
            'razorpay_payment_id' => $validated['razorpay_payment_id'],
            'razorpay_signature' => $validated['razorpay_signature'],
            'paid_at' => now(),
        ]);

        return response()->json(new OrderResource($order->load('items.inventory')));
    }
}
