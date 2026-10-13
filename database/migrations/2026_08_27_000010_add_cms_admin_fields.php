<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds everything the CMS admin panel needs on top of the original schema:
 * - users: reporting hierarchy (manager_id) + admin profile fields
 * - roles/permissions: Arabic labels, grouping and hierarchy level
 * - properties: assignment tracking (who created / manages a listing)
 * - settings: grouped key/value store for the settings + integrations pages
 * - daily_stats: per-day rollup used by the statistics screens and charts
 *
 * Every step is guarded with hasColumn/hasTable so the migration can be
 * re-run safely if it was interrupted half way through.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addColumns('users', [
            'manager_id' => fn (Blueprint $t) => $t->foreignId('manager_id')->nullable()->after('id')->constrained('users')->nullOnDelete(),
            'job_title' => fn (Blueprint $t) => $t->string('job_title')->nullable()->after('phone'),
            'avatar_path' => fn (Blueprint $t) => $t->string('avatar_path')->nullable()->after('job_title'),
            'notes' => fn (Blueprint $t) => $t->text('notes')->nullable()->after('status'),
            'last_login_at' => fn (Blueprint $t) => $t->timestamp('last_login_at')->nullable()->after('notes'),
            'last_login_ip' => fn (Blueprint $t) => $t->string('last_login_ip', 45)->nullable()->after('last_login_at'),
            'sort_order' => fn (Blueprint $t) => $t->unsignedInteger('sort_order')->default(0)->after('last_login_ip'),
        ]);

        $this->addIndex('users', 'users_status_manager_id_index', ['status', 'manager_id']);

        $this->addColumns('roles', [
            'label_ar' => fn (Blueprint $t) => $t->string('label_ar')->nullable()->after('name'),
            'description' => fn (Blueprint $t) => $t->string('description')->nullable()->after('label_ar'),
            'level' => fn (Blueprint $t) => $t->unsignedSmallInteger('level')->default(10)->after('description'),
            'is_system' => fn (Blueprint $t) => $t->boolean('is_system')->default(false)->after('level'),
        ]);

        $this->addColumns('permissions', [
            'group' => fn (Blueprint $t) => $t->string('group')->default('general')->after('name'),
            'label_ar' => fn (Blueprint $t) => $t->string('label_ar')->nullable()->after('group'),
            'sort_order' => fn (Blueprint $t) => $t->unsignedSmallInteger('sort_order')->default(0)->after('label_ar'),
        ]);

        $this->addIndex('permissions', 'permissions_group_index', ['group']);

        $this->addColumns('properties', [
            'created_by' => fn (Blueprint $t) => $t->foreignId('created_by')->nullable()->after('owner_id')->constrained('users')->nullOnDelete(),
            'managed_by' => fn (Blueprint $t) => $t->foreignId('managed_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete(),
            'admin_note' => fn (Blueprint $t) => $t->string('admin_note')->nullable()->after('status'),
            'last_assigned_at' => fn (Blueprint $t) => $t->timestamp('last_assigned_at')->nullable()->after('admin_note'),
            'assignment_status' => fn (Blueprint $t) => $t->string('assignment_status', 30)->default('unassigned')->after('last_assigned_at'),
        ]);

        $this->addIndex('properties', 'properties_assignment_status_index', ['assignment_status']);

        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table): void {
                $table->id();
                $table->string('group', 60)->default('general')->index();
                $table->string('key', 120)->unique();
                $table->longText('value')->nullable();
                $table->string('type', 30)->default('text');
                $table->string('label_ar')->nullable();
                $table->text('help_ar')->nullable();
                $table->boolean('is_public')->default(false);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table('settings', function (Blueprint $table): void {
                if (! Schema::hasColumn('settings', 'label_ar')) {
                    $table->string('label_ar')->nullable();
                }
                if (! Schema::hasColumn('settings', 'help_ar')) {
                    $table->text('help_ar')->nullable();
                }
                if (! Schema::hasColumn('settings', 'sort_order')) {
                    $table->unsignedSmallInteger('sort_order')->default(0);
                }
            });
        }

        if (! Schema::hasTable('daily_stats')) {
            Schema::create('daily_stats', function (Blueprint $table): void {
                $table->id();
                $table->date('stat_date')->index();
                $table->string('metric', 60)->index();
                $table->unsignedBigInteger('value')->default(0);
                $table->timestamps();
                $table->unique(['stat_date', 'metric']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_stats');
        Schema::dropIfExists('settings');

        $this->dropColumns('properties', ['admin_note', 'last_assigned_at', 'assignment_status']);
        $this->dropForeignIfExists('properties', 'created_by');
        $this->dropForeignIfExists('properties', 'managed_by');
        $this->dropColumns('properties', ['created_by', 'managed_by']);

        $this->dropColumns('permissions', ['group', 'label_ar', 'sort_order']);
        $this->dropColumns('roles', ['label_ar', 'description', 'level', 'is_system']);

        $this->dropColumns('users', ['job_title', 'avatar_path', 'notes', 'last_login_at', 'last_login_ip', 'sort_order']);
        $this->dropForeignIfExists('users', 'manager_id');
        $this->dropColumns('users', ['manager_id']);
    }

    /** @param array<string, callable> $columns */
    private function addColumns(string $table, array $columns): void
    {
        foreach ($columns as $name => $definition) {
            if (Schema::hasColumn($table, $name)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($definition): void {
                $definition($blueprint);
            });
        }
    }

    /** @param list<string> $columns */
    private function dropColumns(string $table, array $columns): void
    {
        $existing = array_values(array_filter($columns, fn (string $c) => Schema::hasColumn($table, $c)));

        if ($existing === []) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($existing): void {
            $blueprint->dropColumn($existing);
        });
    }

    /** @param list<string> $columns */
    private function addIndex(string $table, string $name, array $columns): void
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($name, $columns): void {
                $blueprint->index($columns, $name);
            });
        } catch (\Throwable $e) {
            // Index already present.
        }
    }

    private function dropForeignIfExists(string $table, string $column): void
    {
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column): void {
                $blueprint->dropForeign([$column]);
            });
        } catch (\Throwable $e) {
            // No foreign key to drop.
        }
    }
};
