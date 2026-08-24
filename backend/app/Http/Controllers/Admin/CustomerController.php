<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * The four self-registration roles — matches RolesAndPermissionsSeeder.
     * Staff accounts (Super Admin/Admin) never show up in this list.
     */
    private const CUSTOMER_ROLES = ['Retail Customer', 'B2C Customer', 'B2B Customer', 'Enterprise Customer'];

    /**
     * Gated by the `staff` route middleware, same as ActivityLogController
     * — a read-only report, not a single-model resource that needs its own
     * Policy.
     */
    public function index(): View
    {
        return view('admin.customers.index');
    }

    public function data(): JsonResponse
    {
        $customers = User::query()
            ->role(self::CUSTOMER_ROLES)
            ->withCount('orders')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'company' => $customer->company,
                'role' => $customer->getRoleNames()->first(),
                'orders_count' => $customer->orders_count,
                'created_at' => $customer->created_at->format('Y-m-d H:i'),
            ]);

        return response()->json(['data' => $customers]);
    }
}
