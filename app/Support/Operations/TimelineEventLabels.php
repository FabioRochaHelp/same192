<?php

declare(strict_types=1);

namespace App\Support\Operations;

/** Rótulos curtos para chaves da timeline auditável. */
final class TimelineEventLabels
{
    public static function for(string $key): string
    {
        return match ($key) {
            'incident_created' => 'Ocorrência registrada',
            'unit_dispatched' => 'Equipe empenhada',
            'dispatch_stage_advanced' => 'Etapa do deslocamento',
            'unit_released' => 'Retorno à base / encerramento',
            default => str_replace('_', ' ', $key),
        };
    }
}
