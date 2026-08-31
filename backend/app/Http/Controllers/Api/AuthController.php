<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationMailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly NotificationMailer $mailer)
    {
    }

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

        // B2B accounts are agency-tier — they get admin-negotiated pricing,
        // so an admin reviews and approves each one before it can be used,
        // rather than granting access the instant the form is submitted.
        $requiresApproval = $data['user_type'] === 'b2b';

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company' => $data['company'] ?? null,
            'password' => $data['password'],
            'approval_status' => $requiresApproval ? 'pending' : 'approved',
        ]);

        $user->assignRole(self::USER_TYPE_ROLES[$data['user_type']]);

        if ($requiresApproval) {
            $this->mailer->pendingApproval($user);

            return response()->json([
                'pending_approval' => true,
                'message' => "Thanks for registering! Your B2B account is pending admin approval — you'll be able to log in once it's active.",
            ], 201);
        }

        $this->mailer->welcome($user);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'company' => $user->company,
                'gst_number' => $user->gst_number,
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

        if ($user->approval_status === 'pending') {
            throw ValidationException::withMessages([
                'email' => ["Your account is pending admin approval. You'll be able to log in once it's approved."],
            ]);
        }

        if ($user->approval_status === 'rejected') {
            throw ValidationException::withMessages([
                'email' => ['Your account application was not approved. Please contact support for details.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'company' => $user->company,
                'gst_number' => $user->gst_number,
                'roles' => $user->getRoleNames(),
            ],
        ]);
    }

    /**
     * Sends a reset link to the given email, if an account exists for it.
     * The response is deliberately identical either way — a distinguishable
     * "no account found" response would let anyone enumerate registered
     * emails through this endpoint.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $user = User::query()->where('email', $request->input('email'))->first();

        if ($user) {
            // Laravel's password broker generates and stores the token
            // (hashed, with its own expiry) the same way the built-in
            // Password::sendResetLink() would — only delivery is custom,
            // so it goes through this app's own Mailable/NotificationMailer
            // pipeline (branded layout, SMTP settings from the admin panel)
            // instead of Laravel's default notification-based email.
            $token = Password::broker()->createToken($user);
            $resetUrl = rtrim(config('app.frontend_url'), '/')
                .'/reset-password?token='.$token.'&email='.urlencode($user->email);

            $this->mailer->passwordReset($user, $resetUrl);
        }

        return response()->json([
            'message' => "If an account exists for that email, we've sent a password reset link.",
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::broker()->reset(
            $data,
            function (User $user, string $password) {
                $user->update(['password' => $password]);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return response()->json(['message' => 'Your password has been reset. You can now log in.']);
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
            'company' => $user->company,
            'gst_number' => $user->gst_number,
            'roles' => $user->getRoleNames(),
        ]);
    }
}
