<?php

namespace App\Models;

use Database\Factories\AttendanceStatusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceStatus extends Model
{
    /** @use HasFactory<AttendanceStatusFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
