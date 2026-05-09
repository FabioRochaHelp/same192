<?php

declare(strict_types=1);

namespace App\Domain\Operations\Services;

use App\Models\Incident;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Talão único por ano e município (docs/migracao/regras-negocio).
 */
final class TalaoIssuer
{
    public function next(int $municipioId, CarbonInterface $occurredAt): int
    {
        $year = (int) $occurredAt->format('Y');
        $lockKey = "talao:{$municipioId}:{$year}";

        return (int) Cache::lock($lockKey, 10)->block(5, function () use ($municipioId, $year): int {
            $max = Incident::withoutGlobalScopes()
                ->where('municipio_id', $municipioId)
                ->where('dispatch_year', $year)
                ->max('talao');

            return $max ? ((int) $max) + 1 : 1;
        });
    }

    /** Para migração sem Redis/cache lock (SQLite dev). */
    public function nextWithinTransaction(int $municipioId, CarbonInterface $occurredAt): int
    {
        $year = (int) $occurredAt->format('Y');

        $max = DB::table('incidents')
            ->where('municipio_id', $municipioId)
            ->where('dispatch_year', $year)
            ->whereNull('deleted_at')
            ->max('talao');

        return $max ? ((int) $max) + 1 : 1;
    }
}
