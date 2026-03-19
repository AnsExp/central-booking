@extends('layouts.app')

@section('content')
<div class="min-h-[70vh] flex flex-col items-center justify-center px-4 sm:px-6 lg:px-8 text-center">
    <!-- Icono decorativo minimalista -->
    <div class="w-20 h-20 bg-red-50 rounded-3xl flex items-center justify-center mb-8">
        <x-heroicon-o-lock-closed class="w-10 h-10 text-red-600" />
    </div>
    
    <!-- Texto principal -->
    <h1 class="text-9xl font-extrabold text-gray-900 tracking-tight mb-2">403</h1>
    <h2 class="text-3xl font-bold text-gray-800 tracking-tight mb-4">Acceso Denegado</h2>
    <p class="text-gray-500 max-w-md mx-auto mb-10 text-lg">
        Lo sentimos, no tienes los permisos necesarios para acceder a esta página o realizar esta acción.
    </p>
    
    <!-- Botones de acción consistentes -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center w-full max-w-sm">
        <a href="/" class="bg-blue-600 text-white hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/20 px-8 py-3.5 text-base font-medium transition-all rounded-full flex items-center justify-center gap-2 w-full sm:w-auto">
            <x-heroicon-o-arrow-left class="w-5 h-5" />
            Volver al inicio
        </a>
    </div>
</div>
@endsection