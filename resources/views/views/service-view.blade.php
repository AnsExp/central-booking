@extends('layouts.app')

@section('content')
    @php
        $nextNameDirection = $sort === 'name' && $direction === 'asc' ? 'desc' : 'asc';
        $nextPriceDirection = $sort === 'price' && $direction === 'asc' ? 'desc' : 'asc';
    @endphp
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div
            class="rounded-3xl border border-slate-200 bg-gradient-to-r from-cyan-50 via-white to-indigo-50 p-6 sm:p-8 lg:p-10 text-slate-900 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <h1 class="mt-4 text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                        Servicios
                    </h1>
                    <p class="mt-4 text-slate-600 max-w-3xl text-base sm:text-lg">
                        Este es el módulo de servicios. Desde aquí puedes ir directamente al módulo de edición.
                    </p>
                </div>
                @if ($services->total() > 0)
                    <a href="{{ route('services.form') }}"
                        class="inline-flex items-center justify-center rounded-full bg-slate-900 text-white px-6 py-3 text-sm font-semibold hover:bg-slate-800 transition-all">
                        <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                        Nuevo
                    </a>
                @endif
            </div>
            @if ($services->total() > 0)
                <div class="mt-8 rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="table-fixed min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-600">
                                <tr>
                                    <th class="text-left px-4 py-3 font-semibold">
                                        <a href="{{ route('services.view', ['sort' => 'name', 'direction' => $nextNameDirection]) }}"
                                            class="inline-flex items-center gap-1 hover:text-slate-900 transition-colors">
                                            Nombre
                                            @if ($sort === 'name')
                                                <span class="text-xs">
                                                    @if ($direction === 'asc')
                                                        <x-heroicon-o-arrow-small-down class="w-4 h-4" />
                                                    @else
                                                        <x-heroicon-o-arrow-small-up class="w-4 h-4" />
                                                    @endif
                                                </span>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-left px-4 py-3 font-semibold">
                                        <a href="{{ route('services.view', ['sort' => 'price', 'direction' => $nextPriceDirection]) }}"
                                            class="inline-flex items-center gap-1 hover:text-slate-900 transition-colors">
                                            Precio
                                            @if ($sort === 'price')
                                                <span class="text-xs">
                                                    @if ($direction === 'asc')
                                                        <x-heroicon-o-arrow-small-down class="w-4 h-4" />
                                                    @else
                                                        <x-heroicon-o-arrow-small-up class="w-4 h-4" />
                                                    @endif
                                                </span>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-right px-4 py-3 font-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                @foreach ($services as $service)
                                    <tr>
                                        <td class="px-4 py-3">{{ $service->name }}</td>
                                        <td class="px-4 py-3">$ {{ number_format($service->price, 2) }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('services.form', ['service' => $service->id]) }}"
                                                    class="inline-flex items-center rounded-full bg-slate-100 text-slate-700 px-3 py-1.5 text-xs font-semibold hover:bg-slate-200 transition-all"
                                                    title="Editar servicio">
                                                    <x-heroicon-o-pencil class="w-4 h-4 text-slate-600" />
                                                </a>

                                                <form method="POST" action="{{ route('services.destroy', $service) }}"
                                                    onsubmit="return confirm('¿Deseas eliminar este servicio?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center rounded-full bg-red-50 text-red-700 px-3 py-1.5 text-xs font-semibold hover:bg-red-100 transition-all"
                                                        title="Eliminar servicio">
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
                    <x-pagination-controls :paginator="$services" />
                </div>
            @else
                <div class="mt-8">
                    <x-empty-data title="No hay servicios registradas"
                        message="Parece que aún no has agregado ningún servicio. Comienza creando uno nuevo para gestionar tu red de servicios."
                        action-label="Crear servicio" action-href="{{ route('services.form') }}" />
                </div>
            @endif
        </div>
    </div>
@endsection