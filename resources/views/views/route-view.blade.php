@extends('layouts.app')

@section('content')
    @php
        $nextOriginDirection = $sort === 'origin' && $direction === 'asc' ? 'desc' : 'asc';
        $nextDestinationDirection = $sort === 'destination' && $direction === 'asc' ? 'desc' : 'asc';
        $nextDepartureDirection = $sort === 'departure_time' && $direction === 'asc' ? 'desc' : 'asc';
        $nextArrivalDirection = $sort === 'arrival_time' && $direction === 'asc' ? 'desc' : 'asc';
    @endphp

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div
            class="rounded-3xl border border-slate-200 bg-gradient-to-r from-cyan-50 via-white to-indigo-50 p-6 sm:p-8 lg:p-10 text-slate-900 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h1 class="mt-4 text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                        Rutas
                    </h1>
                    <p class="mt-4 text-slate-600 max-w-3xl text-base sm:text-lg">
                        Este es el modulo de rutas. Administra origen, destino y horarios operativos.
                    </p>
                </div>
                @if ($routesList->total() > 0)
                    <a href="{{ route('routes.form') }}"
                        class="inline-flex items-center justify-center rounded-full bg-slate-900 text-white px-6 py-3 text-sm font-semibold hover:bg-slate-800 transition-all">
                        <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                        Nuevo
                    </a>
                @endif
            </div>

            @if ($routesList->total() > 0)
                <div class="mt-8 rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="table-fixed min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="text-left px-4 py-3 font-semibold">
                                        <a href="{{ route('routes.view', ['sort' => 'origin', 'direction' => $nextOriginDirection]) }}"
                                            class="inline-flex items-center gap-1 hover:text-slate-900 transition-colors">
                                            Origen
                                            @if ($sort === 'origin')
                                                @if ($direction === 'asc')
                                                    <x-heroicon-o-arrow-small-down class="w-4 h-4" />
                                                @else
                                                    <x-heroicon-o-arrow-small-up class="w-4 h-4" />
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left px-4 py-3 font-semibold">
                                        <a href="{{ route('routes.view', ['sort' => 'destination', 'direction' => $nextDestinationDirection]) }}"
                                            class="inline-flex items-center gap-1 hover:text-slate-900 transition-colors">
                                            Destino
                                            @if ($sort === 'destination')
                                                @if ($direction === 'asc')
                                                    <x-heroicon-o-arrow-small-down class="w-4 h-4" />
                                                @else
                                                    <x-heroicon-o-arrow-small-up class="w-4 h-4" />
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left px-4 py-3 font-semibold">
                                        <a href="{{ route('routes.view', ['sort' => 'departure_time', 'direction' => $nextDepartureDirection]) }}"
                                            class="inline-flex items-center gap-1 hover:text-slate-900 transition-colors">
                                            Salida
                                            @if ($sort === 'departure_time')
                                                @if ($direction === 'asc')
                                                    <x-heroicon-o-arrow-small-down class="w-4 h-4" />
                                                @else
                                                    <x-heroicon-o-arrow-small-up class="w-4 h-4" />
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left px-4 py-3 font-semibold">
                                        <a href="{{ route('routes.view', ['sort' => 'arrival_time', 'direction' => $nextArrivalDirection]) }}"
                                            class="inline-flex items-center gap-1 hover:text-slate-900 transition-colors">
                                            Llegada
                                            @if ($sort === 'arrival_time')
                                                @if ($direction === 'asc')
                                                    <x-heroicon-o-arrow-small-down class="w-4 h-4" />
                                                @else
                                                    <x-heroicon-o-arrow-small-up class="w-4 h-4" />
                                                @endif
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-right px-4 py-3 font-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach ($routesList as $route)
                                    <tr>
                                        <td class="px-4 py-3">{{ $route->originStation?->name ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $route->destinationStation?->name ?? '-' }}</td>
                                        <td class="px-4 py-3">{{ $route->departure_time }}</td>
                                        <td class="px-4 py-3">{{ $route->arrival_time }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('routes.form', ['route' => $route->id]) }}"
                                                    class="inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-3 py-1.5 text-xs font-semibold hover:bg-slate-200 transition-all"
                                                    title="Editar ruta">
                                                    <x-heroicon-o-pencil class="w-4 h-4 text-slate-600" />
                                                </a>

                                                <form method="POST" action="{{ route('routes.destroy', $route) }}"
                                                    onsubmit="return confirm('¿Deseas eliminar esta ruta?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center rounded-full bg-red-50 text-red-700 px-3 py-1.5 text-xs font-semibold hover:bg-red-100 transition-all"
                                                        title="Eliminar ruta">
                                                        <x-heroicon-o-trash class="w-4 h-4" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <x-pagination-controls :paginator="$routesList" />
                </div>
            @else
                <div class="mt-8">
                    <x-empty-data title="No hay rutas registradas"
                        message="Aun no hay rutas creadas. Registra una ruta para iniciar la planificacion operativa."
                        action-label="Crear ruta" action-href="{{ route('routes.form') }}" />
                </div>
            @endif
        </div>
    </div>
@endsection
