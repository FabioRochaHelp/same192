<?php

declare(strict_types=1);

namespace App\Livewire\Operations\Cadastro;

use App\Domain\Operations\Enums\ShiftStatus;
use App\Models\Municipio;
use App\Models\Shift;
use App\Models\Staff;
use App\Models\Vehicle;
use App\Support\Operations\OperationalMunicipioSelection;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/** CRUD de turnos de serviço (docs/migracao/entidades.md — turno). */
#[Layout('layouts.app')]
#[Title('Turnos de serviço')]
final class ShiftManage extends Component
{
    public ?string $vehicle_id = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public string $status = '';

    /** @var list<int|string> */
    public array $staffIds = [];

    public ?int $editingId = null;

    public string $message = '';

    public ?string $selectedOperationalMunicipioId = null;

    public function mount(): void
    {
        Gate::authorize('viewAny', Shift::class);
        $user = Auth::user();
        if ($user !== null && $user->isOperationalCentral()) {
            $this->selectedOperationalMunicipioId = session('operational_municipio_id') !== null
                ? (string) session('operational_municipio_id')
                : null;
        }
        $this->status = ShiftStatus::Available->value;
    }

    public function updatedSelectedOperationalMunicipioId(?string $value): void
    {
        if ($value === null || $value === '') {
            session()->forget('operational_municipio_id');
        } else {
            session(['operational_municipio_id' => (int) $value]);
        }
    }

    private function municipioId(): ?int
    {
        $user = Auth::user();
        if ($user?->municipio_id !== null) {
            return (int) $user->municipio_id;
        }
        if ($user?->isOperationalCentral()) {
            return $this->selectedOperationalMunicipioId !== null && $this->selectedOperationalMunicipioId !== ''
                ? (int) $this->selectedOperationalMunicipioId
                : null;
        }

        return OperationalMunicipioSelection::current($user);
    }

    public function resetForm(): void
    {
        $this->vehicle_id = '';
        $this->starts_at = '';
        $this->ends_at = '';
        $this->status = ShiftStatus::Available->value;
        $this->staffIds = [];
        $this->editingId = null;
    }

    public function edit(int $id): void
    {
        $this->resetErrorBag();
        $shift = Shift::query()->with('staff')->findOrFail($id);
        $this->authorize('update', $shift);
        $this->editingId = $shift->id;
        $this->vehicle_id = (string) $shift->vehicle_id;
        $this->starts_at = $shift->starts_at->format('Y-m-d\TH:i');
        $this->ends_at = $shift->ends_at->format('Y-m-d\TH:i');
        $this->status = $shift->status->value;
        $this->staffIds = $shift->staff->pluck('id')->map(fn (int $id): string => (string) $id)->values()->all();
    }

    public function save(): void
    {
        $this->resetErrorBag();
        $mid = $this->municipioId();
        if ($mid === null) {
            $this->addError('scope', __('Defina a base (município) para criar ou editar turnos.'));

            return;
        }

        $validated = $this->validate([
            'vehicle_id' => [
                'required',
                Rule::exists('vehicles', 'id')->where(fn ($q) => $q->where('municipio_id', $mid)),
            ],
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'ends_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:starts_at'],
            'status' => ['required', Rule::enum(ShiftStatus::class)],
            'staffIds' => ['array'],
            'staffIds.*' => [
                'integer',
                Rule::exists('staff', 'id')->where(fn ($q) => $q->where('municipio_id', $mid)),
            ],
        ]);

        $starts = CarbonImmutable::createFromFormat('Y-m-d\TH:i', $validated['starts_at'], config('app.timezone'));
        $ends = CarbonImmutable::createFromFormat('Y-m-d\TH:i', $validated['ends_at'], config('app.timezone'));

        $vehicleId = (int) $validated['vehicle_id'];
        if ($this->vehicleOverlapsAnotherShift($vehicleId, $starts, $ends, $this->editingId)) {
            $this->addError('vehicle_id', __('Esta viatura já possui turno com período sobreposto.'));

            return;
        }

        $payload = [
            'municipio_id' => $mid,
            'vehicle_id' => $vehicleId,
            'starts_at' => $starts,
            'ends_at' => $ends,
            'status' => ShiftStatus::from($validated['status']),
            'status_legacy' => ShiftStatus::from($validated['status']) === ShiftStatus::Available ? 1 : 2,
        ];

        $staffPivotIds = array_values(array_unique(array_map(static fn ($id): int => (int) $id, $validated['staffIds'] ?? [])));

        if ($this->editingId !== null) {
            $shift = Shift::query()->findOrFail($this->editingId);
            $this->authorize('update', $shift);
            $shift->update($payload);
            $shift->staff()->sync($staffPivotIds);
            $this->message = __('Turno atualizado.');
        } else {
            $this->authorize('create', Shift::class);
            $shift = Shift::query()->create($payload);
            $shift->staff()->sync($staffPivotIds);
            $this->message = __('Turno criado.');
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $this->resetErrorBag();
        $shift = Shift::query()->findOrFail($id);
        $this->authorize('delete', $shift);
        if ($shift->incidentDispatches()->exists()) {
            $this->addError('delete', __('Não é possível excluir: existem vínculos de despacho com este turno.'));

            return;
        }
        $shift->staff()->detach();
        $shift->delete();
        $this->message = __('Turno excluído.');
        if ($this->editingId === $id) {
            $this->resetForm();
        }
    }

    public function render(): View
    {
        $mid = $this->municipioId();

        $vehicleQuery = Vehicle::query()->orderBy('prefix');
        $staffQuery = Staff::query()->orderBy('name');
        if ($mid !== null) {
            $vehicleQuery->where('municipio_id', $mid);
            $staffQuery->where('municipio_id', $mid);
        }

        $shiftQuery = Shift::query()->with(['vehicle', 'staff'])->orderByDesc('starts_at');
        if ($mid !== null) {
            $shiftQuery->where('municipio_id', $mid);
        }

        return view('livewire.operations.cadastro.shift-manage', [
            'scopeMunicipioId' => $mid,
            'vehicles' => $vehicleQuery->get(),
            'staffMembers' => $staffQuery->get(),
            'shifts' => $shiftQuery->limit(120)->get(),
            'statusCases' => ShiftStatus::cases(),
            'operationalMunicipios' => Auth::user()?->isOperationalCentral()
                ? Municipio::query()->where('active', true)->orderBy('razao_social')->get()
                : collect(),
        ]);
    }

    private function vehicleOverlapsAnotherShift(int $vehicleId, CarbonImmutable $start, CarbonImmutable $end, ?int $ignoreShiftId): bool
    {
        return Shift::query()
            ->where('vehicle_id', $vehicleId)
            ->when($ignoreShiftId !== null, fn ($q) => $q->whereKeyNot($ignoreShiftId))
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();
    }
}
