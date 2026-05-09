<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Operations\Enums\ShiftStatus;
use App\Models\Concerns\BelongsToMunicipio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use BelongsToMunicipio;

    protected $fillable = [
        'municipio_id',
        'vehicle_id',
        'starts_at',
        'ends_at',
        'status',
        'status_legacy',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'status' => ShiftStatus::class,
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'shift_staff')->withTimestamps();
    }

    public function incidentDispatches(): HasMany
    {
        return $this->hasMany(IncidentDispatch::class);
    }

    public function scopeOperationalAvailability(Builder $query): Builder
    {
        return $query->where('ends_at', '>=', now())
            ->where('status', ShiftStatus::Available);
    }
}
