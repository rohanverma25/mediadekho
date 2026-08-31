<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/**
 * Manages who can log into the admin panel and which role they hold —
 * distinct from Admin\CustomerController, which manages the four
 * self-registered customer tiers (Retail/B2C/B2B/Enterprise). Every action
 * is gated on the `staff-user.manage` permission directly, same reasoning
 * as RoleController for skipping a dedicated Policy class.
 */
class StaffUserController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('staff-user.manage'), 403);

        $roles = Role::query()
            ->whereNotIn('name', RolesAndPermissionsSeeder::CUSTOMER_ROLES)
            ->orderBy('name')
            ->get();

        return view('admin.staff-users.index', compact('roles'));
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('staff-user.manage'), 403);

        $staff = User::query()
            ->role($this->staffRoleNames())
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
                'is_self' => $user->id === $request->user()->id,
                'created_at' => $user->created_at->format('Y-m-d'),
            ]);

        return response()->json(['data' => $staff]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('staff-user.manage'), 403);

        $data = $this->validated($request, requirePassword: true);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'approval_status' => 'approved',
        ]);
        $user->assignRole($data['role']);

        return response()->json(['message' => 'Staff account created.'], 201);
    }

    public function edit(Request $request, User $staffUser): JsonResponse
    {
        abort_unless($request->user()->can('staff-user.manage'), 403);
        $this->guardAgainstNonStaff($staffUser);

        return response()->json([
            'user' => [
                'id' => $staffUser->id,
                'name' => $staffUser->name,
                'email' => $staffUser->email,
                'role' => $staffUser->getRoleNames()->first(),
            ],
        ]);
    }

    public function update(Request $request, User $staffUser): JsonResponse
    {
        abort_unless($request->user()->can('staff-user.manage'), 403);
        $this->guardAgainstNonStaff($staffUser);

        $data = $this->validated($request, requirePassword: false, ignoreUserId: $staffUser->id);

        $update = ['name' => $data['name'], 'email' => $data['email']];
        if (filled($data['password'] ?? null)) {
            $update['password'] = $data['password'];
        }

        $staffUser->update($update);
        $staffUser->syncRoles([$data['role']]);

        return response()->json(['message' => 'Staff account updated.']);
    }

    public function destroy(Request $request, User $staffUser): JsonResponse
    {
        abort_unless($request->user()->can('staff-user.manage'), 403);
        $this->guardAgainstNonStaff($staffUser);

        if ($staffUser->id === $request->user()->id) {
            return response()->json(['message' => 'You cannot remove your own admin access.'], 422);
        }

        $staffUser->delete();

        return response()->json(['message' => 'Staff account removed.']);
    }

    /**
     * Customer accounts aren't reachable through this controller even if
     * someone crafts a request for their id directly.
     */
    private function guardAgainstNonStaff(User $user): void
    {
        abort_unless($user->hasAnyRole($this->staffRoleNames()), 404);
    }

    /**
     * Every role except the four customer tiers — Super Admin, Admin, and
     * any custom role created via the Roles admin module.
     */
    private function staffRoleNames(): array
    {
        return Role::query()
            ->whereNotIn('name', RolesAndPermissionsSeeder::CUSTOMER_ROLES)
            ->pluck('name')
            ->all();
    }

    /**
     * @return array{name: string, email: string, password: ?string, role: string}
     */
    private function validated(Request $request, bool $requirePassword, ?int $ignoreUserId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignoreUserId)],
            'password' => [$requirePassword ? 'required' : 'nullable', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in($this->staffRoleNames())],
        ]);
    }
}
