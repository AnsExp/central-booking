@extends('layouts.app')

@section('content')
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div
            class="rounded-3xl border border-slate-200 bg-gradient-to-r from-cyan-50 via-white to-indigo-50 p-6 sm:p-8 lg:p-10 text-slate-900 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div>
                    <h1 class="mt-4 text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                        @if ($station)
                            Editar estación: <span class="text-blue-600">{{ $station->name }}</span>
                        @else
                            Registrar nueva estación
                        @endif
                    </h1>
                </div>
                <a href="{{ route('stations.view') }}"
                    class="inline-flex items-center justify-center rounded-full bg-slate-900 text-white px-6 py-3 text-sm font-semibold hover:bg-slate-800 transition-all">
                    <x-heroicon-o-arrow-small-left class="w-5 h-5 mr-2" />
                    Volver a la lista
                </a>
            </div>
            <div class="mt-8">
                <form class="grid grid-cols-1 sm:grid-cols-2 gap-4" method="POST"
                    action="{{ $station ? route('stations.update', $station) : route('stations.store') }}">
                    @csrf
                    @if ($station)
                        @method('PUT')
                    @endif
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="input-name">Nombre de la estacion
                            *</label>
                        <input id="input-name" type="text" autocomplete="name" name="name" max="255"
                            value="{{ old('name', $station->name ?? '') }}" placeholder="Ej. Estacion Central"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="input-location">Ubicación *</label>
                        <input id="input-location" type="text" autocomplete="location" name="location" max="255"
                            value="{{ old('location', $station->location ?? '') }}" placeholder="Ej. Ciudad de México"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    </div>
                    <div class="sm:col-span-2 flex justify-end">
                        <button type="submit"
                            class="rounded-full bg-blue-600 text-white px-6 py-3 text-sm font-semibold hover:bg-blue-700 transition-all">
                            {{ $station ? 'Guardar cambios' : 'Registrar estación' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection