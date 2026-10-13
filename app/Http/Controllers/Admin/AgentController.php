<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\InteractsWithAdminPanel;
use App\Http\Controllers\Admin\Concerns\UploadsImages;
use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\AgentProfile;
use App\Models\Role;
use App\Models\User;
use App\Services\Admin\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    use InteractsWithAdminPanel;
    use UploadsImages;

    public function index(Request $request)
    {
        $this->authorizePermission('users.view');

        $query = AgentProfile::query()
            ->with(['user', 'agency', 'translations'])
            ->withCount([
                'translations',
            ]);

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->whereHas('translations', fn ($t) => $t->where('display_name', 'like', "%{$term}%"))
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%"))
                    ->orWhereHas('agency', fn ($a) => $a->where('name', 'like', "%{$term}%"))
                    ->orWhere('job_title', 'like', "%{$term}%")
                    ->orWhere('license_number', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('verified')) {
            $verified = $request->input('verified');
            if ($verified === '1') {
                $query->whereNotNull('verified_at');
            } elseif ($verified === '0') {
                $query->whereNull('verified_at');
            }
        }

        if ($request->filled('agency_id')) {
            $query->where('agency_id', (int) $request->input('agency_id'));
        }

        if ($request->filled('agent_type') && AgentProfile::hasAgentTypeColumn()) {
            $query->where('agent_type', $request->input('agent_type'));
        }

        $agents = $query->latest()
            ->paginate((int) setting('general.items_per_page', 15))
            ->withQueryString();

        $stats = [
            'total' => AgentProfile::count(),
            'active' => AgentProfile::where('status', 'active')->count(),
            'verified' => AgentProfile::whereNotNull('verified_at')->count(),
            'agencies' => Agency::count(),
        ];

        return view('admin.agents.index', [
            'agents' => $agents,
            'stats' => $stats,
            'agencies' => Agency::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $this->authorizePermission('users.create');

        return view('admin.agents.form', [
            'agent' => new AgentProfile([
                'status' => 'active',
                'years_experience' => 5,
            ]),
            'agencies' => Agency::orderBy('name')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission('users.create');

        $data = $request->validate([
            'display_name_ar' => ['required', 'string', 'max:255'],
            'display_name_en' => ['nullable', 'string', 'max:255'],
            'agency_id' => ['nullable', 'exists:agencies,id'],
            'new_agency_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'website' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'agent_type' => ['nullable', 'string', Rule::in(['individual', 'office', 'broker'])],
            'contract_terms' => ['nullable', 'string', 'max:10000'],
            'is_verified' => ['nullable', 'boolean'],
            'bio_ar' => ['nullable', 'string', 'max:5000'],
            'bio_en' => ['nullable', 'string', 'max:5000'],
            'avatar' => ['nullable', 'image', 'max:4096'],
        ]);

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $this->uploadImage($request->file('avatar'), 'avatars');
        }

        DB::transaction(function () use ($data, $avatarPath, $request): AgentProfile {
            // 1. Resolve or create Agency
            $agencyId = $data['agency_id'] ?? null;
            if (!empty($data['new_agency_name'])) {
                $agency = Agency::create([
                    'name' => $data['new_agency_name'],
                    'slug' => Str::slug($data['new_agency_name']) . '-' . Str::random(4),
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'status' => 'active',
                    'verified_at' => $request->boolean('is_verified') ? now() : null,
                ]);
                $agencyId = $agency->id;
            }

            // 2. Create User account with role 'agent'
            $agentRole = Role::firstOrCreate(['slug' => 'agent'], ['name' => 'سمسار', 'level' => 30]);

            $user = User::create([
                'name' => $data['display_name_ar'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make(Str::random(12)),
                'job_title' => $data['job_title'] ?? null,
                'status' => $data['status'],
                'avatar_path' => $avatarPath,
                'preferred_locale' => 'ar',
                'email_verified_at' => now(),
            ]);

            $user->roles()->syncWithoutDetaching([$agentRole->id]);

            if ($agencyId) {
                Agency::find($agencyId)?->users()->syncWithoutDetaching([$user->id => ['role' => 'agent']]);
            }

            // 3. Create AgentProfile
            $enName = $data['display_name_en'] ?? null;
            $baseSlug = Str::slug($enName ?: $data['display_name_ar']);
            if (empty($baseSlug)) {
                $baseSlug = 'agent-' . Str::random(6);
            }
            $slug = $baseSlug;
            $counter = 1;
            while (AgentProfile::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $profile = AgentProfile::create([
                'user_id' => $user->id,
                'agency_id' => $agencyId,
                'slug' => $slug,
                'license_number' => $data['license_number'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'bio' => $data['bio_ar'] ?? null,
                'avatar_path' => $avatarPath,
                'whatsapp' => $data['whatsapp'] ?? null,
                'website' => $data['website'] ?? null,
                'years_experience' => $data['years_experience'] ?? 0,
                'status' => $data['status'],
                'agent_type' => $data['agent_type'] ?? 'individual',
                'contract_terms' => $data['contract_terms'] ?? null,
                'verified_at' => $request->boolean('is_verified') ? now() : null,
            ]);

            // Save meta if columns not in DB
            $profile->agent_type = $data['agent_type'] ?? 'individual';
            $profile->contract_terms = $data['contract_terms'] ?? null;

            // 4. Create Translations
            $profile->translations()->create([
                'locale' => 'ar',
                'display_name' => $data['display_name_ar'],
                'bio' => $data['bio_ar'] ?? null,
                'seo_title' => $data['display_name_ar'] . ' - وكيل عقاري معتمد',
                'seo_description' => Str::limit($data['bio_ar'] ?? $data['display_name_ar'], 160),
            ]);

            if (!empty($data['display_name_en'])) {
                $profile->translations()->create([
                    'locale' => 'en',
                    'display_name' => $data['display_name_en'],
                    'bio' => $data['bio_en'] ?? null,
                    'seo_title' => $data['display_name_en'] . ' - Real Estate Agent',
                    'seo_description' => Str::limit($data['bio_en'] ?? $data['display_name_en'], 160),
                ]);
            }

            AuditLogger::log('agent.created', $profile, [], [
                'name' => $data['display_name_ar'],
                'email' => $data['email'],
                'slug' => $slug,
            ]);

            return $profile;
        });

        return redirect()
            ->to(admin_route('admin.agents.index'))
            ->with('status', 'تمت إضافة الوكيل / الشركة العقارية بنجاح.');
    }

    public function edit(AgentProfile $agent)
    {
        $this->authorizePermission('users.update');

        $agent->load(['user', 'agency', 'translations']);

        return view('admin.agents.form', [
            'agent' => $agent,
            'agencies' => Agency::orderBy('name')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, AgentProfile $agent): RedirectResponse
    {
        $this->authorizePermission('users.update');

        $data = $request->validate([
            'display_name_ar' => ['required', 'string', 'max:255'],
            'display_name_en' => ['nullable', 'string', 'max:255'],
            'agency_id' => ['nullable', 'exists:agencies,id'],
            'new_agency_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($agent->user_id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:60'],
            'website' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'agent_type' => ['nullable', 'string', Rule::in(['individual', 'office', 'broker'])],
            'contract_terms' => ['nullable', 'string', 'max:10000'],
            'is_verified' => ['nullable', 'boolean'],
            'bio_ar' => ['nullable', 'string', 'max:5000'],
            'bio_en' => ['nullable', 'string', 'max:5000'],
            'avatar' => ['nullable', 'image', 'max:4096'],
        ]);

        $oldData = $agent->only(['job_title', 'status', 'verified_at', 'agency_id']);

        DB::transaction(function () use ($agent, $data, $request, $oldData) {
            $avatarPath = $agent->avatar_path;
            if ($request->hasFile('avatar')) {
                $avatarPath = $this->uploadImage($request->file('avatar'), 'avatars');
            }

            // 1. Resolve Agency
            $agencyId = $data['agency_id'] ?? null;
            if (!empty($data['new_agency_name'])) {
                $agency = Agency::create([
                    'name' => $data['new_agency_name'],
                    'slug' => Str::slug($data['new_agency_name']) . '-' . Str::random(4),
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'status' => 'active',
                    'verified_at' => $request->boolean('is_verified') ? now() : null,
                ]);
                $agencyId = $agency->id;
            }

            // 2. Update User
            if ($agent->user) {
                $agent->user->update([
                    'name' => $data['display_name_ar'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'job_title' => $data['job_title'] ?? null,
                    'status' => $data['status'],
                    'avatar_path' => $avatarPath ?: $agent->user->avatar_path,
                ]);
            }

            // 3. Update AgentProfile
            $agent->update([
                'agency_id' => $agencyId,
                'license_number' => $data['license_number'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'bio' => $data['bio_ar'] ?? null,
                'avatar_path' => $avatarPath,
                'whatsapp' => $data['whatsapp'] ?? null,
                'website' => $data['website'] ?? null,
                'years_experience' => $data['years_experience'] ?? 0,
                'status' => $data['status'],
                'agent_type' => $data['agent_type'] ?? 'individual',
                'contract_terms' => $data['contract_terms'] ?? null,
                'verified_at' => $request->boolean('is_verified') ? ($agent->verified_at ?: now()) : null,
            ]);

            $agent->agent_type = $data['agent_type'] ?? 'individual';
            $agent->contract_terms = $data['contract_terms'] ?? null;

            // 4. Update Translations
            $agent->translations()->updateOrCreate(
                ['locale' => 'ar'],
                [
                    'display_name' => $data['display_name_ar'],
                    'bio' => $data['bio_ar'] ?? null,
                    'seo_title' => $data['display_name_ar'] . ' - وكيل عقاري معتمد',
                    'seo_description' => Str::limit($data['bio_ar'] ?? $data['display_name_ar'], 160),
                ]
            );

            if (!empty($data['display_name_en'])) {
                $agent->translations()->updateOrCreate(
                    ['locale' => 'en'],
                    [
                        'display_name' => $data['display_name_en'],
                        'bio' => $data['bio_en'] ?? null,
                        'seo_title' => $data['display_name_en'] . ' - Real Estate Agent',
                        'seo_description' => Str::limit($data['bio_en'] ?? $data['display_name_en'], 160),
                    ]
                );
            }

            AuditLogger::log('agent.updated', $agent, $oldData, $agent->fresh()->only(array_keys($oldData)));
        });

        return redirect()
            ->to(admin_route('admin.agents.index'))
            ->with('status', 'تم تحديث بيانات الوكيل العقاري بنجاح.');
    }

    public function toggleVerify(AgentProfile $agent): RedirectResponse
    {
        $this->authorizePermission('users.update');

        $isVerified = $agent->isVerified();
        $agent->update([
            'verified_at' => $isVerified ? null : now(),
        ]);

        if ($agent->agency) {
            $agent->agency->update([
                'verified_at' => $isVerified ? null : now(),
            ]);
        }

        AuditLogger::log('agent.verified', $agent, [], ['is_verified' => ! $isVerified]);

        $message = ! $isVerified ? 'تم اعتماد وتوثيق الوكيل بنجاح.' : 'تم إلغاء اعتماد الوكيل.';

        return back()->with('status', $message);
    }

    public function toggleStatus(AgentProfile $agent): RedirectResponse
    {
        $this->authorizePermission('users.update');

        $newStatus = $agent->status === 'active' ? 'inactive' : 'active';
        $agent->update(['status' => $newStatus]);

        if ($agent->user) {
            $agent->user->update(['status' => $newStatus]);
        }

        AuditLogger::log('agent.status', $agent, [], ['status' => $newStatus]);

        $message = $newStatus === 'active' ? 'تم تنشيط حساب الوكيل بنجاح.' : 'تم تعطيل حساب الوكيل.';

        return back()->with('status', $message);
    }

    public function destroy(AgentProfile $agent): RedirectResponse
    {
        $this->authorizePermission('users.delete');

        $name = $agent->display_name;

        DB::transaction(function () use ($agent) {
            $agent->translations()->delete();
            $agent->delete();
        });

        AuditLogger::log('agent.deleted', null, ['name' => $name, 'slug' => $agent->slug], []);

        return redirect()
            ->to(admin_route('admin.agents.index'))
            ->with('status', "تم حذف الوكيل ({$name}) بنجاح.");
    }
}

