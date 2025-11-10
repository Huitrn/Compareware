@extends('layouts.app')

@section('title', 'Gestión de Usuarios - CompareWare')

@section('content')
<div class="px-10 flex flex-1 justify-center py-5">
    <div class="layout-content-container flex flex-col max-w-[1400px] flex-1">
        
        <!-- Mensajes de éxito/error -->
        @if(session('success'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 dark:border-green-400 p-4 rounded-lg shadow-sm animate-fade-in">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-500 dark:text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-green-800 dark:text-green-200 font-medium">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 dark:border-red-400 p-4 rounded-lg shadow-sm animate-fade-in">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-500 dark:text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-red-800 dark:text-red-200 font-medium">{{ session('error') }}</p>
            </div>
        </div>
        @endif
        
        <!-- Breadcrumb y Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <nav class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400 mb-2">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Dashboard</a>
                    <span>/</span>
                    <span class="text-gray-900 dark:text-gray-100 font-medium">Gestión de Usuarios</span>
                </nav>
                <h1 class="text-[#0d141c] dark:text-white text-[32px] font-bold leading-tight">Gestión de Usuarios</h1>
                <p class="text-gray-600 dark:text-gray-400">Administra usuarios, roles y permisos del sistema</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600 dark:text-gray-400">Total de usuarios</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $users->total() }}</p>
            </div>
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <form method="GET" action="{{ route('admin.users') }}">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar usuario</label>
                        <input type="text" 
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Nombre, email..." 
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Filtrar por rol</label>
                        <select name="role_id" class="px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all" {{ request('role_id') === 'all' || !request('role_id') ? 'selected' : '' }}>Todos los roles</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->nombre }}
                                </option>
                            @endforeach
                            <option value="null" {{ request('role_id') === 'null' ? 'selected' : '' }}>Sin rol asignado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                        <select name="status" class="px-4 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="" {{ !request('status') ? 'selected' : '' }}>Todos los estados</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendidos</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="bg-blue-600 dark:bg-blue-700 text-white px-6 py-2 rounded-lg hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors">
                            🔍 Filtrar
                        </button>
                        @if(request()->hasAny(['search', 'role', 'status']))
                            <a href="{{ route('admin.users') }}" class="bg-gray-500 dark:bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-600 dark:hover:bg-gray-500 transition-colors text-center">
                                🔄 Limpiar
                            </a>
                        @endif
                    </div>
                </div>
            </form>
            
            <!-- Indicadores de filtros activos -->
            @if(request()->hasAny(['search', 'role', 'status']))
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Filtros activos:</span>
                    @if(request('search'))
                        <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 px-2 py-1 rounded text-xs">
                            Búsqueda: "{{ request('search') }}"
                        </span>
                    @endif
                    @if(request('role') && request('role') !== 'all')
                        <span class="bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 px-2 py-1 rounded text-xs">
                            Rol: {{ ucfirst(request('role')) }}
                        </span>
                    @endif
                    @if(request('status'))
                        <span class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 px-2 py-1 rounded text-xs">
                            Estado: {{ request('status') === 'active' ? 'Activos' : 'Suspendidos' }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Tabla de Usuarios -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Usuario
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Rol Actual
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Estado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Registrado
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->userRole)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($user->userRole->slug === 'administrador') bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300
                                        @elseif($user->userRole->slug === 'supervisor') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                                        @elseif($user->userRole->slug === 'desarrollador') bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300
                                        @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300
                                        @endif">
                                        @if($user->userRole->slug === 'administrador')
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z" clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif($user->userRole->slug === 'supervisor')
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                        @elseif($user->userRole->slug === 'desarrollador')
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                            </svg>
                                        @endif
                                        {{ $user->userRole->nombre }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                        Sin rol
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->is_suspended ?? false)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Suspendido
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Activo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <div>{{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Sin fecha' }}</div>
                                <div class="text-xs">{{ $user->created_at ? $user->created_at->diffForHumans() : 'Fecha desconocida' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    
                                    <!-- Cambiar Rol (Solo Administradores) -->
                                    @if(Auth::user()->isAdmin())
                                        @if($user->id !== Auth::id())
                                        <div class="relative inline-block">
                                            <form action="{{ route('admin.user.role', $user) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <select name="role_id" onchange="this.form.submit()" 
                                                        class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600">
                                                    <option value="" {{ !$user->role_id ? 'selected' : '' }}>Sin rol</option>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                                            {{ $role->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </div>
                                        @else
                                        <div class="relative inline-block">
                                            <span class="text-xs px-2 py-1 bg-gray-100 text-gray-500 rounded border border-gray-200 cursor-not-allowed">
                                                🔒 Tú
                                            </span>
                                        </div>
                                        @endif
                                    @else
                                        <!-- Solo mostrar el rol sin poder editarlo -->
                                        <div class="relative inline-block">
                                            <span class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded border border-gray-200">
                                                @if($user->userRole)
                                                    {{ $user->userRole->nombre }}
                                                @else
                                                    Sin rol
                                                @endif
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Suspender/Activar -->
                                    @if($user->id !== Auth::id())
                                    <form action="{{ route('admin.user.status', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" 
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded 
                                                {{ ($user->is_suspended ?? false) ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 hover:bg-green-200 dark:hover:bg-green-900/50' : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900/50' }} 
                                                transition-colors">
                                            @if($user->is_suspended ?? false)
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Activar
                                            @else
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Suspender
                                            @endif
                                        </button>
                                    </form>
                                    @endif

                                    <!-- Ver Detalles -->
                                    <a href="{{ route('admin.user.details', $user) }}" 
                                       class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-800 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/30 rounded hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"></path>
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Ver
                                    </a>

                                    @if($user->id === Auth::id())
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Tú
                                    </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        {{ $users->links() }}
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-300">
                                Mostrando
                                <span class="font-medium">{{ $users->firstItem() ?? 0 }}</span>
                                a
                                <span class="font-medium">{{ $users->lastItem() ?? 0 }}</span>
                                de
                                <span class="font-medium">{{ $users->total() }}</span>
                                resultados
                            </p>
                        </div>
                        <div>
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in {
        animation: fade-in 0.3s ease-out;
    }
    
    /* Auto ocultar mensajes después de 5 segundos */
    @keyframes fade-out {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
</style>

<script>
    // Auto ocultar mensajes de éxito/error después de 5 segundos
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.animate-fade-in');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'fade-out 0.5s ease-out';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        });
    });
</script>
@endsection