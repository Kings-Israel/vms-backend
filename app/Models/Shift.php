<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Shift extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'building_id', 'user_id', 'relieved_by',
        'starts_at', 'ends_at', 'actual_start', 'actual_end',
        'status', 'notes',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function relief(): BelongsTo
    {
        return $this->belongsTo(User::class, 'relieved_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'actual_start', 'actual_end'])->logOnlyDirty();
    }
}
