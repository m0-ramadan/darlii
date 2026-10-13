<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Admin\Concerns\UploadsImages;
use App\Http\Controllers\Controller;
use App\Models\AgentProfile;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\AdminStatsService;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use InteractsWithAdminPanel;
    use UploadsImages;

    /**
     * Roles the signed-in account is allowed to hand out.
     * - super-admin / admin: any panel role + customer
     * - manager (مسؤول): only the agent role under themselves
     */
    private function assignableRoles(): array
    {
        if ($this->admin()->isAdmin()) {
            return array_merge(\App\Support\PermissionCatalog::panelRoles(), ['customer']);
        }

        return ['agent'];
    }

    public function index(Request $request)
    {
        $this->authorizePermission('users.view');

        $users = $this->userQuery()
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = trim((string) $request->input('q'));
                $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%"));
            })
            ->when($request->filled('role'), fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('slug', $request->input('role'))))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('manager_id'), fn ($q) => $q->where('manager_id', $request->input('manager_id')))
            ->with(['roles', 'manager'])
            ->withCount(['properties', 'subordinates'])
            ->latest()
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::query()->orderByDesc('level')->get(),
            'managers' => $this->assignableManagers(),
            'filters' => $request->only(['q', 'role', 'status', 'manager_id']),
            'title' => 'المستخدمون',
        ]);
    }

    /** عرض شجرة الفريق: أدمن ← مسؤول ← سمسار. */
    public function hierarchy()
    {
        $this->authorizePermission('users.view');

        $admins = User::query()
            ->whereHas('roles', fn ($q) => $q->whereIn('slug', ['super-admin', 'admin']))
            ->with(['roles', 'subordinates.roles', 'subordinates.subordinates.roles'])
            ->withCount('properties')
            ->get();

        $unassigned = User::query()
            ->whereNull('manager_id')
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('slug', ['super-admin', 'admin']))
            ->with('roles')
            ->get();

        return view('admin.users.hierarchy', [
            'admins' => $admins,
            'unassigned' => $unassigned,
            'title' => 'المسؤولون والسماسرة',
        ]);
    }

    public function create()
    {
        $this->authorizePermission('users.create');

        return view('admin.users.form', [
            'user' => new User(['status' => 'active', 'manager_id' => $this->admin()->isManager() ? $this->admin()->id : null]),
            'roles' => Role::query()->whereIn('slug', $this->assignableRoles())->orderByDesc('level')->get(),
            'managers' => $this->assignableManagers(),
            'title' => 'إضافة مستخدم',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizePermission('users.create');
        $data = $this->validated($request);

        $user = DB::transaction(function () use ($data, $request): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
                'preferred_locale' => 'ar',
                // Every non-admin account must report to someone: an explicit
                // manager, otherwise whoever created it (admins may be top level).
                'manager_id' => $data['manager_id'] ?? $this->defaultManagerIdFor($data['roles'] ?? []),
                'email_verified_at' => now(),
            ]);

            $user->roles()->sync($this->allowedRoleIds($data['roles']));
            $this->syncAgentProfile($user, $request, $data);

            return $user;
        });

        AuditLogger::log('user.created', $user, [], ['email' => $user->email, 'roles' => $data['roles']]);

        return redirect()->route('admin.users.index')
            ->with('success', 'تم إنشاء المستخدم ' . $user->name . ' بنجاح. كلمة المرور: ' . $data['password']);
    }

    public function show(User $user)
    {
        $this->authorizePermission('users.view');
        $this->guardScope($user);

        $stats = AdminStatsService::for($this->admin());

        return view('admin.users.show', [
            'user' => $user->load(['roles', 'manager', 'subordinates.roles', 'agentProfile.translations']),
            'properties' => $user->properties()->with(['translations', 'currency'])->latest()->limit(10)->get(),
            'propertiesCount' => $user->properties()->count(),
            'leadsCount' => $user->assignedLeads()->count(),
            'visibleProperties' => (clone $stats->propertyScope())->where('owner_id', $user->id)->count(),
            'title' => $user->name,
            'subtitle' => $user->email,
        ]);
    }

    public function edit(User $user)
    {
        $this->authorizePermission('users.update');
        $this->guardScope($user);

        return view('admin.users.form', [
            'user' => $user->load('roles', 'agentProfile'),
            'roles' => Role::query()->whereIn('slug', array_unique(array_merge($this->assignableRoles(), $user->roles->pluck('slug')->all())))->orderByDesc('level')->get(),
            'managers' => $this->assignableManagers()->reject(fn (User $manager) => $manager->id === $user->id),
            'title' => 'تعديل: ' . $user->name,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizePermission('users.update');
        $this->guardScope($user);
        $data = $this->validated($request, $user);

        $old = $user->only(['name', 'email', 'status', 'manager_id']);

        DB::transaction(function () use ($user, $data, $request): void {
            $payload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'status' => $data['status'],
                'notes' => $data['notes'] ?? null,
                'manager_id' => $data['manager_id'] ?? null,
            ];

            if (! empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            $user->update($payload);

            if (! empty($data['roles'])) {
                $user->roles()->sync($this->allowedRoleIds($data['roles']));
            }

            $this->syncAgentProfile($user, $request, $data);
        });

        AuditLogger::log('user.updated', $user, $old, $user->fresh()->only(array_keys($old)));

        return back()->with('success', 'تم تحديث بيانات المستخدم.');
    }

    public function destroy(User $user)
    {
        $this->authorizePermission('users.delete');
        $this->guardScope($user);

        abort_if($user->id === $this->admin()->id, 403, 'لا يمكنك حذف حسابك الحالي.');
        abort_if($user->isAdmin() && ! $this->admin()->isSuperAdmin(), 403, 'لا يمكن حذف حساب أدمن إلا من سوبر أدمن.');

        AuditLogger::log('user.deleted', $user, ['email' => $user->email, 'name' => $user->name]);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'تم حذف المستخدم.');
    }

    public function toggleStatus(User $user)
    {
        $this->authorizePermission('users.update');
        $this->guardScope($user);
        abort_if($user->id === $this->admin()->id, 403, 'لا يمكنك إيقاف حسابك.');

        $user->update(['status' => $user->status === 'active' ? 'suspended' : 'active']);
        AuditLogger::log('user.status', $user, [], ['status' => $user->status]);

        return back()->with('success', $user->status === 'active' ? 'تم تفعيل الحساب.' : 'تم إيقاف الحساب.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->authorizePermission('users.update');
        $this->guardScope($user);

        $data = $request->validate(['password' => ['required', 'string', 'min:8', 'confirmed']]);
        $user->update(['password' => Hash::make($data['password'])]);

        AuditLogger::log('user.password_reset', $user);

        return back()->with('success', 'تم تغيير كلمة المرور بنجاح.');
    }

    /** ربط مستخدم بمسؤول (أو فصله). */
    public function assignManager(Request $request, User $user)
    {
        $this->authorizePermission('users.assign-manager');
        $this->guardScope($user);

        $data = $request->validate(['manager_id' => ['nullable', 'exists:users,id']]);
        abort_if((int) ($data['manager_id'] ?? 0) === $user->id, 422, 'لا يمكن ربط المستخدم بنفسه.');

        if (! empty($data['manager_id']) && ! $this->admin()->isAdmin()) {
            abort_unless((int) $data['manager_id'] === $this->admin()->id, 403, 'يمكنك الربط بحسابك فقط.');
        }

        $user->update(['manager_id' => $data['manager_id'] ?? null]);
        AuditLogger::log('user.manager_assigned', $user, [], ['manager_id' => $user->manager_id]);

        return back()->with('success', 'تم تحديث المسؤول المتابع.');
    }

    // ------------------------------------------------------------------ داخلي
    private function userQuery()
    {
        $query = User::query();
        $admin = $this->admin();

        if ($admin->isAdmin()) {
            return $query;
        }

        // المسؤول يرى نفسه وسماسرته فقط.
        return $query->where(fn ($q) => $q->whereIn('id', $admin->visibleUserIds()));
    }

    private function guardScope(User $user): void
    {
        if ($this->admin()->isAdmin()) {
            return;
        }

        abort_unless(in_array($user->id, $this->admin()->visibleUserIds()->all(), true), 403, 'هذا المستخدم خارج نطاقك.');
    }

    /** @param list<int|string> $roleIds */
    private function allowedRoleIds(array $roleIds): array
    {
        return Role::query()
            ->whereIn('id', $roleIds)
            ->whereIn('slug', $this->assignableRoles())
            ->pluck('id')
            ->all();
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
            'manager_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'roles' => [$user ? 'nullable' : 'required', 'array'],
            'roles.*' => ['integer', 'exists:roles,id'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'agent_profile.job_title' => ['nullable', 'string', 'max:150'],
            'agent_profile.bio' => ['nullable', 'string', 'max:2000'],
            'agent_profile.whatsapp' => ['nullable', 'string', 'max:50'],
            'agent_profile.years_experience' => ['nullable', 'integer', 'min:0', 'max:70'],
            'agent_profile.website' => ['nullable', 'url', 'max:190'],
        ]);
    }

    /** @param array<string, mixed> $data */
    private function syncAgentProfile(User $user, Request $request, array $data): void
    {
        $isAgent = $user->roles()->whereIn('slug', ['agent', 'agency-manager'])->exists();

        if ($request->hasFile('avatar')) {
            $path = $this->uploadImage($request->file('avatar'), 'avatars');
            if ($path) {
                $this->deleteUpload($user->avatar_path);
                $user->update(['avatar_path' => $path]);
            }
        }

        if (! $isAgent) {
            return;
        }

        $profile = AgentProfile::query()->firstOrNew(['user_id' => $user->id]);
        $profile->fill([
            'slug' => $profile->slug ?: $this->agentSlug($user),
            'job_title' => $data['agent_profile']['job_title'] ?? $user->job_title,
            'bio' => $data['agent_profile']['bio'] ?? $profile->bio,
            'whatsapp' => $data['agent_profile']['whatsapp'] ?? $user->phone,
            'years_experience' => $data['agent_profile']['years_experience'] ?? $profile->years_experience,
            'website' => $data['agent_profile']['website'] ?? $profile->website,
            'status' => 'active',
            'verified_at' => $profile->verified_at ?: now(),
        ]);
        $profile->save();
    }

    /** Guaranteed-unique public slug for a new agent profile. */
    private function agentSlug(User $user): string
    {
        $base = $this->uniqueSlug($user->name, AgentProfile::class, 'slug');
        $slug = $base;
        $i = 2;

        while (AgentProfile::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /**
     * Default "المسؤول المتابع" when the form did not choose one: whoever is
     * creating the account. Admin-level accounts may stay at the top level.
     *
     * @param list<int|string> $roleIds
     */
    private function defaultManagerIdFor(array $roleIds): ?int
    {
        $isTopLevel = Role::query()
            ->whereIn('id', $roleIds)
            ->whereIn('slug', ['super-admin', 'admin'])
            ->exists();

        return $isTopLevel ? null : $this->admin()->id;
    }
}
