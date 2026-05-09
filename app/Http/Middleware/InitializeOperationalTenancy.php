<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Operations\CurrentMunicipio;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contexto operacional por `municipio_id` (documentação: não SaaS isolado).
 *
 * Central: sessão `operational_municipio_id` ou header `X-Operational-Municipio`.
 * Demais usuários: `users.municipio_id`.
 */
final class InitializeOperationalTenancy
{
    public function handle(Request $request, Closure $next): Response
    {
        CurrentMunicipio::set(null);

        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        if ($user->isOperationalCentral()) {
            $raw = $request->header('X-Operational-Municipio') ?? session('operational_municipio_id');
            if ($raw !== null && $raw !== '') {
                CurrentMunicipio::set((int) $raw);
            }

            return $next($request);
        }

        if ($user->municipio_id !== null) {
            CurrentMunicipio::set((int) $user->municipio_id);
        }

        return $next($request);
    }
}
