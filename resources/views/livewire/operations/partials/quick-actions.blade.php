@php use Illuminate\Support\Facades\Gate; @endphp

<div class="grid gap-4 lg:grid-cols-3">
    <flux:card class="space-y-4">
        <flux:subheading>{{ __('Ingestão') }}</flux:subheading>
        @if (Gate::check('createOperational', $this->resolveOperationalMunicipioId()))
            <flux:text size="sm">{{ __('Registro rápido para exercício do fluxo (usa a primeira natureza do escopo).') }}</flux:text>
            <flux:button variant="primary" icon="plus" wire:click="createDemoIncident">{{ __('Nova ocorrência (demo)') }}</flux:button>
        @else
            <flux:callout variant="warning">{{ __('Defina o município/base ou confira permissões para registrar ocorrência.') }}</flux:callout>
        @endif
    </flux:card>

    <flux:card class="space-y-4 lg:col-span-2">
        <flux:subheading>{{ __('Empenho') }}</flux:subheading>
        <div class="grid gap-4 md:grid-cols-2 md:items-end">
            <flux:select wire:model.number="selectedVehicleId" :label="__('Viatura em turno disponível')">
                <flux:select.option value="">{{ __('Selecione') }}</flux:select.option>
                @foreach ($availableShifts as $shift)
                    <flux:select.option value="{{ $shift->vehicle_id }}">
                        {{ $shift->vehicle?->prefix ?? '—' }} · {{ $shift->vehicle?->plate ?? __('Sem placa') }} · #{{ $shift->id }}
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:button variant="filled" icon="paper-airplane" wire:click="dispatchIncident">
                {{ __('Empenhar na primeira ocorrência da fila') }}
            </flux:button>
        </div>
    </flux:card>

    <div class="flex flex-wrap gap-2 lg:col-span-3">
        <flux:button size="sm" variant="ghost" icon="rectangle-stack" :href="route('operations.incidents.index')" wire:navigate>
            {{ __('Lista de ocorrências') }}
        </flux:button>
        <flux:button size="sm" variant="ghost" icon="truck" :href="route('operations.fleet')" wire:navigate>
            {{ __('Turnos e viaturas') }}
        </flux:button>
        @if (auth()->user()?->isOperationalCentral())
            <flux:button size="sm" variant="ghost" icon="adjustments-horizontal" :href="route('operations.parameters.accessories')" wire:navigate>
                {{ __('Parâmetros da ocorrência') }}
            </flux:button>
            <flux:button size="sm" variant="ghost" icon="building-office-2" :href="route('operations.cadastro.bases')" wire:navigate>
                {{ __('Cadastro — bases') }}
            </flux:button>
        @endif
        <flux:button size="sm" variant="ghost" icon="cube" :href="route('operations.cadastro.vehicles')" wire:navigate>
            {{ __('Cadastro — viaturas') }}
        </flux:button>
        <flux:button size="sm" variant="ghost" icon="users" :href="route('operations.cadastro.staff')" wire:navigate>
            {{ __('Cadastro — efetivo') }}
        </flux:button>
        @if (Gate::check('createOperational', $this->resolveOperationalMunicipioId()))
            <flux:button size="sm" variant="ghost" icon="plus-circle" :href="route('operations.incidents.create')" wire:navigate>
                {{ __('Cadastro — ocorrência') }}
            </flux:button>
        @endif
    </div>
</div>
