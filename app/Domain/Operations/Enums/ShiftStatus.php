<?php

declare(strict_types=1);

namespace App\Domain\Operations\Enums;

/**
 * Disponibilidade operacional do turno (legado 1 disponível, 2 empenhado).
 *
 * @see docs/migracao/regras-negocio.md
 */
enum ShiftStatus: string
{
    case Available = 'available';
    case Assigned = 'assigned';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponível',
            self::Assigned => 'Empenhado',
        };
    }
}
