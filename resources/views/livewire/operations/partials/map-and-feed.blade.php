@php
    use App\Support\Operations\TimelineEventLabels;
@endphp

<div class="grid gap-4 xl:grid-cols-2">
    <flux:card class="relative flex min-h-[18rem] flex-col justify-center overflow-hidden">
        <flux:subheading class="mb-2">{{ __('Mapa tático') }}</flux:subheading>
        <flux:text size="sm" class="mb-4 max-w-prose text-zinc-600 dark:text-zinc-400">
            {{ __('Área reservada para Leaflet/OpenStreetMap, marcadores de ocorrências e posição das viaturas (Traccar / Reverb).') }}
        </flux:text>
        <div
            class="flex flex-1 flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50/50 dark:border-zinc-600 dark:bg-zinc-900/30"
            aria-hidden="true"
        >
            <flux:icon.map-pin class="size-12 text-zinc-400" />
            <flux:text class="mt-2 text-zinc-500">{{ __('Camada de mapa — próxima integração') }}</flux:text>
        </div>
    </flux:card>

    <flux:card class="flex flex-col gap-3">
        <flux:subheading>{{ __('Últimos eventos operacionais') }}</flux:subheading>
        @if ($recentTimeline->isEmpty())
            <flux:text size="sm">{{ __('Nenhum evento registrado ainda.') }}</flux:text>
        @else
            <ul class="max-h-[22rem] space-y-3 overflow-y-auto pe-1">
                @foreach ($recentTimeline as $event)
                    <li wire:key="tl-{{ $event->id }}" class="border-s-2 border-blue-500 ps-3 dark:border-blue-400">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <flux:text class="font-medium">{{ TimelineEventLabels::for($event->event_key) }}</flux:text>
                            <flux:text size="sm" class="tabular-nums text-zinc-500">{{ $event->recorded_at->format('d/m H:i:s') }}</flux:text>
                        </div>
                        @if ($event->incident)
                            <flux:link class="text-sm" :href="route('operations.incidents.show', $event->incident)" wire:navigate>
                                {{ __('Talão') }} {{ $event->incident->talao }}/{{ $event->incident->dispatch_year }}
                            </flux:link>
                        @endif
                        @if ($event->actor)
                            <flux:text size="sm" class="text-zinc-500">{{ __('Por') }} {{ $event->actor->name }}</flux:text>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </flux:card>
</div>
