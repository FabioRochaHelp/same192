@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-400 to-cyan-600 text-slate-950 shadow-md shadow-cyan-500/30">
            <x-app-logo-icon class="size-5 fill-current text-slate-950" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-400 to-cyan-600 text-slate-950 shadow-md shadow-cyan-500/30">
            <x-app-logo-icon class="size-5 fill-current text-slate-950" />
        </x-slot>
    </flux:brand>
@endif
