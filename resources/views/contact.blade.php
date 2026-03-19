@extends('layouts.app')

@section('content')
	<div class="bg-gray-50/60 py-14 sm:py-20">
		<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
				<div class="lg:col-span-5 space-y-6">
					<div class="inline-flex items-center gap-2 rounded-full bg-blue-100 text-blue-700 px-4 py-1.5 text-sm font-medium">
						<x-heroicon-o-chat-bubble-left-right class="h-4 w-4" />
						Soporte Central Booking
					</div>

					<h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900 leading-tight">
						Estamos aquí para ayudarte con tu viaje
					</h1>

					<p class="text-gray-600 text-lg max-w-xl">
						Escríbenos cualquier duda sobre boletos, horarios, cambios o reembolsos. Nuestro equipo te
						responde en menos de 24 horas.
					</p>

					<div class="space-y-4 pt-2">
						<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-start gap-3">
							<span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white">
								<x-heroicon-o-envelope class="h-5 w-5" />
							</span>
							<div>
								<p class="text-sm text-gray-500">Correo</p>
								<p class="text-gray-900 font-semibold">soporte@centralbooking.com</p>
							</div>
						</div>

						<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-start gap-3">
							<span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gray-900 text-white">
								<x-heroicon-o-phone class="h-5 w-5" />
							</span>
							<div>
								<p class="text-sm text-gray-500">Teléfono</p>
								<p class="text-gray-900 font-semibold">+52 55 1234 5678</p>
							</div>
						</div>

						<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-start gap-3">
							<span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
								<x-heroicon-o-clock class="h-5 w-5" />
							</span>
							<div>
								<p class="text-sm text-gray-500">Horario</p>
								<p class="text-gray-900 font-semibold">Lunes a Domingo, 08:00 - 20:00</p>
							</div>
						</div>
					</div>
				</div>

				<div class="lg:col-span-7">
					<div class="bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-200/50 p-6 sm:p-8">
						<h2 class="text-2xl font-bold text-gray-900">Envíanos un mensaje</h2>
						<p class="mt-2 text-sm text-gray-500">Te contactaremos lo antes posible.</p>

						<form class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-5" action="/contact" method="POST">
							@csrf

							<div class="sm:col-span-1">
								<label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
								<input type="text" id="name" name="name" placeholder="Tu nombre" required
									class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-700 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
							</div>

							<div class="sm:col-span-1">
								<label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo *</label>
								<input type="email" id="email" name="email" placeholder="tu@correo.com" required
									class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-700 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
							</div>

							<div class="sm:col-span-1">
								<label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Asunto *</label>
								<select id="subject" name="subject" required
									class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-700 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
									<option>Consulta general</option>
									<option>Compra de boletos</option>
									<option>Cambios y reembolsos</option>
									<option>Soporte técnico</option>
								</select>
							</div>

							<div class="sm:col-span-1">
								<label for="ticket" class="block text-sm font-medium text-gray-700 mb-1">Número de reserva</label>
								<input type="text" id="ticket" name="ticket" placeholder="Opcional"
									class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-700 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10">
							</div>

							<div class="sm:col-span-2">
								<label for="message" class="block text-sm font-medium text-gray-700 mb-1">Mensaje *</label>
								<textarea id="message" name="message" rows="6" placeholder="Cuéntanos cómo podemos ayudarte..." required
									class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-700 placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"></textarea>
							</div>

							<div class="sm:col-span-2 flex items-center justify-between gap-4 flex-wrap">
								<p class="text-sm text-gray-500">Al enviar aceptas nuestros términos y <a href="{{ route('privacy-policy') }}" class="text-blue-600 hover:text-blue-700">política de privacidad</a>.</p>
								<button type="submit"
									class="inline-flex items-center gap-2 rounded-full bg-blue-600 text-white px-7 py-3.5 text-sm font-semibold shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition-all">
									<x-heroicon-o-paper-airplane class="h-5 w-5" />
									Enviar mensaje
								</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection