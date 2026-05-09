<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Natureza operacional — cadastro global (parâmetro da ocorrência). */
class Nature extends Model
{
    protected $fillable = [
        'nature_type_id',
        'name',
    ];

    public function natureType(): BelongsTo
    {
        return $this->belongsTo(NatureType::class);
    }
}
