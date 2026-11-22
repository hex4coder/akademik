<?php

namespace App\Filament\Resources;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as AuthUser;

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
     * Cek kepemilikan record melalui relasi yang mengarah ke teacher_id.
     */
    public static function userOwns(AuthUser $authUser, Model $record, string $relationship): bool
    {
        return self::owns($record, $relationship, self::teacherIdFor($authUser));
    }

    /**
     * Cek kepemilikan record melalui relasi untuk pengguna yang sedang login.
     */
    public static function ownsForCurrentUser(Model $record, string $relationship): bool
    {
        return self::owns($record, $relationship, self::teacherIdForCurrentUser());
    }

    /**
     * Cek kepemilikan record yang memiliki kolom teacher_id langsung.
     */
    public static function userOwnsTeacherId(?Authenticatable $authUser, Model $record, string $column = 'teacher_id'): bool
    {
        $teacherId = self::teacherIdFor($authUser);

        if ($teacherId === false) {
            return true;
        }

        if ($teacherId === null) {
            return false;
        }

        return (int) $record->{$column} === $teacherId;
    }

    /**
     * Cek kepemilikan teacher_id langsung untuk pengguna yang sedang login.
     */
    public static function ownsTeacherIdForCurrentUser(Model $record, string $column = 'teacher_id'): bool
    {
        return self::userOwnsTeacherId(auth()->user(), $record, $column);
    }

    private static function owns(Model $record, string $relationship, int|false|null $teacherId): bool
    {
        if ($teacherId === false) {
            return true;
        }

        if ($teacherId === null) {
            return false;
        }

        return $record->newQuery()
            ->whereKey($record->getKey())
            ->whereHas(
                $relationship,
                fn (Builder $query): Builder => $query->where('teacher_id', $teacherId)
            )
            ->exists();
    }

    /**
     * false berarti tidak dibatasi, null berarti guru tanpa profil teacher.
     */
    private static function teacherIdForCurrentUser(): int|false|null
    {
        return self::teacherIdFor(auth()->user());
    }

    private static function teacherIdFor(?Authenticatable $user): int|false|null
    {
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
