<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Restaurante Premium'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/restaurant-map.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .pulse-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; animation: pulse 2s infinite; }
        .pulse-dot.green { background: #10b981; }
        .pulse-dot.gold { background: #f59e0b; }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.3); }
        }
    </style>
    @yield('styles')
</head>
<body class="font-sans antialiased bg-slate-900 text-slate-100 min-h-screen selection:bg-rose-500 selection:text-white">
    
    <!-- Navbar -->
    <nav class="glass sticky top-0 z-50 border-b border-slate-700/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ Auth::check() && Auth::user()->hasRole('admin') ? route('admin.dashboard') : route('cliente.mapa') }}" class="flex items-center gap-2 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-orange-400 flex items-center justify-center shadow-lg shadow-rose-500/20 group-hover:shadow-rose-500/40 transition-all duration-300">
                            <i class="fas fa-utensils text-white"></i>
                        </div>
                        <span class="font-bold text-xl tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-rose-400 to-orange-300">
                            ReservaPremium
                        </span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:flex sm:items-center sm:space-x-8">
                    @if(Auth::check())
                        @if(Auth::user()->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.dashboard') ? 'border-rose-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-300' }} text-sm font-medium transition-colors">
                                <i class="fas fa-chart-pie mr-2"></i> Dashboard
                            </a>
                            <a href="{{ route('admin.mesas.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.mesas.*') ? 'border-rose-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-300' }} text-sm font-medium transition-colors">
                                <i class="fas fa-chair mr-2"></i> Mesas
                            </a>
                            <a href="{{ route('admin.reservas.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.reservas.*') ? 'border-rose-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-300' }} text-sm font-medium transition-colors">
                                <i class="fas fa-calendar-check mr-2"></i> Reservas
                            </a>
                            <a href="{{ route('admin.pedidos.live') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.pedidos.live') ? 'border-rose-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-300' }} text-sm font-medium transition-colors">
                                <i class="fas fa-fire-burner mr-2 text-rose-400"></i> Cocina (KDS)
                            </a>
                        @else
                            <a href="{{ route('cliente.mapa') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('cliente.mapa') ? 'border-rose-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-300' }} text-sm font-medium transition-colors">
                                <i class="fas fa-map-marked-alt mr-2"></i> Mapa
                            </a>
                            <a href="{{ route('cliente.mis-reservas') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('cliente.mis-reservas') ? 'border-rose-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-300' }} text-sm font-medium transition-colors">
                                <i class="fas fa-calendar-alt mr-2"></i> Mis Reservas
                            </a>
                            <a href="{{ route('cliente.menu') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('cliente.menu') ? 'border-rose-500 text-white' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-300' }} text-sm font-medium transition-colors">
                                <i class="fas fa-utensils mr-2 text-rose-400"></i> Menú y Pedidos
                            </a>
                        @endif
                    @endif
                </div>

                <!-- User Dropdown -->
                <div class="hidden sm:flex sm:items-center sm:ml-6">
                    @if(Auth::check())
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                            <button @click="open = ! open" class="flex items-center gap-2 text-sm font-medium text-slate-300 hover:text-white focus:outline-none transition-colors">
                                <img class="h-8 w-8 rounded-full object-cover border border-slate-600" src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=f43f5e&background=1e293b" alt="{{ Auth::user()->name }}">
                                <span>{{ Auth::user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs ml-1 transition-transform duration-200" :class="{'rotate-180': open}"></i>
                            </button>

                            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 rounded-xl shadow-xl bg-slate-800 border border-slate-700 py-1 z-50" style="display: none;">
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                                    <i class="fas fa-user mr-2 text-slate-400"></i> Mi Perfil
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition-colors">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-slate-300 hover:text-white text-sm font-medium mr-4">Iniciar Sesión</a>
                        <a href="{{ route('register') }}" class="bg-rose-500 hover:bg-rose-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-lg shadow-rose-500/30">Registrarse</a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        @if(session('success'))
            <div class="mb-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-xl flex items-center gap-3 animate-fade-in">
                <i class="fas fa-check-circle text-lg"></i>
                <p class="font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-rose-500/10 border border-rose-500/20 text-rose-400 px-4 py-3 rounded-xl flex items-start gap-3 animate-fade-in">
                <i class="fas fa-exclamation-circle text-lg mt-0.5"></i>
                <div>
                    @foreach($errors->all() as $error)
                        <p class="font-medium">{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
