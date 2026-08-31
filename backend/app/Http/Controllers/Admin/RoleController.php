<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Manages admin-panel roles (Super Admin, Admin, and any custom roles an
 * admin creates) and which permissions each one carries. Deliberately
 * separate from the four customer-tier "roles" (Retail/B2C/B2B/Enterprise
 * Customer) — those only drive PricingService's tier resolution and never
 * appear here, so there's no risk of confusing the two systems.
 *
 * Every action is gated on the `role.manage` permission directly (Spatie's
 * Role model lives outside App\Models, so Laravel's policy auto-discovery
 * won't find a policy for it — same reasoning CustomerController uses for
 * skipping a Policy class).
 */
class RoleController extends Controller
{
    /**
     * Human-friendly group labels for the permission checkboxes, keyed by
     * the part of the permission name before the first dot. Anything not
     * listed here falls back to a title-cased version of the key.
     */
    private const GROUP_LABELS = [
        'inventory' => 'Media Inventory',
        'category' => 'Media Categories',
        'faq' => 'FAQs',
        'blog' => 'Blogs',
        'magazine' => 'Magazines',
        'news' => 'News',
        'lead' => 'Contact Leads',
        'award' => 'Awards',
        'award-nomination' => 'Award Nominations',
        'job' => 'Careers',
        'job-application' => 'Job Applications',
        'client-logo' => 'Client Logos',
        'industry' => 'Industries',
        'stat' => 'Stats',
        'video' => 'Videos',
        'announcement' => 'Announcements',
        'settings' => 'Settings',
        'page-meta' => 'Meta Tags',
        'order' => 'Orders',
        'role' => 'Roles & Permissions',
        'staff-user' => 'Staff Users',
    ];

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('role.manage'), 403);

        return view('admin.roles.index', ['groupedPermissions' => $this->groupedPermissions()]);
    }

    public function data(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('role.manage'), 403);

        $roles = Role::query()
            ->whereNotIn('name', RolesAndPermissionsSeeder::CUSTOMER_ROLES)
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions_count' => $role->permissions_count,
                'users_count' => $role->users_count,
                'protected' => in_array($role->name, RolesAndPermissionsSeeder::PROTECTED_ROLES, true),
            ]);

        return response()->json(['data' => $roles]);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->can('role.manage'), 403);

        return view('admin.roles.form', [
            'role' => null,
            'groupedPermissions' => $this->groupedPermissions(),
            'checkedPermissions' => collect(old('permissions', [])),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('role.manage'), 403);

        $data = $this->validated($request);

        $role = Role::query()->create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions']);

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Request $request, Role $role): View
    {
        abort_unless($request->user()->can('role.manage'), 403);
        $this->guardAgainstCustomerRole($role);

        return view('admin.roles.form', [
            'role' => $role,
            'groupedPermissions' => $this->groupedPermissions(),
            'checkedPermissions' => collect(old('permissions', $role->permissions->pluck('name'))),
            'protected' => in_array($role->name, RolesAndPermissionsSeeder::PROTECTED_ROLES, true),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()->can('role.manage'), 403);
        $this->guardAgainstCustomerRole($role);

        $data = $this->validated($request, $role);

        // Protected roles (Super Admin, Admin, ...) are relied on by name
        // elsewhere in the codebase — their permissions can still be
        // tuned, but renaming one out from under that code would silently
        // break it, so the name field is simply not touched for these.
        if (! in_array($role->name, RolesAndPermissionsSeeder::PROTECTED_ROLES, true)) {
            $role->update(['name' => $data['name']]);
        }

        $role->syncPermissions($data['permissions']);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Request $request, Role $role): JsonResponse
    {
        abort_unless($request->user()->can('role.manage'), 403);
        $this->guardAgainstCustomerRole($role);

        if (in_array($role->name, RolesAndPermissionsSeeder::PROTECTED_ROLES, true)) {
            return response()->json(['message' => 'This is a built-in role and cannot be deleted.'], 422);
        }

        if ($role->users()->exists()) {
            return response()->json(['message' => 'Reassign every user with this role before deleting it.'], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted.']);
    }

    /**
     * The four customer-tier roles are hidden from this screen entirely —
     * reachable only if someone crafts a request for their id directly.
     */
    private function guardAgainstCustomerRole(Role $role): void
    {
        abort_if(in_array($role->name, RolesAndPermissionsSeeder::CUSTOMER_ROLES, true), 404);
    }

    /**
     * @return array{name: string, permissions: array<int, string>}
     */
    private function validated(Request $request, ?Role $role = null): array
    {
        // A role with every checkbox unchecked submits no `permissions` key
        // at all (HTML forms drop empty checkbox groups entirely) — default
        // it to an empty array so syncPermissions() below always gets a
        // real array rather than an undefined key.
        $request->merge(['permissions' => $request->input('permissions', [])]);

        return $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'name')->ignore($role?->id),
                Rule::notIn(RolesAndPermissionsSeeder::CUSTOMER_ROLES),
            ],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);
    }

    /**
     * @return array<string, \Illuminate\Support\Collection<int, Permission>>
     */
    private function groupedPermissions(): array
    {
        return Permission::query()
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => Str::before($permission->name, '.'))
            ->mapWithKeys(fn ($permissions, $key) => [
                (self::GROUP_LABELS[$key] ?? Str::headline($key)) => $permissions,
            ])
            ->all();
    }
}
