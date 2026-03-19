<nav class="bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
            <!-- Logo -->
            <div class="flex-shrink-0 flex items-center">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                    Central Booking
                </a>
            </div>

            <!-- Navegación Principal -->
            <div class="hidden sm:flex">
                <a href="{{ route('home') }}" class="text-gray-600 px-4 py-2 rounded-full text-sm font-medium transition-all hover:bg-gray-100 hover:text-gray-900">
                    Inicio
                </a>
                <a href="#" class="text-gray-600 px-4 py-2 rounded-full text-sm font-medium transition-all hover:bg-gray-100 hover:text-gray-900">
                    Destinos
                </a>
                <a href="#" class="text-gray-600 px-4 py-2 rounded-full text-sm font-medium transition-all hover:bg-gray-100 hover:text-gray-900">
                    Servicios
                </a>
                <a href="{{ route('contact') }}" class="text-gray-600 px-4 py-2 rounded-full text-sm font-medium transition-all hover:bg-gray-100 hover:text-gray-900">
                    Contacto
                </a>
            </div>

            <!-- Botones de Acción -->
            <div class="hidden sm:flex sm:items-center sm:space-x-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 px-4 py-2 rounded-full text-sm font-medium transition-all hover:bg-gray-100">
                        Dashboard
                    </a>
                    <span class="text-sm text-gray-700 px-3 py-2 rounded-full bg-gray-100">
                        {{ auth()->user()->email }}
                    </span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-gray-900 px-4 py-2 rounded-full text-sm font-medium transition-all hover:bg-gray-100">
                            Cerrar sesión
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 px-4 py-2 rounded-full text-sm font-medium transition-all hover:bg-gray-100">
                        Entrar
                    </a>
                @endauth
            </div>

            <!-- Botón menú móvil -->
            <button type="button" class="sm:hidden inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-all"
                aria-label="Abrir menú" aria-expanded="false"
                onclick="const menu=document.getElementById('mobile-menu'); const expanded=this.getAttribute('aria-expanded')==='true'; menu.classList.toggle('hidden'); this.setAttribute('aria-expanded', (!expanded).toString());">
                <x-heroicon-o-bars-3 class="h-5 w-5" />
            </button>
        </div>

        <!-- Menú móvil -->
        <div id="mobile-menu" class="sm:hidden hidden pb-5 border-t border-gray-100">
            <div class="pt-4 flex flex-col gap-2">
                <a href="/" class="text-gray-700 px-4 py-3 rounded-2xl text-sm font-medium transition-all hover:bg-gray-100">
                    Inicio
                </a>
                <a href="#" class="text-gray-700 px-4 py-3 rounded-2xl text-sm font-medium transition-all hover:bg-gray-100">
                    Destinos
                </a>
                <a href="#" class="text-gray-700 px-4 py-3 rounded-2xl text-sm font-medium transition-all hover:bg-gray-100">
                    Servicios
                </a>
                <a href="{{ route('contact') }}" class="text-gray-700 px-4 py-3 rounded-2xl text-sm font-medium transition-all hover:bg-gray-100">
                    Contacto
                </a>
                <a href="{{ route('privacy-policy') }}" class="text-gray-700 px-4 py-3 rounded-2xl text-sm font-medium transition-all hover:bg-gray-100">
                    Privacidad
                </a>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100">
                @auth
                    <a href="{{ route('dashboard') }}" class="block text-gray-700 px-4 py-3 rounded-2xl text-sm font-medium transition-all hover:bg-gray-100">
                        Dashboard
                    </a>
                    <div class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-2xl">
                        {{ auth()->user()->email }}
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full text-left text-gray-700 px-4 py-3 rounded-2xl text-sm font-medium transition-all hover:bg-gray-100">
                            Cerrar sesión
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block text-gray-700 px-4 py-3 rounded-2xl text-sm font-medium transition-all hover:bg-gray-100">
                        Entrar
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>