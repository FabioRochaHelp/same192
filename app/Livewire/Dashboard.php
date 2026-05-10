<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Domain\Operations\Enums\CallType;
use App\Models\Incident;
use App\Support\Operations\OperationalIncidentVisibility;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
final class Dashboard extends Component
{
    /** Incrementado por Echo/Reverb para forçar novo render com contagens atualizadas. */
    public int $callStatsBroadcastTick = 0;

    #[On('dashboard-call-stats-refresh')]
    public function refreshCallStatsFromBroadcast(): void
    {
        $this->callStatsBroadcastTick++;
    }

    public function render(): View
    {
        $showCallStats = Gate::allows('viewAny', Incident::class);
        $callTypeStats = [];

        if ($showCallStats) {
            $base = Incident::query();
            OperationalIncidentVisibility::constrainListing($base, Auth::user());

            $start = now()->startOfDay();
            $end = now()->endOfDay();

            foreach (CallType::orderedForDashboard() as $type) {
                $callTypeStats[] = [
                    'code' => $type->value,
                    'label' => $type->label(),
                    'count' => (clone $base)
                        ->where('patient_call_type', $type->value)
                        ->whereBetween('occurred_at', [$start, $end])
                        ->count(),
                ];
            }
        }

        return view('livewire.dashboard', [
            'showCallStats' => $showCallStats,
            'callTypeStats' => $callTypeStats,
        ]);
    }
}
