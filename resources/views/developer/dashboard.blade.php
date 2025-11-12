@extends('layouts.app')

@section('title', 'Panel de Desarrollador')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg shadow-lg p-6 mb-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">👨‍💻 Panel de Desarrollador</h1>
                    <p class="text-purple-100">Herramientas y configuración del sistema</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-purple-200">Bienvenido, {{ auth()->user()->name }}</p>
                    <p class="text-xs text-purple-300">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>

        <!-- System Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <!-- PHP Version -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-600">PHP Version</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ $info['php_version'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Laravel Version -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-600">Laravel</h3>
                        <p class="text-2xl font-bold text-gray-900">{{ $info['laravel_version'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Environment -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-600">Environment</h3>
                        <p class="text-2xl font-bold text-gray-900 uppercase">{{ $info['environment'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Database -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-600">Database</h3>
                        <p class="text-xl font-bold text-gray-900">{{ $info['database'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Cache Driver -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-600">Cache Driver</h3>
                        <p class="text-xl font-bold text-gray-900 uppercase">{{ $info['cache_driver'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Queue Driver -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-600">Queue Driver</h3>
                        <p class="text-xl font-bold text-gray-900 uppercase">{{ $info['queue_driver'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">🔧 Acciones Rápidas</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- View Logs -->
                <a href="{{ route('developer.logs') }}" class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                    <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-gray-900">Ver Logs</h3>
                        <p class="text-xs text-gray-600">Sistema</p>
                    </div>
                </a>

                <!-- Clear Cache -->
                <form action="{{ route('developer.clear-cache') }}" method="POST" class="block">
                    @csrf
                    <button type="submit" class="w-full flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition">
                        <svg class="w-8 h-8 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <div class="text-left">
                            <h3 class="font-semibold text-gray-900">Limpiar Cache</h3>
                            <p class="text-xs text-gray-600">Sistema</p>
                        </div>
                    </button>
                </form>

                <!-- View Routes -->
                <a href="{{ route('developer.routes') }}" class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                    <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-gray-900">Ver Rutas</h3>
                        <p class="text-xs text-gray-600">Sistema</p>
                    </div>
                </a>

                <!-- View Config -->
                <a href="{{ route('developer.config') }}" class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                    <svg class="w-8 h-8 text-purple-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-gray-900">Configuración</h3>
                        <p class="text-xs text-gray-600">Sistema</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">🔗 Enlaces Útiles</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('home') }}" class="p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 transition">
                    <h3 class="font-semibold text-gray-900 mb-1">🏠 Inicio</h3>
                    <p class="text-sm text-gray-600">Volver a la página principal</p>
                </a>
                
                <a href="{{ route('comparadora') }}" class="p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 transition">
                    <h3 class="font-semibold text-gray-900 mb-1">🔍 Comparadora</h3>
                    <p class="text-sm text-gray-600">Comparar periféricos</p>
                </a>
                
                <a href="{{ route('perfil') }}" class="p-4 border-2 border-gray-200 rounded-lg hover:border-purple-500 transition">
                    <h3 class="font-semibold text-gray-900 mb-1">👤 Perfil</h3>
                    <p class="text-sm text-gray-600">Ver tu perfil</p>
                </a>
            </div>
        </div>

        <!-- Logout -->
        <div class="mt-6 text-center">
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold">
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </div>
</div>

@if(session('success'))
<div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg">
    {{ session('error') }}
</div>
@endif

@endsection
