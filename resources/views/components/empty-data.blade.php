@props([
    'title' => 'Sin contenido para mostrar',
    'message' => 'Aun no hay registros en este modulo.',
    'actionLabel' => null,
    'actionHref' => null,
])

<div class="rounded-2xl border border-slate-200 bg-white p-8 sm:p-10 text-center shadow-sm">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-600">
        <x-heroicon-o-inbox class="h-7 w-7" />
    </div>

    <h3 class="mt-5 text-xl font-bold text-slate-900">{{ $title }}</h3>
    <p class="mt-2 text-sm sm:text-base text-slate-600 max-w-2xl mx-auto">{{ $message }}</p>

    @if ($actionLabel && $actionHref)
        <a href="{{ $actionHref }}"
            class="mt-6 inline-flex items-center justify-center rounded-full bg-slate-900 text-white px-6 py-2.5 text-sm font-semibold hover:bg-slate-800 transition-all">
            {{ $actionLabel }}
        </a>
    @endif
</div>
