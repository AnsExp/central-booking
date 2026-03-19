@extends('layouts.app')

@section('content')
    <!-- Hero Section -->
    <div class="relative bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32 pt-20">
                <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                    <div class="sm:text-center lg:text-left">
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl">
                            <span class="block xl:inline">Tu próximo viaje en tren,</span>
                            <span class="block text-blue-600 xl:inline">más fácil que nunca</span>
                        </h1>
                        <p
                            class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                            Bienvenido a Central Booking. Compra tus boletos, gestiona tus viajes y disfruta de la mejor
                            experiencia ferroviaria del país. Administradores: control total en tiempo real.
                        </p>
                        <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                            <div class="rounded-md shadow">
                                <a href="#"
                                    class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-white bg-blue-600 hover:bg-blue-700 md:py-4 md:text-lg md:px-10 transition-all hover:shadow-lg hover:shadow-blue-600/20">
                                    Comprar Boletos
                                </a>
                            </div>
                            <div class="mt-3 sm:mt-0 sm:ml-3">
                                <a href="#"
                                    class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-blue-700 bg-blue-100 hover:bg-blue-200 md:py-4 md:text-lg md:px-10 transition-all">
                                    Ver Horarios
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <!-- Imagen decorativa a la derecha (placeholder visual) -->
        <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 bg-gray-50 flex items-center justify-center">
            <div class="p-12 text-center">
                <div class="w-32 h-32 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-heroicon-o-ticket class="w-16 h-16 text-blue-600" />
                </div>
            </div>
        </div>
    </div>

    <!-- Buscador Rápido (Componente Flotante) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-20 -mt-10 mb-20 lg:-mt-16">
        <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-6 border border-gray-100">
            <form class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Origen</label>
                    <select
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 text-gray-600">
                        <option>Selecciona estación...</option>
                        <option>Estación Central</option>
                        <option>Estación Norte</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Destino</label>
                    <select
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 text-gray-600">
                        <option>Selecciona estación...</option>
                        <option>Terminal Sur</option>
                        <option>Valle Verde</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Viaje</label>
                    <input type="date" min="<?= date('Y-m-d') ?>"
                        class="w-full rounded-xl border border-gray-200 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 text-gray-600">
                </div>
                <div>
                    <button type="submit"
                        class="w-full flex items-center justify-center px-8 py-3.5 border border-transparent text-base font-medium rounded-xl text-white bg-gray-900 hover:bg-gray-800 transition-all">
                        Buscar Trenes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Características Rápidas -->
    <div class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    La forma más inteligente de viajar
                </p>
            </div>

            <div class="mt-16">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Card 1 -->
                    <div class="pt-6">
                        <div
                            class="flow-root bg-white rounded-3xl px-6 pb-8 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="-mt-6">
                                <div>
                                    <span
                                        class="inline-flex items-center justify-center p-3 bg-blue-600 rounded-2xl shadow-lg shadow-blue-500/30">
                                        <x-heroicon-o-ticket class="h-6 w-6 text-white" />
                                    </span>
                                </div>
                                <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Compra Fácil</h3>
                                <p class="mt-5 text-base text-gray-500">
                                    Elige tu destino, selecciona tu asiento en el vagón preferido y obtén tu ticket digital
                                    al instante.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="pt-6">
                        <div
                            class="flow-root bg-white rounded-3xl px-6 pb-8 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="-mt-6">
                                <div>
                                    <span
                                        class="inline-flex items-center justify-center p-3 bg-blue-600 rounded-2xl shadow-lg shadow-blue-500/30">
                                        <x-heroicon-o-map class="h-6 w-6 text-white" />
                                    </span>
                                </div>
                                <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Rastreo Interactivo</h3>
                                <p class="mt-5 text-base text-gray-500">
                                    Visualiza el trayecto del tren en tiempo real. Conoce estaciones, demoras y llegadas
                                    estimadas.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="pt-6">
                        <div
                            class="flow-root bg-white rounded-3xl px-6 pb-8 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                            <div class="-mt-6">
                                <div>
                                    <span
                                        class="inline-flex items-center justify-center p-3 bg-gray-900 rounded-2xl shadow-lg shadow-gray-500/30">
                                        <x-heroicon-o-adjustments-horizontal class="h-6 w-6 text-white" />
                                    </span>
                                </div>
                                <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Gestión Avanzada</h3>
                                <p class="mt-5 text-base text-gray-500">
                                    Herramientas para administradores: mover pasajeros de vagón, reasignar trenes y
                                    consultar manifiestos.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection