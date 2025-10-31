@extends('layouts.app')

@section('title', 'Acceso Administrativo - CompareWare')

@section('content')
<div class="px-10 flex flex-1 justify-center py-5">
    <div class="layout-content-container flex flex-col max-w-[800px] flex-1">
        
        <div class="text-center py-10">
            <h1 class="text-4xl font-bold text-gray-900 mb-6">🔧 Área Administrativa</h1>
            
            @auth
                @if(Auth::user()->role === 'admin')
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        <p class="font-bold">✅ Acceso Autorizado</p>
                        <p>Bienvenido, {{ Auth::user()->name }}. Tienes permisos de administrador.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Gestión Directa -->
                        <a href="{{ route('admin.users') }}" class="bg-purple-600 hover:bg-purple-700 text-white p-8 rounded-lg transition-colors block">
                            <div class="text-4xl mb-4">🎛️</div>
                            <h2 class="text-xl font-bold mb-2">Panel Admin</h2>
                            <p class="text-purple-100">Acceso directo a gestión completa</p>
                        </a>
                        
                        <!-- Gestión de Usuarios -->
                        <a href="{{ route('admin.users') }}" class="bg-blue-600 hover:bg-blue-700 text-white p-8 rounded-lg transition-colors block">
                            <div class="text-4xl mb-4">👥</div>
                            <h2 class="text-xl font-bold mb-2">Usuarios</h2>
                            <p class="text-blue-100">Gestionar usuarios, roles y permisos</p>
                        </a>
                    </div>
                    
                    <div class="mt-8">
                        <p class="text-gray-600 mb-4">También puedes acceder directamente con estas URLs:</p>
                        <div class="bg-gray-100 p-4 rounded-lg text-left">
                            <p class="font-mono text-sm mb-2">🎛️ Panel Principal: <code class="bg-white px-2 py-1 rounded">{{ url('/admin/users') }}</code></p>
                            <p class="font-mono text-sm mb-2">👥 Gestión Usuarios: <code class="bg-white px-2 py-1 rounded">{{ url('/admin/users') }}</code></p>
                            <p class="font-mono text-sm">🚀 Acceso Directo: <code class="bg-white px-2 py-1 rounded">{{ url('/panel-admin') }}</code></p>
                        </div>
                    </div>
                    
                @else
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <p class="font-bold">❌ Acceso Denegado</p>
                        <p>No tienes permisos de administrador. Tu rol actual es: <strong>{{ Auth::user()->role }}</strong></p>
                    </div>
                    
                    <a href="{{ route('home') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg inline-block">
                        ← Volver al Inicio
                    </a>
                @endif
                
            @else
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                    <p class="font-bold">🔒 Acceso Restringido</p>
                    <p>Debes iniciar sesión para acceder al área administrativa.</p>
                </div>
                
                <a href="{{ route('login') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg inline-block">
                    Iniciar Sesión
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection