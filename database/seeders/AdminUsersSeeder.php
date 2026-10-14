<?php

namespace Database\Seeders;

use App\Models\AgentProfile;
use App\Models\Property;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Creates the CMS team with a real reporting hierarchy:
 *
 *   admin@darlii.eg        (أدمن - كل الصلاحيات)
 *     ├── manager@darlii.eg        (مسؤول)
 *     │     ├── agent1@darlii.eg   (سمسار)
 *     │     └── agent2@darlii.eg   (سمسار)
 *     └── agent3@darlii.eg         (سمسار تابع للأدمن مباشرة)
 *
 * super@darlii.eg is the technical system owner (سوبر أدمن).
 */
class AdminUsersSeeder extends Seeder
{
    public const PASSWORD = 'Admin@12345';

    public function run(): void
    {
        $ids = fn(string $slug): int => (int) Role::query()->where('slug', $slug)->value('id');

        $super = $this->makeUser([
            'name' => 'مدير النظام',
            'email' => 'super@darlii.eg',
            'phone' => '+201000000001',
            'job_title' => 'مالك النظام',
            'status' => 'active',
        ], ['super-admin']);

        $admin = $this->makeUser([
            'name' => 'محمد الأدمن',
            'email' => 'admin@darlii.eg',
            'phone' => '+201000000002',
            'job_title' => 'المدير العام',
            'status' => 'active',
            'manager_id' => null,
        ], ['admin']);

        // Legacy test suite alias
        $this->makeUser([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'phone' => '+201000000099',
            'job_title' => 'System Admin',
            'status' => 'active',
            'manager_id' => null,
        ], ['admin']);

        $manager = $this->makeUser([
            'name' => 'أحمد المسؤول',
            'email' => 'manager@darlii.eg',
            'phone' => '+201000000003',
            'job_title' => 'مسؤول مبيعات',
            'status' => 'active',
            'manager_id' => $admin->id,
        ], ['manager']);

        $agentOne = $this->makeUser([
            'name' => 'سارة السمسار',
            'email' => 'agent1@darlii.eg',
            'phone' => '+201000000004',
            'job_title' => 'سمسار عقارات',
            'status' => 'active',
            'manager_id' => $manager->id,
        ], ['agent']);

        $agentTwo = $this->makeUser([
            'name' => 'خالد السمسار',
            'email' => 'agent2@darlii.eg',
            'phone' => '+201000000005',
            'job_title' => 'سمسار عقارات',
            'status' => 'active',
            'manager_id' => $manager->id,
        ], ['agent']);

        // Agent that reports straight to the admin (allowed by the hierarchy).
        $agentThree = $this->makeUser([
            'name' => 'منى السمسار',
            'email' => 'agent3@darlii.eg',
            'phone' => '+201000000006',
            'job_title' => 'سمسار عقارات',
            'status' => 'active',
            'manager_id' => $admin->id,
        ], ['agent']);

        foreach ([$agentOne, $agentTwo, $agentThree] as $index => $agent) {
            AgentProfile::query()->updateOrCreate(
                ['user_id' => $agent->id],
                [
                    'slug' => Str::slug($agent->name) ?: 'agent-' . $agent->id,
                    'job_title' => 'سمسار عقارات',
                    'bio' => 'سمسار معتمد لدى منصة دارلي.',
                    'whatsapp' => $agent->phone,
                    'years_experience' => 3 + $index,
                    'status' => 'active',
                    'verified_at' => now(),
                ]
            );
        }

        // Attach the legacy/recovered catalogue to the admin so the reporting
        // tree owns every record instead of leaving them orphaned.
        Property::query()
            ->where(fn($q) => $q->whereNull('owner_id')->orWhere('owner_id', '!=', $admin->id))
            ->whereNull('managed_by')
            ->update(['created_by' => $admin->id, 'managed_by' => $admin->id, 'assignment_status' => 'assigned', 'last_assigned_at' => now()]);

        // Spread a few properties over the agents so their dashboards are not empty.
        $agentProperties = Property::query()->where('managed_by', $admin->id)->inRandomOrder()->limit(6)->pluck('id');
        foreach ($agentProperties->values() as $index => $propertyId) {
            $agent = [$agentOne, $agentTwo, $agentThree][$index % 3];
            Property::query()->whereKey($propertyId)->update([
                'managed_by' => $agent->id,
                'owner_id' => $agent->id,
                'assignment_status' => 'assigned',
                'last_assigned_at' => now(),
            ]);
        }

        $this->command?->info('CMS users ready. Password for all seeded accounts: ' . self::PASSWORD);
    }

    /** @param list<string> $roles */
    private function makeUser(array $attributes, array $roles): User
    {
        $user = User::withTrashed()->updateOrCreate(
            ['email' => $attributes['email']],
            array_merge([
                'password' => self::PASSWORD,
                'preferred_locale' => 'ar',
                'preferred_theme' => 'light',
                'email_verified_at' => now(),
                'sort_order' => 0,
            ], $attributes)
        );

        if ($user->trashed()) {
            $user->restore();
        }

        $roleIds = Role::query()->whereIn('slug', $roles)->pluck('id')->all();
        $user->roles()->syncWithoutDetaching($roleIds);

        return $user;
    }
}
