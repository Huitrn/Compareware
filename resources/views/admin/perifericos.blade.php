@extends('layouts.app')

@section('title', 'Gestión de Periféricos - CompareWare')

@section('content')
<div class="px-10 flex flex-1 justify-center py-5 bg-slate-50 dark:bg-gray-900">
    <div class="layout-content-container flex flex-col max-w-[1400px] flex-1">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">🖱️ Gestión de Periféricos</h1>
                <p class="text-gray-600 dark:text-gray-400">Administra el catálogo completo de periféricos</p>
            </div>
            <a href="{{ route('admin.access') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                ← Volver
            </a>
        </div>

        <!-- Tabs de Categorías -->
        <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
            <nav class="flex space-x-4" aria-label="Tabs">
                <button onclick="cambiarCategoria('mouse')" id="tab-mouse" class="tab-button px-4 py-2 text-sm font-medium border-b-2 border-purple-600 text-purple-600 dark:text-purple-400">
                    🖱️ Mouse
                </button>
                <button onclick="cambiarCategoria('teclado')" id="tab-teclado" class="tab-button px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    ⌨️ Teclado
                </button>
                <button onclick="cambiarCategoria('audifonos')" id="tab-audifonos" class="tab-button px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    🎧 Audífonos
                </button>
                <button onclick="cambiarCategoria('webcam')" id="tab-webcam" class="tab-button px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    📷 Webcam
                </button>
            </nav>
        </div>

        <!-- Botón Crear Nuevo -->
        <div class="mb-6 flex justify-end">
            <button onclick="abrirModalCrear()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors flex items-center gap-2">
                <span class="text-xl">➕</span>
                Crear Nuevo Periférico
            </button>
        </div>

        <!-- Tabla de Periféricos -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Marca</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Precio</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-perifericos" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Los datos se cargarán dinámicamente con JavaScript -->
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                <div class="flex justify-center items-center">
                                    <svg class="animate-spin h-8 w-8 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="ml-2">Cargando periféricos...</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<!-- Modal Crear/Editar Periférico -->
<div id="modal-periferico" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-3xl shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modal-titulo" class="text-2xl font-bold text-gray-900 dark:text-white">Crear Nuevo Periférico</h3>
            <button onclick="cerrarModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="form-periferico" class="space-y-4">
            <input type="hidden" id="periferico-id" name="id">
            
            <!-- Nombre y Modelo -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre del Periférico *</label>
                    <input type="text" id="periferico-nombre" name="nombre" required 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Modelo</label>
                    <input type="text" id="periferico-modelo" name="modelo" 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                </div>
            </div>

            <!-- Marca y Categoría -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Marca *</label>
                    <input type="text" id="periferico-marca" name="marca" required 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Categoría *</label>
                    <select id="periferico-categoria" name="categoria" required 
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                        <option value="mouse">🖱️ Mouse</option>
                        <option value="teclado">⌨️ Teclado</option>
                        <option value="audifonos">🎧 Audífonos</option>
                        <option value="webcam">📷 Webcam</option>
                    </select>
                </div>
            </div>

            <!-- Precio -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Precio (MXN) *</label>
                <input type="number" id="periferico-precio" name="precio" step="0.01" required 
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Botones -->
            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="cerrarModal()" 
                    class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-white rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                    class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <span id="btn-guardar-texto">Guardar Periférico</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let categoriaActual = 'mouse';
let perifericos = [];

// Cargar periféricos al inicio
document.addEventListener('DOMContentLoaded', function() {
    cargarPerifericos();
});

// Cambiar categoría
function cambiarCategoria(categoria) {
    categoriaActual = categoria;
    
    // Actualizar tabs
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('border-purple-600', 'text-purple-600', 'dark:text-purple-400');
        btn.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
    });
    
    const tabActivo = document.getElementById(`tab-${categoria}`);
    tabActivo.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
    tabActivo.classList.add('border-purple-600', 'text-purple-600', 'dark:text-purple-400');
    
    cargarPerifericos();
}

// Cargar periféricos desde la API
async function cargarPerifericos() {
    const tbody = document.getElementById('tabla-perifericos');
    tbody.innerHTML = `
        <tr>
            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                <div class="flex justify-center items-center">
                    <svg class="animate-spin h-8 w-8 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="ml-2">Cargando periféricos...</span>
                </div>
            </td>
        </tr>
    `;

    try {
        console.log('Cargando periféricos de categoría:', categoriaActual);
        const url = `/api/perifericos/${categoriaActual}`;
        console.log('URL:', url);
        
        const response = await fetch(url);
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Datos recibidos:', data);
        perifericos = data;
        
        if (perifericos.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        <div class="text-4xl mb-2">📦</div>
                        <p>No hay periféricos registrados en esta categoría</p>
                        <button onclick="abrirModalCrear()" class="mt-4 text-purple-600 hover:text-purple-700 font-medium">
                            ➕ Crear el primero
                        </button>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = perifericos.map(p => `
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">${p.id}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">${p.nombre}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${p.marca}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">$${parseFloat(p.precio).toFixed(2)}</td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                    <button onclick="editarPeriferico(${p.id})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                        ✏️ Editar
                    </button>
                    <button onclick="eliminarPeriferico(${p.id}, '${p.nombre}')" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                        🗑️ Eliminar
                    </button>
                </td>
            </tr>
        `).join('');
        
    } catch (error) {
        console.error('Error al cargar periféricos:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-red-600 dark:text-red-400">
                    <div class="mb-2">❌ Error al cargar los periféricos</div>
                    <div class="text-sm">${error.message}</div>
                    <div class="text-xs mt-2 text-gray-500">Abre la consola del navegador (F12) para más detalles</div>
                </td>
            </tr>
        `;
    }
}

// Abrir modal para crear
function abrirModalCrear() {
    document.getElementById('modal-titulo').textContent = 'Crear Nuevo Periférico';
    document.getElementById('btn-guardar-texto').textContent = 'Crear Periférico';
    document.getElementById('form-periferico').reset();
    document.getElementById('periferico-id').value = '';
    document.getElementById('periferico-categoria').value = categoriaActual;
    document.getElementById('modal-periferico').classList.remove('hidden');
}

// Editar periférico
function editarPeriferico(id) {
    const periferico = perifericos.find(p => p.id === id);
    if (!periferico) return;
    
    document.getElementById('modal-titulo').textContent = 'Editar Periférico';
    document.getElementById('btn-guardar-texto').textContent = 'Actualizar Periférico';
    document.getElementById('periferico-id').value = periferico.id;
    document.getElementById('periferico-nombre').value = periferico.nombre;
    document.getElementById('periferico-modelo').value = periferico.modelo || '';
    document.getElementById('periferico-marca').value = periferico.marca;
    document.getElementById('periferico-categoria').value = periferico.categoria;
    document.getElementById('periferico-precio').value = periferico.precio;
    
    document.getElementById('modal-periferico').classList.remove('hidden');
}

// Cerrar modal
function cerrarModal() {
    document.getElementById('modal-periferico').classList.add('hidden');
}

// Guardar periférico (crear o actualizar)
document.getElementById('form-periferico').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = document.getElementById('periferico-id').value;
    const datos = {
        nombre: document.getElementById('periferico-nombre').value,
        modelo: document.getElementById('periferico-modelo').value || null,
        marca: document.getElementById('periferico-marca').value,
        categoria: document.getElementById('periferico-categoria').value,
        precio: parseFloat(document.getElementById('periferico-precio').value)
    };
    
    try {
        console.log('Enviando datos:', datos);
        const url = id ? `/api/perifericos/${id}` : '/api/perifericos';
        const method = id ? 'PUT' : 'POST';
        console.log('URL:', url, 'Method:', method);
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(datos)
        });
        
        console.log('Response status:', response.status);
        const responseData = await response.json();
        console.log('Response data:', responseData);
        
        if (response.ok) {
            cerrarModal();
            cargarPerifericos();
            alert(id ? '✅ Periférico actualizado exitosamente' : '✅ Periférico creado exitosamente');
        } else {
            alert('❌ Error al guardar el periférico: ' + (responseData.error || responseData.message || 'Error desconocido'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error al guardar el periférico');
    }
});

// Eliminar periférico
async function eliminarPeriferico(id, nombre) {
    if (!confirm(`¿Estás seguro de que deseas eliminar "${nombre}"?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/api/perifericos/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            cargarPerifericos();
            alert('✅ Periférico eliminado exitosamente');
        } else {
            alert('❌ Error al eliminar el periférico');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('❌ Error al eliminar el periférico');
    }
}

// Cerrar modal al hacer clic fuera
document.getElementById('modal-periferico').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModal();
    }
});
</script>

@endsection
