<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Visitor extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'first_name', 'last_name', 'national_id', 'passport_number',
        'phone', 'email', 'company', 'photo',
        'id_photo_front', 'id_photo_back', 'visitor_type_id',
        'is_blacklisted', 'blacklist_reason',
    ];

    protected $casts = [
        'is_blacklisted' => 'boolean',
    ];

    public function visitorType(): BelongsTo
    {
        return $this->belongsTo(VisitorType::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['first_name', 'last_name', 'national_id', 'is_blacklisted'])->logOnlyDirty();
    }
}
