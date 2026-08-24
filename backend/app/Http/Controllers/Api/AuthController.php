<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Maps the storefront's "Account Type" dropdown value onto the Spatie
     * role PricingService::resolveTier() checks for. Anything not in here
     * (or omitted) falls back to Retail — the public rate — same as guests.
     */
    private const USER_TYPE_ROLES = [
        'retail' => 'Retail Customer',
        'b2c' => 'B2C Customer',
        'b2b' => 'B2B Customer',
        'enterprise' => 'Enterprise Customer',
    ];

    /**
     * Public self-registration for storefront customers. Staff/admin
     * accounts are never created through this endpoint — only the four
     * customer tiers are assignable here.
     */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'user_type' => ['required', 'string', Rule::in(array_keys(self::USER_TYPE_ROLES))],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'password' => $data['password'],
        ]);

        $user->assignRole(self::USER_TYPE_ROLES[$data['user_type']]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->getRoleNames(),
            ],
        ], 201);
    }

    /**
     * Issue a Sanctum token. Used by retail/B2C/B2B/Enterprise customers
     * (and staff, if they need API access) — the returned token carries
     * whatever role the user has, which PricingService uses downstream to
     * resolve the correct price tier on every subsequent request.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::once($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['These credentials do not match our records.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'roles' => $user->getRoleNames(),
        ]);
    }
}
