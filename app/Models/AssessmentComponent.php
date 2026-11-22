<?php

namespace App\Models;

use App\Filament\Resources\OwnTeachingDataScope;
use Database\Factories\AssessmentComponentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class AssessmentComponent extends Model
{
    /** @use HasFactory<AssessmentComponentFactory> */
    use HasFactory;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (AssessmentComponent $component): void {
            if ((float) $component->weight <= 0 || (float) $component->weight > 100) {
                throw ValidationException::withMessages([
                    'weight' => 'Bobot harus lebih besar dari 0 dan maksimal 100.',
                ]);
            }

            if (! $component->teaching_assignment_id) {
                return;
            }

            $totalWeight = static::query()
                ->where('teaching_assignment_id', $component->teaching_assignment_id)
                ->where('is_active', true)
                ->when($component->exists, fn ($query) => $query->whereKeyNot($component->getKey()))
                ->sum('weight');

            $nextTotal = $totalWeight + ((bool) $component->is_active ? (float) $component->weight : 0);

            if ($nextTotal > 100) {
                throw ValidationException::withMessages([
                    'weight' => 'Total bobot komponen aktif pada penugasan mengajar ini tidak boleh lebih dari 100%.',
                ]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'weight' => 'float',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function isOwnedByCurrentTeacher(): bool
    {
        return OwnTeachingDataScope::ownsForCurrentUser($this, 'teachingAssignment');
    }
}
