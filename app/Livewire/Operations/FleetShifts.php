<?php

declare(strict_types=1);

namespace App\Livewire\Operations;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** Turnos ativos e estado das viaturas (equivalente a `/api/viaturas` / gestão de turno). */
#[Layout('layouts.app')]
#[Title('Turnos e viaturas')]
final class FleetShifts extends Component
{
    public function mount(): void
    {
        abort_unless(Auth::user()?->hasOperationalAbility('dispatch.view'), 403);
    }

    public function render(): View
    {
        /** @var User $user */
        $user = Auth::user();

        $query = Shift::query()
            ->with(['vehicle', 'municipio'])
            ->where('ends_at', '>=', now()->subDay());

        if (! $user->hasOperationalAbility('*')) {
            $query->where('municipio_id', (int) $user->municipio_id);
        } else {
            $sid = session('operational_municipio_id');
            if ($sid !== null) {
                $query->where('municipio_id', (int) $sid);
            }
        }

        $shifts = $query->orderByDesc('starts_at')->limit(80)->get();

        return view('livewire.operations.fleet-shifts', [
            'shifts' => $shifts,
        ]);
    }
}
