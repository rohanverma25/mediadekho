<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * The logged-in customer's own account details. Password is optional —
     * a blank submission leaves it unchanged, same "blank means don't
     * change" convention already used for the Razorpay secret in Settings.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        // GSTIN is a fixed, government-defined 15-character format
        // (state code + PAN + entity code + 'Z' + checksum) and is always
        // canonically uppercase — normalize case here rather than forcing
        // the customer to type it exactly right.
        if ($request->filled('gst_number')) {
            $request->merge(['gst_number' => strtoupper($request->input('gst_number'))]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'company' => ['nullable', 'string', 'max:255'],
            'gst_number' => [
                'nullable',
                'string',
                'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/',
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'gst_number.regex' => 'Enter a valid 15-character GSTIN (e.g. 22AAAAA0000A1Z5).',
        ]);

        $data = collect($validated)->only(['name', 'email', 'phone', 'company', 'gst_number'])->all();

        if (filled($validated['password'] ?? null)) {
            $data['password'] = $validated['password'];
        }

        $user->update($data);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'company' => $user->company,
            'gst_number' => $user->gst_number,
            'roles' => $user->getRoleNames(),
        ]);
    }
}
