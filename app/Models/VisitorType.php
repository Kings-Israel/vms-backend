<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VisitorType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'color', 'icon',
        'requires_escort', 'is_active',
    ];

    protected $casts = [
        'requires_escort' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function visitors(): HasMany
    {
        return $this->hasMany(Visitor::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
