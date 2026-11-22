<?php

declare(strict_types=1);

namespace App\Policies;

use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\Assignment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AssignmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Assignment');
    }

    public function view(AuthUser $authUser, Assignment $assignment): bool
    {
        return $authUser->can('View:Assignment') && OwnTeachingDataScope::userOwns($authUser, $assignment, 'teachingAssignment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Assignment');
    }

    public function update(AuthUser $authUser, Assignment $assignment): bool
    {
        return $authUser->can('Update:Assignment') && OwnTeachingDataScope::userOwns($authUser, $assignment, 'teachingAssignment');
    }

    public function delete(AuthUser $authUser, Assignment $assignment): bool
    {
        return $authUser->can('Delete:Assignment') && OwnTeachingDataScope::userOwns($authUser, $assignment, 'teachingAssignment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Assignment');
    }

    public function restore(AuthUser $authUser, Assignment $assignment): bool
    {
        return $authUser->can('Restore:Assignment') && OwnTeachingDataScope::userOwns($authUser, $assignment, 'teachingAssignment');
    }

    public function forceDelete(AuthUser $authUser, Assignment $assignment): bool
    {
        return $authUser->can('ForceDelete:Assignment') && OwnTeachingDataScope::userOwns($authUser, $assignment, 'teachingAssignment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Assignment');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Assignment');
    }

    public function replicate(AuthUser $authUser, Assignment $assignment): bool
    {
        return $authUser->can('Replicate:Assignment') && OwnTeachingDataScope::userOwns($authUser, $assignment, 'teachingAssignment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Assignment');
    }
}
