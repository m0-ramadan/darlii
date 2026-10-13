<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Shared helpers for every CMS controller: permission checks, scoped queries
 * (admin sees everything, a manager sees their agents' records, an agent sees
 * only their own) and flash helpers.
 */
trait InteractsWithAdminPanel
{
    protected function admin(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }

    protected function authorizePermission(string $permission): void
    {
        abort_unless($this->admin()->hasPermission($permission), 403, 'ليست لديك صلاحية لهذا الإجراء.');
    }

    /**
     * Scope a property query to what the signed-in user is allowed to see.
     */
    protected function scopedPropertyQuery(): Builder
    {
        $query = Property::query()->with(['translations', 'currency', 'category.translations', 'owner', 'manager']);
        $user = $this->admin();

        if ($user->canSeeAllProperties()) {
            return $query;
        }

        if ($user->isManager()) {
            $ids = $user->visibleUserIds();

            return $query->where(function (Builder $q) use ($ids, $user): void {
                $q->whereIn('owner_id', $ids)
                    ->orWhereIn('managed_by', $ids)
                    ->orWhere('managed_by', $user->id);
            });
        }

        return $query->where('owner_id', $user->id);
    }

    /** Users the signed-in account is allowed to assign work to. */
    protected function assignableUsers()
    {
        $user = $this->admin();

        $query = User::query()->with('roles')->where('status', 'active');

        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user): void {
                $q->where('manager_id', $user->id)
                    ->orWhere('id', $user->id);
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Users that may be selected as a "مسؤول" for a new user.
     */
    protected function assignableManagers()
    {
        $query = User::query()->with('roles')->where('status', 'active')
            ->whereHas('roles', fn ($q) => $q->whereIn('slug', ['super-admin', 'admin', 'manager', 'property-manager', 'agency-manager']));

        if (! $this->admin()->isAdmin()) {
            $query->where('id', $this->admin()->id);
        }

        return $query->orderBy('name')->get();
    }

    protected function ok(string $message)
    {
        return back()->with('success', $message);
    }
}
