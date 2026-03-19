@extends('layouts.app')

@section('content')
	<div class="relative overflow-hidden bg-white py-14 sm:py-18">
		<div class="absolute inset-0">
			<div class="absolute -top-24 -left-20 h-72 w-72 rounded-full bg-cyan-500/20 blur-3xl"></div>
			<div class="absolute -bottom-20 right-0 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
		</div>

		<div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
			<div
				class="rounded-3xl border border-slate-200 bg-gradient-to-r from-cyan-50 via-white to-indigo-50 p-6 sm:p-8 lg:p-10 text-slate-900 shadow-sm">
				<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
					<div>
						<div
							class="inline-flex items-center gap-2 rounded-full bg-cyan-100 text-cyan-800 px-4 py-1.5 text-sm font-medium border border-cyan-200">
							<x-heroicon-o-chart-bar-square class="h-4 w-4" />
							Panel Ejecutivo
						</div>
						<h1 class="mt-4 text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
							Dashboard de Gestión del Sistema
						</h1>
						<p class="mt-4 text-slate-600 max-w-3xl text-base sm:text-lg">
							Visión centralizada de rendimiento operativo, estado de catálogos y accesos administrativos.
							Contenido temporal en modo placeholder.
						</p>
					</div>
					<div class="flex flex-col sm:flex-row gap-3">
						<button type="button"
							class="inline-flex items-center justify-center rounded-full bg-slate-900 text-white px-6 py-3 text-sm font-semibold hover:bg-slate-800 transition-all">
							Exportar reporte
						</button>
						<button type="button"
							class="inline-flex items-center justify-center rounded-full border border-slate-300 text-slate-700 px-6 py-3 text-sm font-semibold hover:bg-slate-100 transition-all">
							Configurar vista
						</button>
					</div>
				</div>
			</div>

			<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
				<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<p class="text-sm font-medium text-slate-500">Estaciones registradas</p>
					<p class="mt-2 text-3xl font-extrabold text-slate-900">42</p>
					<div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden">
						<div class="h-full w-3/4 bg-cyan-500"></div>
					</div>
					<p class="mt-2 text-xs text-slate-500">Cobertura de red: 75% (placeholder)</p>
				</div>
				<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<p class="text-sm font-medium text-slate-500">Rutas activas</p>
					<p class="mt-2 text-3xl font-extrabold text-slate-900">18</p>
					<div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden">
						<div class="h-full w-2/3 bg-emerald-500"></div>
					</div>
					<p class="mt-2 text-xs text-slate-500">Disponibilidad: 66% (placeholder)</p>
				</div>
				<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<p class="text-sm font-medium text-slate-500">Trenes registrados</p>
					<p class="mt-2 text-3xl font-extrabold text-slate-900">27</p>
					<div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden">
						<div class="h-full w-4/5 bg-indigo-500"></div>
					</div>
					<p class="mt-2 text-xs text-slate-500">Operativos: 80% (placeholder)</p>
				</div>
				<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
					<p class="text-sm font-medium text-slate-500">Servicios adicionales</p>
					<p class="mt-2 text-3xl font-extrabold text-slate-900">33</p>
					<div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden">
						<div class="h-full w-5/6 bg-violet-500"></div>
					</div>
					<p class="mt-2 text-xs text-slate-500">Catálogo actualizado: 83% (placeholder)</p>
				</div>
			</div>

			<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
				<div class="xl:col-span-2 rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
					<div class="flex items-center justify-between gap-3 flex-wrap">
						<div>
							<h2 class="text-2xl font-bold text-slate-900">Gestión del sistema</h2>
							<p class="mt-2 text-sm text-slate-500">Accesos directos a módulos críticos del negocio.</p>
						</div>
						<span class="rounded-full bg-slate-100 text-slate-700 px-3 py-1 text-xs font-semibold">Vista
							comercial</span>
					</div>

					<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
						<a href="{{ route('stations.view') }}">
							<div
								class="group rounded-2xl border border-slate-200 p-5 hover:border-cyan-300 hover:shadow-md transition-all">
								<div class="flex items-center justify-between">
									<h3 class="font-semibold text-slate-900">Estaciones</h3>
									<x-heroicon-o-map-pin class="w-5 h-5 text-slate-400 group-hover:text-cyan-600" />
								</div>
								<p class="mt-2 text-sm text-slate-600">Catálogo de nodos ferroviarios y su
									disponibilidad.</p>
							</div>
						</a>
						<a href="{{ route('routes.view') }}">
							<div
								class="group rounded-2xl border border-slate-200 p-5 hover:border-cyan-300 hover:shadow-md transition-all">
								<div class="flex items-center justify-between">
									<h3 class="font-semibold text-slate-900">Rutas</h3>
									<x-heroicon-o-arrows-right-left
										class="w-5 h-5 text-slate-400 group-hover:text-cyan-600" />
								</div>
								<p class="mt-2 text-sm text-slate-600">Planificación de trayectos y ventanas operativas.</p>
							</div>
						</a>
						<a href="{{ route('trains.view') }}">
							<div
								class="group rounded-2xl border border-slate-200 p-5 hover:border-cyan-300 hover:shadow-md transition-all">
								<div class="flex items-center justify-between">
									<h3 class="font-semibold text-slate-900">Trenes</h3>
									<x-heroicon-o-truck class="w-5 h-5 text-slate-400 group-hover:text-cyan-600" />
								</div>
								<p class="mt-2 text-sm text-slate-600">Control de flota, capacidad y estado técnico.</p>
							</div>
						</a>
						<a href="{{ route('services.view') }}">
							<div
								class="group rounded-2xl border border-slate-200 p-5 hover:border-cyan-300 hover:shadow-md transition-all">
								<div class="flex items-center justify-between">
									<h3 class="font-semibold text-slate-900">Servicios</h3>
									<x-heroicon-o-wrench-screwdriver
										class="w-5 h-5 text-slate-400 group-hover:text-cyan-600" />
								</div>
								<p class="mt-2 text-sm text-slate-600">Ofertas a bordo y tarifas adicionales.</p>
							</div>
						</a>
					</div>
				</div>

				<div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
					<h2 class="text-xl font-bold text-slate-900">Actividad reciente</h2>
					<p class="mt-2 text-sm text-slate-500">Eventos clave para seguimiento ejecutivo.</p>
					<ul class="mt-5 space-y-3 text-sm text-slate-700">
						<li class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3">[Placeholder] Se registró un
							nuevo tren de pasajeros.</li>
						<li class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3">[Placeholder] Ajuste de precio
							en servicios premium.</li>
						<li class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3">[Placeholder] Alta de estación
							en zona norte.</li>
						<li class="rounded-xl bg-slate-50 border border-slate-100 px-4 py-3">[Placeholder] Validación
							semanal de rutas activas.</li>
					</ul>
				</div>
			</div>
		</div>
	</div>

@endsection