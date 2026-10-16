<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CoreSeeder::class,
            RolePermissionSeeder::class,
            SettingsSeeder::class,
            BeniSuefLocationSeeder::class,
            RecoveredCatalogSeeder::class,
            HomepageContentSeeder::class,
            AdminUsersSeeder::class,
            BeniSuefAgenciesSeeder::class,
            ContentPagesSeeder::class,
            CornicheYasmineApartmentSeeder::class,
        ]);
    }
}
