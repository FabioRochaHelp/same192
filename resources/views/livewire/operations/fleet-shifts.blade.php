<div class="cco-page-gap !gap-6" wire:poll.30s>
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">{{ __('Turnos e viaturas') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Turnos recentes e estado operacional (disponível / empenhado).') }}</flux:text>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @can('create', \App\Models\Shift::class)
                <flux:button variant="primary" icon="clock" :href="route('operations.cadastro.shifts')" wire:navigate>
                    {{ __('Gerir turnos') }}
                </flux:button>
            @endcan
            <flux:button variant="ghost" icon="radio" :href="route('operations.dispatch')" wire:navigate>
                {{ __('Central operacional') }}
            </flux:button>
        </div>
    </div>

    <flux:card class="space-y-4">
        @if ($shifts->isEmpty())
            <flux:text>{{ __('Nenhum turno encontrado no período.') }}</flux:text>
        @else
            <div class="overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-200 text-start text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-900/50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-zinc-700 dark:text-zinc-300">{{ __('Turno') }}</th>
                            <th class="px-4 py-3 font-medium text-zinc-700 dark:text-zinc-300">{{ __('Viatura') }}</th>
                            <th class="px-4 py-3 font-medium text-zinc-700 dark:text-zinc-300">{{ __('Estado') }}</th>
                            <th class="px-4 py-3 font-medium text-zinc-700 dark:text-zinc-300">{{ __('Início') }}</th>
                            <th class="px-4 py-3 font-medium text-zinc-700 dark:text-zinc-300">{{ __('Fim previsto') }}</th>
                            <th class="px-4 py-3 font-medium text-zinc-700 dark:text-zinc-300">{{ __('Base') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 bg-white dark:divide-zinc-800 dark:bg-zinc-900">
                        @foreach ($shifts as $shift)
                            <tr wire:key="shift-{{ $shift->id }}" class="hover:bg-zinc-50/80 dark:hover:bg-zinc-800/40">
                                <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-zinc-500">#{{ $shift->id }}</td>
                                <td class="px-4 py-3 font-medium">{{ $shift->vehicle?->prefix ?? '—' }} · {{ $shift->vehicle?->plate ?? __('Sem placa') }}</td>
                                <td class="px-4 py-3">
                                    @if ($shift->status->value === 'available')
                                        <flux:badge size="sm" :inset="true" color="green">{{ $shift->status->label() }}</flux:badge>
                                    @else
                                        <flux:badge size="sm" :inset="true" color="zinc">{{ $shift->status->label() }}</flux:badge>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 tabular-nums text-zinc-600 dark:text-zinc-400">{{ $shift->starts_at->format('d/m/Y H:i') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 tabular-nums text-zinc-600 dark:text-zinc-400">{{ $shift->ends_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-xs text-zinc-500">{{ $shift->municipio?->razao_social ?? ('#'.$shift->municipio_id) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </flux:card>
</div>
