<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AttendanceStatus;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AttendanceStatusPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AttendanceStatus');
    }

    public function view(AuthUser $authUser, AttendanceStatus $attendanceStatus): bool
    {
        return $authUser->can('View:AttendanceStatus');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AttendanceStatus');
    }

    public function update(AuthUser $authUser, AttendanceStatus $attendanceStatus): bool
    {
        return $authUser->can('Update:AttendanceStatus');
    }

    public function delete(AuthUser $authUser, AttendanceStatus $attendanceStatus): bool
    {
        return $authUser->can('Delete:AttendanceStatus');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AttendanceStatus');
    }

    public function restore(AuthUser $authUser, AttendanceStatus $attendanceStatus): bool
    {
        return $authUser->can('Restore:AttendanceStatus');
    }

    public function forceDelete(AuthUser $authUser, AttendanceStatus $attendanceStatus): bool
    {
        return $authUser->can('ForceDelete:AttendanceStatus');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AttendanceStatus');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AttendanceStatus');
    }

    public function replicate(AuthUser $authUser, AttendanceStatus $attendanceStatus): bool
    {
        return $authUser->can('Replicate:AttendanceStatus');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AttendanceStatus');
    }
}
