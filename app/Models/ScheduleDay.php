<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'is_locked',
        'locked_at',
    ];

    protected $casts = [
        'date' => 'date',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];
}

