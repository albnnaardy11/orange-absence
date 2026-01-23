<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Activitylog\Models\Activity;

class ActivityPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(AuthUser $user, Activity $activity): bool
    {
        return $user->hasRole('super_admin');
    }

    public function create(AuthUser $user): bool
    {
        return false;
    }

    public function update(AuthUser $user, Activity $activity): bool
    {
        return false;
    }

    public function delete(AuthUser $user, Activity $activity): bool
    {
        return $user->hasRole('super_admin');
    }
}
