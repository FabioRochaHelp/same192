<div class="cco-page-gap" wire:poll.10s>
    @include('livewire.operations.partials.dispatch-header')
    @include('livewire.operations.partials.dispatch-alerts')
    @include('livewire.operations.partials.tactical-strip', ['stats' => $stats])
    @include('livewire.operations.partials.quick-actions')
    @include('livewire.operations.partials.open-incidents')
    @include('livewire.operations.partials.map-and-feed')
    @include('livewire.operations.partials.kanban')

    <flux:text size="sm" class="border-t border-slate-200/95 pt-6 text-slate-600 dark:border-slate-700/50 dark:text-slate-500">
        {{ __('Eventos persistidos em') }}
        <code
            class="rounded-md border border-slate-300/90 bg-slate-100 px-1.5 py-0.5 font-mono text-xs text-cyan-900 dark:border-slate-700/60 dark:bg-slate-950/80 dark:text-cyan-200/90"
        >incident_events</code>.
        {{ __('Broadcast privado:') }}
        <code
            class="rounded-md border border-slate-300/90 bg-slate-100 px-1.5 py-0.5 font-mono text-xs text-cyan-900 dark:border-slate-700/60 dark:bg-slate-950/80 dark:text-cyan-200/90"
        >operations.dispatch</code>,
        <code
            class="rounded-md border border-slate-300/90 bg-slate-100 px-1.5 py-0.5 font-mono text-xs text-cyan-900 dark:border-slate-700/60 dark:bg-slate-950/80 dark:text-cyan-200/90"
        >operations.municipio.{id}</code>,
        <code
            class="rounded-md border border-slate-300/90 bg-slate-100 px-1.5 py-0.5 font-mono text-xs text-cyan-900 dark:border-slate-700/60 dark:bg-slate-950/80 dark:text-cyan-200/90"
        >incidents.{id}</code>.
    </flux:text>
</div>
