@extends('layouts.app')

@php
    use App\Models\Service;
    use App\Models\TrainType;
    use App\Models\TrainStatus;

    $services = Service::all();
    $selectedServices = old('services');
    if (is_null($selectedServices) && isset($train) && $train) {
        $selectedServices = $train->services->pluck('id')->all();
    }
    $selectedServices = is_array($selectedServices) ? $selectedServices : [];

    $selectedRoutes = old('routes');
    if (is_null($selectedRoutes) && isset($train) && $train) {
        $selectedRoutes = $train->routes->pluck('id')->all();
    }
    $selectedRoutes = is_array($selectedRoutes) ? $selectedRoutes : [];

    $typeValue = old('type', $train->type ?? TrainType::PASSENGERS->value);
    $statusValue = old('status', $train->status ?? TrainStatus::ACTIVE->value);
    $notesValue = old('notes', $train?->metadataEntries?->firstWhere('meta_key', 'notes')?->meta_value['text'] ?? '');
@endphp

@section('content')
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div
            class="rounded-3xl border border-slate-200 bg-gradient-to-r from-cyan-50 via-white to-indigo-50 p-6 sm:p-8 lg:p-10 text-slate-900 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                <div>
                    <h1 class="mt-4 text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                        @if ($train)
                            Editar tren: <span class="text-blue-600">{{ $train->name }}</span>
                        @else
                            Registrar nuevo tren
                        @endif
                    </h1>
                </div>
                <a href="{{ route('trains.view') }}"
                    class="inline-flex items-center justify-center rounded-full bg-slate-900 text-white px-6 py-3 text-sm font-semibold hover:bg-slate-800 transition-all">
                    <x-heroicon-o-arrow-small-left class="w-5 h-5 mr-2" />
                    Volver a la lista
                </a>
            </div>
            <div class="mt-8">
                <form class="grid grid-cols-1 sm:grid-cols-2 gap-4" method="POST"
                    action="{{ $train ? route('trains.update', $train) : route('trains.store') }}">
                    @csrf
                    @if ($train)
                        @method('PUT')
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del tren *</label>
                        <input type="text" name="name" value="{{ old('name', $train->name ?? '') }}"
                            placeholder="Ej. Tren Ejecutivo Norte"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
                        <input type="text" name="code" value="{{ old('code', $train->code ?? '') }}" placeholder="TRN-100"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                        <select id="train-type" name="type" required
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                            @foreach (TrainType::cases() as $type)
                                <option value="{{$type->value}}" {{ $typeValue === $type->value ? 'selected' : '' }}>
                                    {{ TrainType::label($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estado *</label>
                        <select name="status" required
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                            @foreach (TrainStatus::cases() as $status)
                                <option value="{{$status->value}}" {{ $statusValue === $status->value ? 'selected' : '' }}>
                                    {{ TrainStatus::label($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div id="passenger-capacity-field" data-group="passenger">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacidad de pasajeros</label>
                        <input id="passenger-capacity-input" type="number" name="capacity" min="1"
                            value="{{ old('capacity', $train->capacity ?? '') }}" placeholder="Ej. 280"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    </div>

                    <div id="cargo-weight-field" data-group="cargo">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacidad de peso (kg)</label>
                        <input id="cargo-weight-input" type="number" name="baggage_weight_limit" min="0" step="0.01"
                            value="{{ old('baggage_weight_limit', $train->baggage_weight_limit ?? '') }}"
                            placeholder="Ej. 20"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    </div>

                    <div id="cargo-volume-field" class="sm:col-span-2" data-group="cargo">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacidad de volumen (m3)</label>
                        <input id="cargo-volume-input" type="number" name="baggage_volume_limit" min="0" step="0.01"
                            value="{{ old('baggage_volume_limit', $train->baggage_volume_limit ?? '') }}"
                            placeholder="Ej. 1.20"
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
                    </div>

                    <div class="sm:col-span-2">
                        <p class="block text-sm font-medium text-gray-700 mb-2">Servicios del tren</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-gray-700">
                            @forelse ($services as $service)
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="services[]" value="{{ $service->id }}" {{ in_array($service->id, $selectedServices, true) ? 'checked' : '' }}>
                                    {{ $service->name }} (${{ number_format((float) $service->price, 2, ',', '.') }})
                                </label>
                            @empty
                                <p class="text-gray-500">No hay servicios registrados. Crea servicios primero en la pestaña
                                    Servicios.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <p class="block text-sm font-medium text-gray-700 mb-2">Rutas asignadas al tren</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-gray-700">
                            @forelse ($routes as $routeOption)
                                @php
                                    $originName = $routeOption->originStation?->name ?? 'Sin origen';
                                    $destinationName = $routeOption->destinationStation?->name ?? 'Sin destino';
                                @endphp
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="routes[]" value="{{ $routeOption->id }}" {{ in_array($routeOption->id, $selectedRoutes, true) ? 'checked' : '' }}>
                                    Ruta #{{ $routeOption->id }}: {{ $originName }} -> {{ $destinationName }}
                                </label>
                            @empty
                                <p class="text-gray-500">No hay rutas registradas. Crea rutas primero en el módulo de rutas.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notas técnicas</label>
                        <textarea name="notes" rows="4"
                            placeholder="Información adicional del tren para futuras ampliaciones..."
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">{{ $notesValue }}</textarea>
                    </div>

                    <div class="sm:col-span-2 flex justify-end">
                        <button type="submit"
                            class="rounded-full bg-blue-600 text-white px-6 py-3 text-sm font-semibold hover:bg-blue-700 transition-all">
                            {{ $train ? 'Guardar cambios' : 'Registrar tren' }}
                        </button>
                    </div>
                </form>

                <script>
                    (function () {
                        const typeSelect = document.getElementById('train-type');
                        if (!typeSelect) {
                            return;
                        }

                        const passengerField = document.getElementById('passenger-capacity-field');
                        const passengerInput = document.getElementById('passenger-capacity-input');
                        const cargoWeightField = document.getElementById('cargo-weight-field');
                        const cargoWeightInput = document.getElementById('cargo-weight-input');
                        const cargoVolumeField = document.getElementById('cargo-volume-field');
                        const cargoVolumeInput = document.getElementById('cargo-volume-input');

                        const setVisibility = (element, visible) => {
                            if (!element) {
                                return;
                            }
                            element.classList.toggle('hidden', !visible);
                        };

                        const updateConditionalFields = () => {
                            const type = typeSelect.value;
                            const showPassenger = type === 'passengers' || type === 'mixed';
                            const showCargo = type === 'cargo' || type === 'mixed';

                            setVisibility(passengerField, showPassenger);
                            setVisibility(cargoWeightField, showCargo);
                            setVisibility(cargoVolumeField, showCargo);

                            if (passengerInput) {
                                passengerInput.disabled = !showPassenger;
                                passengerInput.required = showPassenger;
                            }

                            if (cargoWeightInput) {
                                cargoWeightInput.disabled = !showCargo;
                                cargoWeightInput.required = showCargo;
                            }

                            if (cargoVolumeInput) {
                                cargoVolumeInput.disabled = !showCargo;
                                cargoVolumeInput.required = showCargo;
                            }
                        };

                        typeSelect.addEventListener('change', updateConditionalFields);
                        updateConditionalFields();
                    })();
                </script>
            </div>
        </div>
    </div>
@endsection