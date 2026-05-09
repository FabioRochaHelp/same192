<?php

declare(strict_types=1);

use App\Models\Incident;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('operations.dispatch', function ($user) {
    return $user !== null && $user->hasOperationalAbility('dispatch.view');
});

Broadcast::channel('operations.municipio.{municipioId}', function ($user, string $municipioId) {
    return $user !== null && $user->canAccessOperationalMunicipio((int) $municipioId);
});

Broadcast::channel('incidents.{incidentId}', function ($user, string $incidentId) {
    if ($user === null || ! $user->hasOperationalAbility('dispatch.view')) {
        return false;
    }

    $incident = Incident::withoutGlobalScopes()->find($incidentId);

    return $incident !== null && $user->canAccessOperationalMunicipio((int) $incident->municipio_id);
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
