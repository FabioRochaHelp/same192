<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Local de ferimento — cadastro global (uso futuro em vínculos clínicos). */
class InjurySite extends Model
{
    use SoftDeletes;

    protected $table = 'injury_sites';

    protected $fillable = [
        'name',
    ];
}
