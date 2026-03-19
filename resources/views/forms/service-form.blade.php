@extends('layouts.app')

@php
    $selectedTrains = old('trains');
    if (is_null($selectedTrains) && isset($service) && $service) {
        $selectedTrains = $service->trains->pluck('id')->all();
    }
    $selectedTrains = is_array($selectedTrains) ? $selectedTrains : [];
@endphp

@section('content')
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div
            class="rounded-3xl border border-slate-200 bg-gradient-to-r from-cyan-50 via-white to-indigo-50 p-6 sm:p-8 lg:p-10 text-slate-900 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div>
                    <h1 class="mt-4 text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                        @if ($service)
                            Editar servicio: <span class="text-blue-600">{{ $service->name }}</span>
                        @else
                            Registrar nuevo servicio
                        @endif
                    </h1>
                </div>
                <a href="{{ route('services.view') }}"
                    class="inline-flex items-center justify-center rounded-full bg-slate-900 text-white px-6 py-3 text-sm font-semibold hover:bg-slate-800 transition-all">
                    <x-heroicon-o-arrow-small-left class="w-5 h-5 mr-2" />
                    Volver a la lista
                </a>
            </div>
            <div class="mt-8">
                <form class="grid grid-cols-1 sm:grid-cols-2 gap-4" method="POST"
                    action="{{ $service ? route('services.update', $service) : route('services.store') }}">
                    @csrf
                    @if ($service)
                        @method('PUT')
                    @endif
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="input-name">Nombre del servicio
                            *</label>
                        <input id="input-name" type="text" autocomplete="name" name="name" max="255"
                            value="{{ old('name', $service->name ?? '') }}" placeholder="Ej. Servicio Central"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="input-price">Precio *</label>
                        <input id="input-price" type="number" autocomplete="price" name="price" step="0.1" min="0"
                            value="{{ old('price', $service->price ?? '') }}" placeholder="Ej. 100.00"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    </div>

                    <div class="sm:col-span-2">
                        <p class="block text-sm font-medium text-gray-700 mb-2">Transportes asignados a este servicio</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-gray-700">
                            @forelse ($trains as $trainOption)
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="trains[]" value="{{ $trainOption->id }}" {{ in_array($trainOption->id, $selectedTrains, true) ? 'checked' : '' }}>
                                    {{ $trainOption->name }} <span class="text-gray-500">({{ $trainOption->code }})</span>
                                </label>
                            @empty
                                <p class="text-gray-500">No hay trenes registrados. Crea trenes primero en el modulo de trenes.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="sm:col-span-2 flex justify-end">
                        <button type="submit"
                            class="rounded-full bg-blue-600 text-white px-6 py-3 text-sm font-semibold hover:bg-blue-700 transition-all">
                            {{ $service ? 'Guardar cambios' : 'Registrar servicio' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection