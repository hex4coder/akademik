<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AssignmentScore;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AssignmentScorePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AssignmentScore');
    }

    public function view(AuthUser $authUser, AssignmentScore $assignmentScore): bool
    {
        return $authUser->can('View:AssignmentScore');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AssignmentScore');
    }

    public function update(AuthUser $authUser, AssignmentScore $assignmentScore): bool
    {
        return $authUser->can('Update:AssignmentScore');
    }

    public function delete(AuthUser $authUser, AssignmentScore $assignmentScore): bool
    {
        return $authUser->can('Delete:AssignmentScore');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AssignmentScore');
    }

    public function restore(AuthUser $authUser, AssignmentScore $assignmentScore): bool
    {
        return $authUser->can('Restore:AssignmentScore');
    }

    public function forceDelete(AuthUser $authUser, AssignmentScore $assignmentScore): bool
    {
        return $authUser->can('ForceDelete:AssignmentScore');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AssignmentScore');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AssignmentScore');
    }

    public function replicate(AuthUser $authUser, AssignmentScore $assignmentScore): bool
    {
        return $authUser->can('Replicate:AssignmentScore');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AssignmentScore');
    }
}
