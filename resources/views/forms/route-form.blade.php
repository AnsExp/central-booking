@extends('layouts.app')

@section('content')
    @php
        $routeStops = old('stops');
        $selectedTrains = old('trains');

        if (!is_array($selectedTrains) && $route) {
            $selectedTrains = $route->trains->pluck('id')->map(fn ($id) => (string) $id)->all();
        }
        $selectedTrains = is_array($selectedTrains) ? array_map('strval', $selectedTrains) : [];

        if (!is_array($routeStops) && $route) {
            $routeStops = $route->stops
                ->sortBy('stop_order')
                ->map(function ($stop) {
                    return [
                        'station_id' => (string) $stop->station_id,
                        'stop_order' => (string) $stop->stop_order,
                        'arrival_time' => substr((string) $stop->arrival_time, 0, 5),
                        'departure_time' => substr((string) $stop->departure_time, 0, 5),
                    ];
                })
                ->values()
                ->all();
        }

        if (!is_array($routeStops) || count($routeStops) === 0) {
            $routeStops = [[
                'station_id' => '',
                'stop_order' => '1',
                'arrival_time' => '',
                'departure_time' => '',
            ]];
        }

        $stationOptionsHtml = '';
        foreach ($stations as $stationOption) {
            $stationOptionsHtml .= '<option value="' . $stationOption->id . '">' . e($stationOption->name) . '</option>';
        }
    @endphp

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div
            class="rounded-3xl border border-slate-200 bg-gradient-to-r from-cyan-50 via-white to-indigo-50 p-6 sm:p-8 lg:p-10 text-slate-900 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div>
                    <h1 class="mt-4 text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                        @if ($route)
                            Editar ruta
                        @else
                            Registrar nueva ruta
                        @endif
                    </h1>
                </div>
                <a href="{{ route('routes.view') }}"
                    class="inline-flex items-center justify-center rounded-full bg-slate-900 text-white px-6 py-3 text-sm font-semibold hover:bg-slate-800 transition-all">
                    <x-heroicon-o-arrow-small-left class="w-5 h-5 mr-2" />
                    Volver a la lista
                </a>
            </div>

            <div class="mt-8">
                <form class="grid grid-cols-1 sm:grid-cols-2 gap-4" method="POST"
                    action="{{ $route ? route('routes.update', $route) : route('routes.store') }}">
                    @csrf
                    @if ($route)
                        @method('PUT')
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="origin_station_id">Estacion de origen *</label>
                        <select id="origin_station_id" name="origin_station_id"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                            <option value="">Selecciona una estacion</option>
                            @foreach ($stations as $station)
                                <option value="{{ $station->id }}" {{ (string) old('origin_station_id', $route->origin_station_id ?? '') === (string) $station->id ? 'selected' : '' }}>
                                    {{ $station->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="destination_station_id">Estacion de destino *</label>
                        <select id="destination_station_id" name="destination_station_id"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                            <option value="">Selecciona una estacion</option>
                            @foreach ($stations as $station)
                                <option value="{{ $station->id }}" {{ (string) old('destination_station_id', $route->destination_station_id ?? '') === (string) $station->id ? 'selected' : '' }}>
                                    {{ $station->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="departure_time">Hora de salida *</label>
                        <input id="departure_time" type="time" name="departure_time"
                            value="{{ old('departure_time', isset($route->departure_time) ? substr((string) $route->departure_time, 0, 5) : '') }}"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="arrival_time">Hora de llegada *</label>
                        <input id="arrival_time" type="time" name="arrival_time"
                            value="{{ old('arrival_time', isset($route->arrival_time) ? substr((string) $route->arrival_time, 0, 5) : '') }}"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    </div>

                    <div class="sm:col-span-2 mt-4">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-gray-700">Paradas de la ruta *</label>
                            <button type="button" id="add-stop-row"
                                class="inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-4 py-2 text-xs font-semibold hover:bg-slate-200 transition-all">
                                <x-heroicon-o-plus class="w-4 h-4 mr-1" />
                                Agregar parada
                            </button>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm" id="route-stops-table">
                                    <thead class="bg-slate-50 text-slate-600">
                                        <tr>
                                            <th class="text-left px-4 py-3 font-semibold">Estación</th>
                                            <th class="text-left px-4 py-3 font-semibold">Orden</th>
                                            <th class="text-left px-4 py-3 font-semibold">Llegada</th>
                                            <th class="text-left px-4 py-3 font-semibold">Salida</th>
                                            <th class="text-right px-4 py-3 font-semibold">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100" id="route-stops-body">
                                        @foreach ($routeStops as $index => $stop)
                                            <tr data-stop-row>
                                                <td class="px-4 py-3">
                                                    <select name="stops[{{ $index }}][station_id]"
                                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                                                        <option value="">Selecciona una estación</option>
                                                        @foreach ($stations as $stationOption)
                                                            <option value="{{ $stationOption->id }}" {{ (string) ($stop['station_id'] ?? '') === (string) $stationOption->id ? 'selected' : '' }}>
                                                                {{ $stationOption->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-4 py-3 w-28">
                                                    <input type="number" name="stops[{{ $index }}][stop_order]" min="1"
                                                        value="{{ $stop['stop_order'] ?? $index + 1 }}"
                                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                                                </td>
                                                <td class="px-4 py-3 w-36">
                                                    <input type="time" name="stops[{{ $index }}][arrival_time]"
                                                        value="{{ $stop['arrival_time'] ?? '' }}"
                                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                                                </td>
                                                <td class="px-4 py-3 w-36">
                                                    <input type="time" name="stops[{{ $index }}][departure_time]"
                                                        value="{{ $stop['departure_time'] ?? '' }}"
                                                        class="w-full rounded-xl border border-gray-200 px-3 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <button type="button"
                                                        class="inline-flex items-center rounded-full bg-red-50 text-red-700 px-3 py-1.5 text-xs font-semibold hover:bg-red-100 transition-all"
                                                        data-remove-stop>
                                                        <x-heroicon-o-trash class="w-4 h-4" />
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2 mt-2">
                        <p class="block text-sm font-medium text-gray-700 mb-2">Trenes asignados a la ruta</p>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm text-gray-700">
                                @forelse ($trains as $trainOption)
                                    @php $trainId = (string) $trainOption->id; @endphp
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" name="trains[]" value="{{ $trainOption->id }}"
                                            {{ in_array($trainId, $selectedTrains, true) ? 'checked' : '' }}>
                                        <span>
                                            {{ $trainOption->name }}
                                            <span class="text-gray-500">({{ $trainOption->code }})</span>
                                        </span>
                                    </label>
                                @empty
                                    <p class="text-gray-500">No hay trenes registrados. Crea trenes primero en el módulo de trenes.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="sm:col-span-2 flex justify-end">
                        <button type="submit"
                            class="rounded-full bg-blue-600 text-white px-6 py-3 text-sm font-semibold hover:bg-blue-700 transition-all">
                            {{ $route ? 'Guardar cambios' : 'Registrar ruta' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const tableBody = document.getElementById('route-stops-body');
            const addStopButton = document.getElementById('add-stop-row');
            if (!tableBody || !addStopButton) {
                return;
            }

            const stationOptions = @json($stationOptionsHtml);

            const reindexRows = () => {
                const rows = tableBody.querySelectorAll('[data-stop-row]');
                rows.forEach((row, index) => {
                    const inputs = row.querySelectorAll('select, input');
                    inputs.forEach((input) => {
                        if (!input.name) {
                            return;
                        }

                        input.name = input.name.replace(/stops\[\d+\]/, `stops[${index}]`);
                    });
                });
            };

            const buildRow = (index) => {
                const row = document.createElement('tr');
                row.setAttribute('data-stop-row', '');
                row.innerHTML = `
                    <td class="px-4 py-3">
                        <select name="stops[${index}][station_id]" class="w-full rounded-xl border border-gray-200 px-3 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                            <option value="">Selecciona una estación</option>
                            ${stationOptions}
                        </select>
                    </td>
                    <td class="px-4 py-3 w-28">
                        <input type="number" min="1" name="stops[${index}][stop_order]" value="${index + 1}" class="w-full rounded-xl border border-gray-200 px-3 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                    </td>
                    <td class="px-4 py-3 w-36">
                        <input type="time" name="stops[${index}][arrival_time]" class="w-full rounded-xl border border-gray-200 px-3 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                    </td>
                    <td class="px-4 py-3 w-36">
                        <input type="time" name="stops[${index}][departure_time]" class="w-full rounded-xl border border-gray-200 px-3 py-2 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10">
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button type="button" class="inline-flex items-center rounded-full bg-red-50 text-red-700 px-3 py-1.5 text-xs font-semibold hover:bg-red-100 transition-all" data-remove-stop>
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6l-1 14H6L5 6"></path>
                                <path d="M10 11v6"></path>
                                <path d="M14 11v6"></path>
                                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>
                            </svg>
                        </button>
                    </td>
                `;

                return row;
            };

            addStopButton.addEventListener('click', () => {
                const index = tableBody.querySelectorAll('[data-stop-row]').length;
                tableBody.appendChild(buildRow(index));
            });

            tableBody.addEventListener('click', (event) => {
                const removeButton = event.target.closest('[data-remove-stop]');
                if (!removeButton) {
                    return;
                }

                const rows = tableBody.querySelectorAll('[data-stop-row]');
                if (rows.length <= 1) {
                    return;
                }

                const row = removeButton.closest('[data-stop-row]');
                if (row) {
                    row.remove();
                    reindexRows();
                }
            });
        })();
    </script>
@endsection
