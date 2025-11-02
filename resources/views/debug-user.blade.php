<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Usuario - CompareWare</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg p-8 max-w-2xl w-full">
        <h1 class="text-3xl font-bold text-center mb-6 text-gray-800">🔍 Debug Usuario</h1>
        
        <div class="bg-gray-900 text-green-400 p-6 rounded-lg font-mono text-sm mb-6">
            <pre>{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        
        <div class="flex flex-wrap gap-4 justify-center">
            <a href="{{ $data['home_url'] }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition-colors">
                🏠 Ir al Inicio
            </a>
            
            @if($data['can_access_admin'] === 'YES')
                <a href="{{ route('admin.access') }}" 
                   class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-lg transition-colors">
                    🎯 Área Admin
                </a>
            @endif
            
            <form method="POST" action="{{ $data['logout_url'] }}" class="inline">
                @csrf
                <button type="submit" 
                        class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg transition-colors">
                    🚪 Cerrar Sesión
                </button>
            </form>
        </div>
        
        <div class="mt-6 text-center text-gray-600">
            <p class="text-sm">Actualiza esta página después de cerrar sesión para ver los cambios</p>
        </div>
    </div>
</body>
</html>