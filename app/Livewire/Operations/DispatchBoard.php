<?php

declare(strict_types=1);

namespace App\Livewire\Operations;

use App\Domain\Operations\Actions\AdvanceDispatchStageAction;
use App\Domain\Operations\Actions\CreateOperationalIncidentAction;
use App\Domain\Operations\Actions\DispatchUnitAction;
use App\Domain\Operations\Actions\ReleaseUnitAction;
use App\Domain\Operations\DTOs\AdvanceDispatchStageDTO;
use App\Domain\Operations\DTOs\CreateIncidentDTO;
use App\Domain\Operations\DTOs\DispatchUnitDTO;
use App\Domain\Operations\DTOs\ReleaseUnitDTO;
use App\Domain\Operations\Enums\CallType;
use App\Domain\Operations\Enums\DispatchStage;
use App\Domain\Operations\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\IncidentDispatch;
use App\Models\IncidentEvent;
use App\Models\Municipio;
use App\Models\Nature;
use App\Models\Shift;
use App\Support\Operations\OperationalMunicipioSelection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use RuntimeException;

#[Layout('layouts.app')]
#[Title('Central operacional')]
final class DispatchBoard extends Component
{
    public ?int $selectedVehicleId = null;

    /** ID numérico em `municipios` para usuários centrais (sessão). */
    public ?string $selectedOperationalMunicipioId = null;

    public string $boardMessage = '';

    public function mount(): void
    {
        $user = Auth::user();
        if ($user !== null && $user->isOperationalCentral()) {
            $this->selectedOperationalMunicipioId = session('operational_municipio_id') !== null
                ? (string) session('operational_municipio_id')
                : null;
        }
    }

    public function updatedSelectedOperationalMunicipioId(?string $value): void
    {
        if ($value === null || $value === '') {
            session()->forget('operational_municipio_id');
        } else {
            session(['operational_municipio_id' => (int) $value]);
        }
    }

    public function resolveOperationalMunicipioId(): ?int
    {
        return OperationalMunicipioSelection::current(Auth::user());
    }

    public function createDemoIncident(CreateOperationalIncidentAction $action): void
    {
        $this->resetErrorBag();
        $this->boardMessage = '';

        $municipioId = $this->resolveOperationalMunicipioId();
        if ($municipioId === null) {
            $this->addError('tenant', 'Selecione o município/base operacional.');

            return;
        }

        $nature = Nature::query()->orderBy('id')->first();
        if ($nature === null) {
            $this->addError('tenant', 'Cadastre ao menos uma natureza para o município.');

            return;
        }

        Gate::authorize('createOperational', $municipioId);

        $dto = new CreateIncidentDTO(
            municipioId: $municipioId,
            natureId: $nature->id,
            description: 'Ocorrência demonstrativa (CCO)',
            addressLine: null,
            number: null,
            district: null,
            city: null,
            callerName: 'Central',
            callerPhone: null,
            patientAge: null,
            patientSex: null,
            latitude: null,
            longitude: null,
            referenceNotes: null,
            callType: CallType::Normal,
            expectedVictimTotal: null,
            createdByUserId: Auth::id(),
        );

        try {
            $action->execute($dto);
            $this->boardMessage = 'Ocorrência registrada.';
        } catch (RuntimeException $e) {
            $this->addError('board', $e->getMessage());
        }
    }

    public function dispatchIncident(DispatchUnitAction $action): void
    {
        $this->resetErrorBag();
        $this->boardMessage = '';

        if ($this->selectedVehicleId === null) {
            $this->addError('vehicle', 'Selecione uma viatura em turno disponível.');

            return;
        }

        /** @var Incident|null $incident */
        $incident = Incident::query()->where('status', IncidentStatus::Open)->orderByDesc('occurred_at')->first();

        if ($incident === null) {
            $this->addError('board', 'Não há ocorrências abertas para despacho.');

            return;
        }

        Gate::authorize('dispatchUnit', $incident);

        try {
            $action->execute(new DispatchUnitDTO(
                incidentId: $incident->id,
                vehicleId: $this->selectedVehicleId,
                note: null,
                operatorUserId: Auth::id(),
            ));
            $this->boardMessage = 'Equipe empenhada.';
        } catch (RuntimeException $e) {
            $this->addError('board', $e->getMessage());
        }
    }

    public function advanceStage(int $dispatchId, AdvanceDispatchStageAction $action): void
    {
        $this->resetErrorBag();
        $this->boardMessage = '';

        /** @var IncidentDispatch|null $dispatch */
        $dispatch = IncidentDispatch::query()->find($dispatchId);
        if ($dispatch === null) {
            return;
        }

        Gate::authorize('advanceStage', $dispatch->incident);

        $target = $dispatch->stage->next();
        if ($target === null) {
            return;
        }

        try {
            $action->execute(new AdvanceDispatchStageDTO(
                incidentDispatchId: $dispatch->id,
                targetStage: $target,
                operatorUserId: Auth::id(),
            ));
            $this->boardMessage = 'Etapa atualizada.';
        } catch (RuntimeException $e) {
            $this->addError('board', $e->getMessage());
        }
    }

    public function releaseIncident(int $incidentId, int $vehicleId, ReleaseUnitAction $action): void
    {
        $this->resetErrorBag();
        $this->boardMessage = '';

        /** @var Incident|null $incident */
        $incident = Incident::query()->find($incidentId);
        if ($incident === null) {
            return;
        }

        Gate::authorize('releaseUnit', $incident);

        try {
            $action->execute(new ReleaseUnitDTO(
                incidentId: $incident->id,
                vehicleId: $vehicleId,
                operatorUserId: Auth::id(),
            ));
            $this->boardMessage = 'Viatura liberada / ocorrência encerrada.';
        } catch (RuntimeException $e) {
            $this->addError('board', $e->getMessage());
        }
    }

    public function render(): View
    {
        $municipioOptions = Auth::user()?->isOperationalCentral()
            ? Municipio::query()->orderBy('razao_social')->get()
            : collect();

        $openIncidents = Incident::query()
            ->with('municipio')
            ->where('status', IncidentStatus::Open)
            ->orderByDesc('occurred_at')
            ->get();

        $availableShifts = Shift::query()
            ->with('vehicle')
            ->operationalAvailability()
            ->orderBy('id')
            ->get();

        $kanbanDispatches = IncidentDispatch::query()
            ->with(['incident', 'shift.vehicle'])
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (IncidentDispatch $d) => $d->stage->value);

        $recentTimeline = IncidentEvent::query()
            ->with(['incident', 'actor'])
            ->latest('recorded_at')
            ->limit(25)
            ->get();

        $stats = [
            'open_incidents' => Incident::query()->where('status', IncidentStatus::Open)->count(),
            'active_dispatches' => IncidentDispatch::query()->whereNull('deleted_at')->count(),
            'available_units' => Shift::query()->operationalAvailability()->count(),
        ];

        return view('livewire.operations.dispatch-board', [
            'municipioOptions' => $municipioOptions,
            'openIncidents' => $openIncidents,
            'availableShifts' => $availableShifts,
            'kanbanDispatches' => $kanbanDispatches,
            'orderedStages' => DispatchStage::ordered(),
            'recentTimeline' => $recentTimeline,
            'stats' => $stats,
        ]);
    }
}
