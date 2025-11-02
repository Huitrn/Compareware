@extends('layouts.app')

@section('title', 'Detalles de Usuario - CompareWare')

@section('content')
<div class="px-10 flex flex-1 justify-center py-5">
    <div class="layout-content-container flex flex-col max-w-[1000px] flex-1">
        
        <!-- Breadcrumb y Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <nav class="flex items-center space-x-2 text-sm text-gray-600 mb-2">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-600">Dashboard</a>
                    <span>/</span>
                    <a href="{{ route('admin.users') }}" class="hover:text-blue-600">Usuarios</a>
                    <span>/</span>
                    <span class="text-gray-900 font-medium">{{ $user->name }}</span>
                </nav>
                <h1 class="text-[#0d141c] text-[32px] font-bold leading-tight">Detalles del Usuario</h1>
            </div>
            <a href="{{ route('admin.users') }}" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors">
                ← Volver a Lista
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Información Principal -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Perfil -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Información del Perfil
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre Completo</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $user->name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Correo Electrónico</label>
                            <p class="text-lg text-gray-900">{{ $user->email }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rol Actual</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($user->role === 'admin') bg-red-100 text-red-800
                                @elseif($user->role === 'moderator') bg-yellow-100 text-yellow-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado de la Cuenta</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                {{ ($user->is_suspended ?? false) ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ ($user->is_suspended ?? false) ? 'Suspendido' : 'Activo' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Información de Registro -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 12v-4m0 0V9a4 4 0 00-8 0v6m4-6v6"></path>
                        </svg>
                        Información de Registro
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Registro</label>
                            @if($user->created_at)
                                <p class="text-gray-900">{{ $user->created_at->format('d/m/Y H:i:s') }}</p>
                                <p class="text-sm text-gray-500">{{ $user->created_at->diffForHumans() }}</p>
                            @else
                                <p class="text-gray-500">Sin fecha de registro</p>
                                <p class="text-sm text-gray-400">Fecha desconocida</p>
                            @endif
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Última Actualización</label>
                            @if($user->updated_at)
                                <p class="text-gray-900">{{ $user->updated_at->format('d/m/Y H:i:s') }}</p>
                                <p class="text-sm text-gray-500">{{ $user->updated_at->diffForHumans() }}</p>
                            @else
                                <p class="text-gray-500">Sin actualización</p>
                                <p class="text-sm text-gray-400">Nunca actualizado</p>
                            @endif
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">ID de Usuario</label>
                            <p class="text-gray-900 font-mono">#{{ $user->id }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tiempo en la Plataforma</label>
                            <p class="text-gray-900">
                                @if($user->created_at)
                                    {{ $user->created_at->diffInDays() }} días
                                @else
                                    <span class="text-gray-500">Tiempo desconocido</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel de Acciones -->
            <div class="space-y-6">
                
                <!-- Acciones Rápidas -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Acciones Rápidas</h3>
                    
                    @if($user->id !== Auth::id())
                    <div class="space-y-3">
                        
                        <!-- Cambiar Rol -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cambiar Rol</label>
                            <form action="{{ route('admin.user.role', $user) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="flex gap-2">
                                    <select name="role" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Usuario</option>
                                        <option value="moderator" {{ $user->role === 'moderator' ? 'selected' : '' }}>Moderador</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrador</option>
                                    </select>
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        Cambiar
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Suspender/Activar -->
                        <div>
                            <form action="{{ route('admin.user.status', $user) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full px-4 py-2 font-medium rounded-lg transition-colors
                                    {{ ($user->is_suspended ?? false) ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700' }}">
                                    @if($user->is_suspended ?? false)
                                        <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Activar Usuario
                                    @else
                                        <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Suspender Usuario
                                    @endif
                                </button>
                            </form>
                        </div>
                        
                        <!-- Eliminar Usuario -->
                        @if($user->id !== Auth::id())
                            <div>
                                <button onclick="openDeleteModal()" class="w-full px-4 py-2 font-medium rounded-lg transition-colors bg-red-700 text-white hover:bg-red-800 border-2 border-red-800">
                                    <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 012 0v4a1 1 0 11-2 0V7zM12 7a1 1 0 10-2 0v4a1 1 0 002 0V7z" clip-rule="evenodd"></path>
                                    </svg>
                                    🗑️ ELIMINAR DEFINITIVAMENTE
                                </button>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <p class="text-gray-500 text-sm">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    No puedes eliminar tu propio usuario
                                </p>
                            </div>
                        @endif
                    </div>
                    @else
                    <div class="text-center py-4">
                        <div class="bg-gray-100 rounded-lg p-4">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-gray-600 font-medium">Este es tu propio perfil</p>
                            <p class="text-sm text-gray-500">No puedes modificar tu propia cuenta</p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Información Adicional -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Estadísticas</h3>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Días registrado:</span>
                            <span class="font-semibold">
                                {{ $user->created_at ? $user->created_at->diffInDays() : 'Desconocido' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Última actividad:</span>
                            <span class="font-semibold">
                                {{ $user->updated_at ? $user->updated_at->diffForHumans() : 'Sin actividad registrada' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Estado:</span>
                            <span class="font-semibold {{ ($user->is_suspended ?? false) ? 'text-red-600' : 'text-green-600' }}">
                                {{ ($user->is_suspended ?? false) ? 'Suspendido' : 'Activo' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div id="deleteModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 text-center mb-4">⚠️ ELIMINAR USUARIO DEFINITIVAMENTE</h3>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-red-800 mb-2"><strong>🚨 ADVERTENCIA: Esta acción NO se puede deshacer</strong></p>
                <div class="text-sm text-red-700">
                    <p><strong>Usuario a eliminar:</strong></p>
                    <p>• Nombre: <strong>{{ $user->name }}</strong></p>
                    <p>• Email: <strong>{{ $user->email }}</strong></p>
                    <p>• Rol: <strong>{{ ucfirst($user->role) }}</strong></p>
                </div>
            </div>
            
            <p class="text-sm text-gray-600 mb-4 text-center">
                Escribe <strong class="text-red-600">ELIMINAR</strong> para confirmar:
            </p>
            
            <input type="text" id="deleteConfirmation" placeholder="Escribe ELIMINAR"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 mb-4">
            
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" 
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancelar
                </button>
                <form id="deleteForm" action="{{ route('admin.user.delete', $user) }}" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" id="confirmDeleteBtn" disabled
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                        🗑️ ELIMINAR
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openDeleteModal() {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteConfirmation').value = '';
    document.getElementById('confirmDeleteBtn').disabled = true;
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

document.getElementById('deleteConfirmation').addEventListener('input', function(e) {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (e.target.value === 'ELIMINAR') {
        confirmBtn.disabled = false;
        confirmBtn.classList.remove('disabled:bg-gray-400');
    } else {
        confirmBtn.disabled = true;
        confirmBtn.classList.add('disabled:bg-gray-400');
    }
});

// Cerrar modal si se hace clic fuera de él
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>

@endsection