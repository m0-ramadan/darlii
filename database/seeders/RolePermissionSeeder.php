<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Support\PermissionCatalog;
use Illuminate\Database\Seeder;

/**
 * Syncs the permission catalogue and the role matrix (with Arabic labels and
 * hierarchy levels). Safe to re-run: roles/permissions are upserted and system
 * roles keep the permissions declared in PermissionCatalog.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ------------------------------------------------------------ permissions
        $permissions = PermissionCatalog::all();

        foreach ($permissions as $slug => $meta) {
            Permission::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $slug, 'label_ar' => $meta['label'], 'group' => $meta['group']]
            );
        }

        // Keep the old "property-manager" slug alive as an alias of "manager".
        if (! Role::query()->where('slug', 'property-manager')->exists()) {
            Role::query()->where('slug', 'manager')->first()?->replicate()->fill([
                'slug' => 'property-manager',
                'name' => 'Property Manager',
                'label_ar' => 'مسؤول عقارات',
            ])->save();
        }

        foreach (PermissionCatalog::roles() as $slug => $definition) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'label_ar' => $definition['label_ar'],
                    'description' => $definition['description'],
                    'level' => $definition['level'],
                    'is_system' => true,
                ]
            );

            $slugs = $definition['permissions'] === '*'
                ? Permission::query()->pluck('id')->all()
                : Permission::query()->whereIn('slug', $definition['permissions'])->pluck('id')->all();

            $role->permissions()->sync($slugs);
        }

        // The legacy property-manager alias mirrors the manager matrix.
        if ($alias = Role::query()->where('slug', 'property-manager')->first()) {
            $alias->permissions()->sync(
                Permission::query()->whereIn('slug', PermissionCatalog::roles()['manager']['permissions'])->pluck('id')->all()
            );
        }
    }
}
