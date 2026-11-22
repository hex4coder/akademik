<?php

namespace App\Filament\Resources;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class OwnTeachingDataScope
{
    /**
     * Batasi query ke penugasan mengajar milik guru yang sedang login.
     */
    public static function apply(Builder $query, string $relationship): Builder
    {
        $teacherId = self::teacherIdForCurrentUser();

        if ($teacherId === false) {
            return $query;
        }

        if ($teacherId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas(
            $relationship,
            fn (Builder $query): Builder => $query->where('teacher_id', $teacherId)
        );
    }

    /**
     * Batasi query yang memiliki kolom teacher_id langsung.
     */
    public static function applyToTeacherId(Builder $query, string $column = 'teacher_id'): Builder
    {
        $teacherId = self::teacherIdForCurrentUser();

        if ($teacherId === false) {
            return $query;
        }

        if ($teacherId === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($column, $teacherId);
    }

    /**
     * false berarti tidak dibatasi, null berarti guru tanpa profil teacher.
     */
    private static function teacherIdForCurrentUser(): int|false|null
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        if ($user->hasAnyRole(['admin', 'staf', 'kepala sekolah'])) {
            return false;
        }

        if (! $user->hasRole('guru')) {
            return false;
        }

        return $user->teacher?->id;
    }
}
