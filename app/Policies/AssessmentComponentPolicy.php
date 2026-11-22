<?php

declare(strict_types=1);

namespace App\Policies;

use App\Filament\Resources\OwnTeachingDataScope;
use App\Models\AssessmentComponent;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AssessmentComponentPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AssessmentComponent');
    }

    public function view(AuthUser $authUser, AssessmentComponent $assessmentComponent): bool
    {
        return $authUser->can('View:AssessmentComponent')
            && OwnTeachingDataScope::userOwns($authUser, $assessmentComponent, 'teachingAssignment');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AssessmentComponent');
    }

    public function update(AuthUser $authUser, AssessmentComponent $assessmentComponent): bool
    {
        return $authUser->can('Update:AssessmentComponent')
            && OwnTeachingDataScope::userOwns($authUser, $assessmentComponent, 'teachingAssignment');
    }

    public function delete(AuthUser $authUser, AssessmentComponent $assessmentComponent): bool
    {
        return $authUser->can('Delete:AssessmentComponent')
            && OwnTeachingDataScope::userOwns($authUser, $assessmentComponent, 'teachingAssignment');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AssessmentComponent');
    }

    public function restore(AuthUser $authUser, AssessmentComponent $assessmentComponent): bool
    {
        return $authUser->can('Restore:AssessmentComponent')
            && OwnTeachingDataScope::userOwns($authUser, $assessmentComponent, 'teachingAssignment');
    }

    public function forceDelete(AuthUser $authUser, AssessmentComponent $assessmentComponent): bool
    {
        return $authUser->can('ForceDelete:AssessmentComponent')
            && OwnTeachingDataScope::userOwns($authUser, $assessmentComponent, 'teachingAssignment');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AssessmentComponent');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AssessmentComponent');
    }

    public function replicate(AuthUser $authUser, AssessmentComponent $assessmentComponent): bool
    {
        return $authUser->can('Replicate:AssessmentComponent')
            && OwnTeachingDataScope::userOwns($authUser, $assessmentComponent, 'teachingAssignment');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AssessmentComponent');
    }
}
