@extends('layouts.app')

@section('content')
	<div class="bg-gray-50/60 py-14 sm:py-20">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
				<div class="lg:col-span-7">
					<div class="inline-flex items-center gap-2 rounded-full bg-blue-100 text-blue-700 px-4 py-1.5 text-sm font-medium">
						<x-heroicon-o-building-office-2 class="h-4 w-4" />
						Sobre Central Booking
					</div>

					<h1 class="mt-5 text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 leading-tight">
						Conectamos personas con destinos de forma simple y confiable
					</h1>

					<p class="mt-5 text-lg text-gray-600 max-w-3xl">
						Central Booking nació para modernizar la experiencia ferroviaria: compra de boletos en minutos,
						gestión de viajes en tiempo real y una plataforma pensada tanto para pasajeros como para equipos de operación.
					</p>

					<div class="mt-8 flex flex-wrap gap-3">
						<a href="{{ route('contact') }}"
							class="inline-flex items-center gap-2 rounded-full bg-blue-600 text-white px-6 py-3 text-sm font-semibold hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20">
							<x-heroicon-o-chat-bubble-left-right class="h-5 w-5" />
							Contáctanos
						</a>
						<a href="/"
							class="inline-flex items-center gap-2 rounded-full bg-gray-100 text-gray-800 px-6 py-3 text-sm font-semibold hover:bg-gray-200 transition-all">
							<x-heroicon-o-home class="h-5 w-5" />
							Volver al inicio
						</a>
					</div>
				</div>

				<div class="lg:col-span-5">
					<div class="bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-200/50 p-7 space-y-5">
						<h2 class="text-2xl font-bold text-gray-900">Nuestra misión</h2>
						<p class="text-gray-600 leading-relaxed">
							Ofrecer una movilidad ferroviaria más eficiente, transparente y humana, con tecnología que simplifica cada etapa del viaje.
						</p>
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
							<div class="rounded-2xl bg-blue-50 border border-blue-100 p-4">
								<p class="text-2xl font-extrabold text-blue-700">+250K</p>
								<p class="text-sm text-blue-800">Boletos gestionados</p>
							</div>
							<div class="rounded-2xl bg-gray-50 border border-gray-100 p-4">
								<p class="text-2xl font-extrabold text-gray-800">24/7</p>
								<p class="text-sm text-gray-600">Monitoreo operativo</p>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="mt-14 grid grid-cols-1 md:grid-cols-3 gap-6">
				<div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
					<span class="inline-flex items-center justify-center h-11 w-11 rounded-xl bg-blue-600 text-white">
						<x-heroicon-o-bolt class="h-5 w-5" />
					</span>
					<h3 class="mt-4 text-lg font-bold text-gray-900">Velocidad</h3>
					<p class="mt-2 text-gray-600">
						Procesos ágiles para reservar, modificar y consultar viajes sin fricción.
					</p>
				</div>

				<div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
					<span class="inline-flex items-center justify-center h-11 w-11 rounded-xl bg-gray-900 text-white">
						<x-heroicon-o-shield-check class="h-5 w-5" />
					</span>
					<h3 class="mt-4 text-lg font-bold text-gray-900">Seguridad</h3>
					<p class="mt-2 text-gray-600">
						Protección de datos, sesiones seguras y controles de acceso para operar con confianza.
					</p>
				</div>

				<div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
					<span class="inline-flex items-center justify-center h-11 w-11 rounded-xl bg-blue-100 text-blue-700">
						<x-heroicon-o-heart class="h-5 w-5" />
					</span>
					<h3 class="mt-4 text-lg font-bold text-gray-900">Experiencia</h3>
					<p class="mt-2 text-gray-600">
						Diseñamos cada flujo pensando en pasajeros, personal y administradores.
					</p>
				</div>
			</div>

			<div class="mt-14 bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8">
				<h2 class="text-2xl font-bold text-gray-900">Nuestros valores</h2>
				<div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-5">
					<div class="rounded-2xl border border-gray-100 p-5">
						<h3 class="font-semibold text-gray-900">Transparencia</h3>
						<p class="mt-2 text-gray-600">Información clara sobre horarios, disponibilidad y estado de servicio.</p>
					</div>
					<div class="rounded-2xl border border-gray-100 p-5">
						<h3 class="font-semibold text-gray-900">Innovación útil</h3>
						<p class="mt-2 text-gray-600">Tecnología que resuelve problemas reales y mejora la operación diaria.</p>
					</div>
					<div class="rounded-2xl border border-gray-100 p-5">
						<h3 class="font-semibold text-gray-900">Compromiso</h3>
						<p class="mt-2 text-gray-600">Atención cercana y soporte continuo para usuarios y equipos internos.</p>
					</div>
					<div class="rounded-2xl border border-gray-100 p-5">
						<h3 class="font-semibold text-gray-900">Confiabilidad</h3>
						<p class="mt-2 text-gray-600">Procesos robustos para que cada viaje esté respaldado de principio a fin.</p>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection