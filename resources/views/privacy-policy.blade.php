@extends('layouts.app')

@section('content')
	<div class="bg-gray-50/60 py-14 sm:py-20">
		<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
			<div class="text-center mb-10">
				<div
					class="inline-flex items-center gap-2 rounded-full bg-blue-100 text-blue-700 px-4 py-1.5 text-sm font-medium">
					<x-heroicon-o-shield-check class="h-4 w-4" />
					Central Booking
				</div>

				<h1 class="mt-4 text-4xl sm:text-5xl font-extrabold tracking-tight text-gray-900">
					Política de Privacidad
				</h1>
				<p class="mt-4 text-gray-600 text-lg max-w-3xl mx-auto">
					En Central Booking protegemos tu información personal y la tratamos con transparencia.
					Esta política explica qué datos recopilamos, para qué los usamos y cómo puedes ejercer tus derechos.
				</p>
				<p class="mt-3 text-sm text-gray-500">
					Última actualización: {{ now()->format('d/m/Y') }}
				</p>
			</div>

			<div class="bg-white rounded-3xl border border-gray-100 shadow-xl shadow-gray-200/50 p-6 sm:p-8 lg:p-10 space-y-8">
				<section>
					<h2 class="text-2xl font-bold text-gray-900">1. Información que recopilamos</h2>
					<p class="mt-3 text-gray-600 leading-relaxed">
						Podemos recopilar datos de identificación y contacto, como nombre, correo electrónico, teléfono,
						información de reservas, preferencias de viaje y datos técnicos básicos de navegación.
					</p>
				</section>

				<section>
					<h2 class="text-2xl font-bold text-gray-900">2. Uso de la información</h2>
					<p class="mt-3 text-gray-600 leading-relaxed">
						Usamos tus datos para gestionar reservas, brindar soporte, enviarte notificaciones de viaje,
						mejorar la experiencia de la plataforma y cumplir obligaciones legales o regulatorias.
					</p>
				</section>

				<section>
					<h2 class="text-2xl font-bold text-gray-900">3. Base legal y consentimiento</h2>
					<p class="mt-3 text-gray-600 leading-relaxed">
						Tratamos tus datos cuando es necesario para ejecutar el servicio solicitado, cumplir obligaciones
						legales o cuando has otorgado tu consentimiento para finalidades específicas.
					</p>
				</section>

				<section>
					<h2 class="text-2xl font-bold text-gray-900">4. Compartición de datos</h2>
					<p class="mt-3 text-gray-600 leading-relaxed">
						Solo compartimos información con proveedores tecnológicos o aliados necesarios para operar la
						plataforma, bajo acuerdos de confidencialidad y medidas de seguridad adecuadas.
					</p>
				</section>

				<section>
					<h2 class="text-2xl font-bold text-gray-900">5. Conservación y seguridad</h2>
					<p class="mt-3 text-gray-600 leading-relaxed">
						Conservamos tus datos durante el tiempo estrictamente necesario y aplicamos controles técnicos y
						organizativos razonables para protegerlos contra acceso no autorizado, pérdida o alteración.
					</p>
				</section>

				<section>
					<h2 class="text-2xl font-bold text-gray-900">6. Tus derechos</h2>
					<p class="mt-3 text-gray-600 leading-relaxed">
						Puedes solicitar acceso, rectificación, actualización, oposición o eliminación de tus datos,
						así como revocar consentimientos cuando corresponda. Atendemos solicitudes por los canales de contacto.
					</p>
				</section>

				<section>
					<h2 class="text-2xl font-bold text-gray-900">7. Cookies y tecnologías similares</h2>
					<p class="mt-3 text-gray-600 leading-relaxed">
						Utilizamos cookies esenciales para el funcionamiento de la plataforma y, en algunos casos,
						cookies analíticas para mejorar el servicio. Puedes ajustar estas preferencias desde tu navegador.
					</p>
				</section>

				<section>
					<h2 class="text-2xl font-bold text-gray-900">8. Contacto</h2>
					<p class="mt-3 text-gray-600 leading-relaxed">
						Si tienes preguntas sobre esta política o sobre el tratamiento de tus datos,
						puedes escribirnos a soporte@centralbooking.com o visitar nuestra página de contacto.
					</p>
				</section>

				<div class="rounded-2xl bg-blue-50 border border-blue-100 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
					<p class="text-blue-800 text-sm sm:text-base">
						Al usar Central Booking aceptas las prácticas descritas en esta Política de Privacidad.
					</p>
					<a href="{{ route('contact') }}"
						class="inline-flex items-center justify-center gap-2 rounded-full bg-blue-600 text-white px-6 py-3 text-sm font-semibold hover:bg-blue-700 transition-all shadow-lg shadow-blue-600/20">
						<x-heroicon-o-chat-bubble-left-right class="h-5 w-5" />
						Contactar soporte
					</a>
				</div>
			</div>
		</div>
	</div>
@endsection