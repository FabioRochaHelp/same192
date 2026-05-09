<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToMunicipio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Victim extends Model
{
    use BelongsToMunicipio, SoftDeletes;

    protected $fillable = [
        'municipio_id',
        'incident_id',
        'name',
        'sex',
        'rg',
        'age',
        'ssp',
        'situacao',
        'status',
        'hospital',
        'transporte',
        'unidade_saude',
        'medico_us',
        'crm_medico_us',
        'dados_complementares',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }
}
