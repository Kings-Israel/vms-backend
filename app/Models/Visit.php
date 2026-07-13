<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Visit extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'visitor_id', 'unit_id', 'host_user_id', 'building_id',
        'visitor_type_id', 'vehicle_id', 'purpose', 'notes', 'status',
        'expected_arrival', 'expected_departure',
        'checked_in_at', 'checked_out_at',
        'checked_in_by', 'checked_out_by',
        'badge_number', 'qr_token', 'is_walk_in', 'escort_required',
    ];

    protected $hidden = ['qr_token'];

    protected $casts = [
        'expected_arrival' => 'datetime',
        'expected_departure' => 'datetime',
        'checked_in_at' => 'datetime',
        'checked_out_at' => 'datetime',
        'is_walk_in' => 'boolean',
        'escort_required' => 'boolean',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function visitorType(): BelongsTo
    {
        return $this->belongsTo(VisitorType::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['status', 'checked_in_at', 'checked_out_at'])->logOnlyDirty();
    }

    public function getDurationAttribute(): ?string
    {
        if ($this->checked_in_at && $this->checked_out_at) {
            $minutes = $this->checked_in_at->diffInMinutes($this->checked_out_at);
            $hours = intdiv($minutes, 60);
            $mins = $minutes % 60;
            return $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";
        }
        return null;
    }
}
