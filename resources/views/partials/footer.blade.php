<footer class="bg-gray-50 border-t border-gray-100 py-12 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Brand -->
            <div class="col-span-1 md:col-span-1">
                <a href="/" class="text-xl font-bold text-gray-900 tracking-tight flex items-center gap-2 mb-4">
                    Central Booking
                </a>
                <p class="text-gray-500 text-sm">
                    Tu próximo destino a un clic de distancia. Encuentra los mejores lugares para hospedarte.
                </p>
            </div>

            <!-- Links -->
            <div>
                <h3 class="text-gray-900 font-semibold mb-4 text-sm uppercase tracking-wider">Empresa</h3>
                <ul class="space-y-3">
                    <li><a href="{{ route('about') }}" class="text-gray-500 hover:text-blue-600 text-sm transition-colors">Sobre nosotros</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-blue-600 text-sm transition-colors">Carreras</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-blue-600 text-sm transition-colors">Prensa</a></li>
                </ul>
            </div>

            <!-- Servicios -->
            <div>
                <h3 class="text-gray-900 font-semibold mb-4 text-sm uppercase tracking-wider">Servicios</h3>
                <ul class="space-y-3">
                    <li><a href="#" class="text-gray-500 hover:text-blue-600 text-sm transition-colors">Destinos</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-blue-600 text-sm transition-colors">Ofertas</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-blue-600 text-sm transition-colors">Guías de viaje</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h3 class="text-gray-900 font-semibold mb-4 text-sm uppercase tracking-wider">Soporte</h3>
                <ul class="space-y-3">
                    <li><a href="#" class="text-gray-500 hover:text-blue-600 text-sm transition-colors">Centro de ayuda</a></li>
                    <li><a href="#" class="text-gray-500 hover:text-blue-600 text-sm transition-colors">Términos de servicio</a></li>
                    <li><a href="{{ route('privacy-policy') }}" class="text-gray-500 hover:text-blue-600 text-sm transition-colors">Privacidad</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-gray-200">
            <p class="text-gray-400 text-sm text-center">
                &copy; {{ date('Y') }} Central Booking. Todos los derechos reservados.
            </p>
        </div>
    </div>
</footer>
