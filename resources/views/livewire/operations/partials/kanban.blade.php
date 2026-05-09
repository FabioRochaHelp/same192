@php
    use App\Domain\Operations\Enums\DispatchStage;
@endphp

<div>
    <flux:subheading class="mb-4 text-slate-700 dark:text-slate-300">{{ __('Kanban — etapas do deslocamento') }}</flux:subheading>
    <div class="grid gap-4 lg:grid-cols-3 xl:grid-cols-6">
        @foreach ($orderedStages as $stage)
            <flux:card class="cco-kanban-column flex min-h-52 flex-col gap-3">
                <div class="flex items-center justify-between gap-2">
                    <flux:badge color="cyan">{{ $stage->label() }}</flux:badge>
                    <flux:text size="sm" class="tabular-nums text-slate-500">{{ $stage->index() + 1 }}/6</flux:text>
                </div>
                @php($cards = $kanbanDispatches->get($stage->value, collect()))
                <div class="flex flex-1 flex-col gap-2">
                    @forelse ($cards as $dispatch)
                        <div
                            wire:key="dispatch-{{ $dispatch->id }}"
                            class="cco-kanban-card"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <flux:text class="font-semibold tabular-nums">#{{ $dispatch->incident?->talao }}/{{ $dispatch->incident?->dispatch_year }}</flux:text>
                                @if ($dispatch->shift?->vehicle)
                                    <flux:badge size="sm" color="zinc">{{ $dispatch->shift->vehicle->prefix }}</flux:badge>
                                @endif
                            </div>
                            @if ($dispatch->shift?->vehicle?->plate)
                                <flux:text size="sm" class="text-slate-600 dark:text-slate-500">{{ $dispatch->shift->vehicle->plate }}</flux:text>
                            @endif
                            @can('view', $dispatch->incident)
                                <flux:link class="mt-1 block text-xs" :href="route('operations.incidents.show', $dispatch->incident)" wire:navigate>
                                    {{ __('Painel da ocorrência') }}
                                </flux:link>
                            @endcan
                            @can('advanceStage', $dispatch->incident)
                                @if ($dispatch->stage->next())
                                    <flux:button size="sm" class="mt-3 w-full" wire:click="advanceStage({{ $dispatch->id }})">
                                        {{ __('Avançar para') }}:
                                        {{ $dispatch->stage->next()?->label() }}
                                    </flux:button>
                                @endif
                            @endcan
                            @can('releaseUnit', $dispatch->incident)
                                @if ($dispatch->stage === DispatchStage::ReleasedHospital && $dispatch->shift?->vehicle_id)
                                    <flux:button
                                        size="sm"
                                        variant="danger"
                                        class="mt-2 w-full"
                                        wire:click="releaseIncident({{ $dispatch->incident_id }}, {{ $dispatch->shift->vehicle_id }})"
                                    >
                                        {{ __('Encerrar na base') }}
                                    </flux:button>
                                @endif
                            @endcan
                        </div>
                    @empty
                        <flux:text size="sm" class="text-slate-600 dark:text-slate-500">{{ __('Sem equipes nesta coluna') }}</flux:text>
                    @endforelse
                </div>
            </flux:card>
        @endforeach
    </div>
</div>
