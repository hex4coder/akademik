<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SchoolProfile;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SchoolProfilePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SchoolProfile');
    }

    public function view(AuthUser $authUser, SchoolProfile $schoolProfile): bool
    {
        return $authUser->can('View:SchoolProfile');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SchoolProfile') && ! SchoolProfile::exists();
    }

    public function update(AuthUser $authUser, SchoolProfile $schoolProfile): bool
    {
        return $authUser->can('Update:SchoolProfile');
    }

    public function delete(AuthUser $authUser, SchoolProfile $schoolProfile): bool
    {
        return $authUser->can('Delete:SchoolProfile');
    }

    public function restore(AuthUser $authUser, SchoolProfile $schoolProfile): bool
    {
        return $authUser->can('Restore:SchoolProfile');
    }

    public function forceDelete(AuthUser $authUser, SchoolProfile $schoolProfile): bool
    {
        return $authUser->can('ForceDelete:SchoolProfile');
    }
}
