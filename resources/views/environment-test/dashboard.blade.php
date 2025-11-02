<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧪 Test de Ambientes - CompareWare</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold flex items-center">
                        <i class="fas fa-flask mr-3"></i>
                        Dashboard de Pruebas de Ambientes
                    </h1>
                    <p class="text-blue-100 mt-1">CompareWare - Sistema Multi-Ambiente</p>
                </div>
                <div class="text-right">
                    @php
                        $envConfig = [
                            'sandbox' => ['icon' => '🏖️', 'color' => 'bg-green-500', 'name' => 'Sandbox'],
                            'staging' => ['icon' => '🎭', 'color' => 'bg-yellow-500', 'name' => 'Staging'],
                            'production' => ['icon' => '🚀', 'color' => 'bg-red-500', 'name' => 'Production'],
                            'local' => ['icon' => '🏠', 'color' => 'bg-gray-500', 'name' => 'Local']
                        ];
                        $config = $envConfig[$currentEnv] ?? $envConfig['local'];
                    @endphp
                    <span class="inline-flex items-center px-4 py-2 rounded-full {{ $config['color'] }} text-white font-semibold">
                        {{ $config['icon'] }} {{ $config['name'] }}
                    </span>
                    <div class="text-sm text-blue-100 mt-1">
                        Ambiente Actual: {{ strtoupper($currentEnv) }}
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-8" x-data="environmentDashboard()">
        <!-- Environment Stats Cards -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            @foreach(['sandbox' => ['icon' => '🏖️', 'color' => 'green'], 'staging' => ['icon' => '🎭', 'color' => 'yellow'], 'production' => ['icon' => '🚀', 'color' => 'red']] as $env => $config)
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-{{ $config['color'] }}-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 capitalize">
                            {{ $config['icon'] }} {{ $env }}
                        </h3>
                        @if(isset($stats[$env]))
                            <p class="text-sm text-gray-600">
                                {{ $stats[$env]->total }} actividades
                            </p>
                            <p class="text-xs text-gray-500">
                                Último: {{ $stats[$env]->last_activity ? \Carbon\Carbon::parse($stats[$env]->last_activity)->diffForHumans() : 'Nunca' }}
                            </p>
                        @else
                            <p class="text-sm text-gray-500">Sin actividad</p>
                        @endif
                    </div>
                    @if($currentEnv === $env)
                        <div class="text-{{ $config['color'] }}-500">
                            <i class="fas fa-check-circle text-2xl"></i>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-tools mr-2 text-blue-600"></i>
                Acciones Rápidas
            </h2>
            
            <div class="grid md:grid-cols-4 gap-4">
                <!-- Test Current Environment -->
                <button @click="runTests()" 
                        :disabled="testing"
                        class="flex flex-col items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border-2 border-blue-200 transition-colors disabled:opacity-50">
                    <i class="fas fa-play-circle text-3xl text-blue-600 mb-2"></i>
                    <span class="font-semibold text-blue-800">Probar Ambiente</span>
                    <span class="text-xs text-blue-600">{{ $currentEnv }}</span>
                </button>

                <!-- Compare Environments -->
                <button @click="compareEnvironments()" 
                        class="flex flex-col items-center p-4 bg-green-50 hover:bg-green-100 rounded-lg border-2 border-green-200 transition-colors">
                    <i class="fas fa-balance-scale text-3xl text-green-600 mb-2"></i>
                    <span class="font-semibold text-green-800">Comparar</span>
                    <span class="text-xs text-green-600">Ambientes</span>
                </button>

                <!-- View Logs -->
                <button @click="viewLogs()" 
                        class="flex flex-col items-center p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border-2 border-purple-200 transition-colors">
                    <i class="fas fa-list-alt text-3xl text-purple-600 mb-2"></i>
                    <span class="font-semibold text-purple-800">Ver Logs</span>
                    <span class="text-xs text-purple-600">Actividades</span>
                </button>

                <!-- Switch Environment -->
                <div class="relative">
                    <select @change="showSwitchConfirm($event.target.value)" 
                            class="w-full h-full p-4 bg-orange-50 hover:bg-orange-100 rounded-lg border-2 border-orange-200 transition-colors appearance-none text-center font-semibold text-orange-800">
                        <option value="">Cambiar Ambiente</option>
                        <option value="sandbox" {{ $currentEnv === 'sandbox' ? 'disabled' : '' }}>🏖️ Sandbox</option>
                        <option value="staging" {{ $currentEnv === 'staging' ? 'disabled' : '' }}>🎭 Staging</option>
                        <option value="production" {{ $currentEnv === 'production' ? 'disabled' : '' }}>🚀 Production</option>
                    </select>
                    <i class="fas fa-exchange-alt absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-2xl text-orange-600 pointer-events-none"></i>
                </div>
            </div>
        </div>

        <!-- Test Results -->
        <div x-show="showResults" class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-clipboard-check mr-2 text-green-600"></i>
                Resultados de Pruebas
            </h2>
            
            <div x-show="testing" class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
                <p class="text-gray-600">Ejecutando pruebas del ambiente {{ $currentEnv }}...</p>
            </div>

            <div x-show="testResults && !testing" class="space-y-4">
                <template x-for="(test, key) in testResults?.tests" :key="key">
                    <div class="border rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 capitalize mb-2 flex items-center">
                            <i :class="test.status === 'success' ? 'fas fa-check-circle text-green-500' : 'fas fa-times-circle text-red-500'" class="mr-2"></i>
                            <span x-text="key.replace('_', ' ')"></span>
                        </h3>
                        <pre class="bg-gray-100 p-2 rounded text-sm overflow-x-auto" x-text="JSON.stringify(test, null, 2)"></pre>
                    </div>
                </template>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-history mr-2 text-indigo-600"></i>
                Actividad Reciente - {{ ucfirst($currentEnv) }}
            </h2>
            
            @if($recentLogs->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left">Fecha</th>
                                <th class="px-4 py-2 text-left">Acción</th>
                                <th class="px-4 py-2 text-left">Descripción</th>
                                <th class="px-4 py-2 text-left">IP</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($recentLogs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">{{ $log->description }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $log->ip_address }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">
                    <i class="fas fa-inbox text-4xl mb-2 block"></i>
                    No hay actividad reciente en este ambiente
                </p>
            @endif
        </div>
    </div>

    <script>
        function environmentDashboard() {
            return {
                testing: false,
                showResults: false,
                testResults: null,

                async runTests() {
                    this.testing = true;
                    this.showResults = true;
                    this.testResults = null;

                    try {
                        const response = await fetch('/environment/test', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                            }
                        });
                        
                        this.testResults = await response.json();
                    } catch (error) {
                        console.error('Error running tests:', error);
                        this.testResults = { success: false, error: 'Error de conexión' };
                    } finally {
                        this.testing = false;
                    }
                },

                async compareEnvironments() {
                    try {
                        const response = await fetch('/environment/compare');
                        const data = await response.json();
                        
                        // Mostrar comparación en modal o nueva vista
                        alert('Comparación de ambientes:\n' + JSON.stringify(data, null, 2));
                    } catch (error) {
                        console.error('Error comparing environments:', error);
                    }
                },

                viewLogs() {
                    // Scroll to logs section
                    document.querySelector('table')?.scrollIntoView({ behavior: 'smooth' });
                },

                showSwitchConfirm(environment) {
                    if (environment && confirm(`¿Cambiar al ambiente ${environment}?`)) {
                        window.location.href = `/environment/switch/${environment}`;
                    }
                }
            }
        }
    </script>
</body>
</html>