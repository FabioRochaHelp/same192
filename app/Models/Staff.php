<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToMunicipio;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use BelongsToMunicipio, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'municipio_id',
        'name',
        'document_type',
        'document_number',
        'cpf',
        'email',
        'phone',
        'cargo',
    ];

    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'shift_staff')->withTimestamps();
    }
}
