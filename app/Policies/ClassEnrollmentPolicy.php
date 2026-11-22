<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ClassEnrollment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ClassEnrollmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ClassEnrollment');
    }

    public function view(AuthUser $authUser, ClassEnrollment $classEnrollment): bool
    {
        return $authUser->can('View:ClassEnrollment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ClassEnrollment');
    }

    public function update(AuthUser $authUser, ClassEnrollment $classEnrollment): bool
    {
        return $authUser->can('Update:ClassEnrollment');
    }

    public function delete(AuthUser $authUser, ClassEnrollment $classEnrollment): bool
    {
        return $authUser->can('Delete:ClassEnrollment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:ClassEnrollment');
    }

    public function restore(AuthUser $authUser, ClassEnrollment $classEnrollment): bool
    {
        return $authUser->can('Restore:ClassEnrollment');
    }

    public function forceDelete(AuthUser $authUser, ClassEnrollment $classEnrollment): bool
    {
        return $authUser->can('ForceDelete:ClassEnrollment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ClassEnrollment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ClassEnrollment');
    }

    public function replicate(AuthUser $authUser, ClassEnrollment $classEnrollment): bool
    {
        return $authUser->can('Replicate:ClassEnrollment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ClassEnrollment');
    }
}
