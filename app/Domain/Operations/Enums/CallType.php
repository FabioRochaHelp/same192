<?php

declare(strict_types=1);

namespace App\Domain\Operations\Enums;

/**
 * Classificação da chamada no formulário/triagem.
 *
 * @see docs/migracao/regras-negocio.md
 */
enum CallType: string
{
    case NotCompleted = 'C';
    case Administrative = 'A';
    case Hoax = 'T';
    case Normal = 'N';
    case Urgent = 'U';

    public function createsOperationalIncident(): bool
    {
        return $this === self::Normal || $this === self::Urgent;
    }

    public function label(): string
    {
        return match ($this) {
            self::NotCompleted => __('Chamada incompleta'),
            self::Administrative => __('Administrativa'),
            self::Hoax => __('Trote'),
            self::Normal => __('Normal'),
            self::Urgent => __('Urgente'),
        };
    }

    /** Ordem dos botões no formulário: C, T, A, N, U (@see docs/migracao/regras-negocio.md). */
    public static function orderedForIncidentForm(): array
    {
        return [
            self::NotCompleted,
            self::Hoax,
            self::Administrative,
            self::Normal,
            self::Urgent,
        ];
    }
}
