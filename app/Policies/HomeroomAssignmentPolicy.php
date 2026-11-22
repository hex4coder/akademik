<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\HomeroomAssignment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class HomeroomAssignmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:HomeroomAssignment');
    }

    public function view(AuthUser $authUser, HomeroomAssignment $homeroomAssignment): bool
    {
        return $authUser->can('View:HomeroomAssignment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:HomeroomAssignment');
    }

    public function update(AuthUser $authUser, HomeroomAssignment $homeroomAssignment): bool
    {
        return $authUser->can('Update:HomeroomAssignment');
    }

    public function delete(AuthUser $authUser, HomeroomAssignment $homeroomAssignment): bool
    {
        return $authUser->can('Delete:HomeroomAssignment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:HomeroomAssignment');
    }

    public function restore(AuthUser $authUser, HomeroomAssignment $homeroomAssignment): bool
    {
        return $authUser->can('Restore:HomeroomAssignment');
    }

    public function forceDelete(AuthUser $authUser, HomeroomAssignment $homeroomAssignment): bool
    {
        return $authUser->can('ForceDelete:HomeroomAssignment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:HomeroomAssignment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:HomeroomAssignment');
    }

    public function replicate(AuthUser $authUser, HomeroomAssignment $homeroomAssignment): bool
    {
        return $authUser->can('Replicate:HomeroomAssignment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:HomeroomAssignment');
    }
}
