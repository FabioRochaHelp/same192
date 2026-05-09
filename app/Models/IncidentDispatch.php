<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Operations\Enums\DispatchStage;
use App\Models\Concerns\BelongsToMunicipio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncidentDispatch extends Model
{
    use BelongsToMunicipio, SoftDeletes;

    protected $fillable = [
        'municipio_id',
        'incident_id',
        'shift_id',
        'stage',
        'stage_position',
    ];

    protected static function booted(): void
    {
        static::saving(function (IncidentDispatch $dispatch): void {
            if ($dispatch->stage instanceof DispatchStage) {
                $dispatch->stage_position = $dispatch->stage->index() + 1;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'stage' => DispatchStage::class,
        ];
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
