<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Property $property): bool
    {
        return $property->status?->value === 'published'
            || $property->status?->value === 'sold'
            || ($user && ($property->owner_id === $user->id || $user->hasPermission('property.update-any')));
    }

    public function create(User $user): bool
    {
        return true; // Any authenticated user / client can submit a property for review
    }

    public function update(User $user, Property $property): bool
    {
        return $user->hasPermission('property.update-any')
            || ($property->owner_id === $user->id && ($user->hasPermission('property.update-own') || ! $user->hasRole('admin')));
    }

    public function approve(User $user): bool
    {
        return $user->hasPermission('property.approve');
    }
}
