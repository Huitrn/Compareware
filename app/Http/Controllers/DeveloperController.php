<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DeveloperController extends Controller
{
    /**
     * Constructor - Aplica middleware de rol
     */
    public function __construct()
    {
        $this->middleware(['auth', 'role:desarrollador']);
    }

    /**
     * Panel principal del desarrollador
     */
    public function dashboard()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => DB::connection()->getDatabaseName(),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
            'environment' => app()->environment(),
        ];

        return view('developer.dashboard', compact('info'));
    }

    /**
     * Ver logs del sistema
     */
    public function logs(Request $request)
    {
        if (!auth()->user()->hasPermission('view_logs')) {
            abort(403, 'No tiene permiso para ver logs.');
        }

        $logFile = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logFile)) {
            $content = File::get($logFile);
            $lines = explode("\n", $content);
            
            // Obtener últimas 100 líneas
            $logs = array_slice(array_reverse($lines), 0, 100);
        }

        return view('developer.logs', compact('logs'));
    }

    /**
     * Limpiar logs
     */
    public function clearLogs()
    {
        if (!auth()->user()->hasPermission('view_logs')) {
            abort(403);
        }

        $logFile = storage_path('logs/laravel.log');
        
        if (File::exists($logFile)) {
            File::put($logFile, '');
        }

        return redirect()->back()->with('success', 'Logs limpiados exitosamente.');
    }

    /**
     * Panel de gestión de caché
     */
    public function cachePanel()
    {
        if (!auth()->user()->hasPermission('manage_cache')) {
            abort(403, 'No tiene permiso para gestionar caché.');
        }

        $cacheInfo = [
            'driver' => config('cache.default'),
            'enabled' => true,
        ];

        return view('developer.cache', compact('cacheInfo'));
    }

    /**
     * Limpiar caché
     */
    public function clearCache(Request $request)
    {
        if (!auth()->user()->hasPermission('manage_cache')) {
            abort(403);
        }

        $type = $request->input('type', 'all');

        switch ($type) {
            case 'application':
                Artisan::call('cache:clear');
                $message = 'Caché de aplicación limpiada.';
                break;
            case 'config':
                Artisan::call('config:clear');
                $message = 'Caché de configuración limpiada.';
                break;
            case 'route':
                Artisan::call('route:clear');
                $message = 'Caché de rutas limpiada.';
                break;
            case 'view':
                Artisan::call('view:clear');
                $message = 'Caché de vistas limpiada.';
                break;
            default:
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
                $message = 'Toda la caché limpiada.';
        }

        Log::info('Cache cleared by developer', [
            'user_id' => auth()->id(),
            'type' => $type
        ]);

        return redirect()->back()->with('success', $message);
    }

    /**
     * Panel de pruebas de API
     */
    public function apiTester()
    {
        if (!auth()->user()->hasPermission('test_apis')) {
            abort(403, 'No tiene permiso para probar APIs.');
        }

        return view('developer.api-tester');
    }

    /**
     * Probar API
     */
    public function testApi(Request $request)
    {
        if (!auth()->user()->hasPermission('test_apis')) {
            abort(403);
        }

        $request->validate([
            'url' => 'required|url',
            'method' => 'required|in:GET,POST,PUT,DELETE',
            'headers' => 'nullable|json',
            'body' => 'nullable|json',
        ]);

        try {
            $client = new \GuzzleHttp\Client();
            
            $options = [];
            
            if ($request->headers) {
                $options['headers'] = json_decode($request->headers, true);
            }
            
            if ($request->body && in_array($request->method, ['POST', 'PUT'])) {
                $options['json'] = json_decode($request->body, true);
            }

            $response = $client->request($request->method, $request->url, $options);

            $result = [
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => json_decode($response->getBody(), true),
            ];

            Log::info('API test executed', [
                'user_id' => auth()->id(),
                'url' => $request->url,
                'method' => $request->method,
                'status' => $response->getStatusCode()
            ]);

            return response()->json(['success' => true, 'result' => $result]);
        } catch (\Exception $e) {
            Log::error('API test failed', [
                'user_id' => auth()->id(),
                'url' => $request->url,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Ver configuración del sistema
     */
    public function viewConfig()
    {
        if (!auth()->user()->hasPermission('view_config')) {
            abort(403, 'No tiene permiso para ver la configuración.');
        }

        $config = [
            'app' => config('app'),
            'database' => [
                'connection' => config('database.default'),
                'database' => config('database.connections.'.config('database.default').'.database'),
            ],
            'cache' => config('cache'),
            'queue' => config('queue'),
        ];

        return view('developer.config', compact('config'));
    }

    /**
     * Información de la base de datos
     */
    public function databaseInfo()
    {
        if (!auth()->user()->hasPermission('view_config')) {
            abort(403);
        }

        $tables = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = ?', [config('database.connections.pgsql.database')]);
        
        $info = [
            'tables' => $tables,
            'connection' => DB::connection()->getName(),
            'database' => DB::connection()->getDatabaseName(),
        ];

        return view('developer.database-info', compact('info'));
    }

    /**
     * Herramientas de debugging
     */
    public function debugTools()
    {
        $tools = [
            'routes' => [
                'name' => 'Listar Rutas',
                'command' => 'route:list',
                'description' => 'Ver todas las rutas registradas'
            ],
            'config' => [
                'name' => 'Config Cache',
                'command' => 'config:cache',
                'description' => 'Cachear la configuración'
            ],
            'optimize' => [
                'name' => 'Optimize',
                'command' => 'optimize',
                'description' => 'Optimizar la aplicación'
            ],
        ];

        return view('developer.debug-tools', compact('tools'));
    }

    /**
     * Ejecutar comando artisan
     */
    public function executeCommand(Request $request)
    {
        if (!auth()->user()->hasPermission('view_logs')) {
            abort(403);
        }

        $request->validate([
            'command' => 'required|string'
        ]);

        $allowedCommands = ['route:list', 'config:cache', 'optimize', 'about'];

        if (!in_array($request->command, $allowedCommands)) {
            return response()->json(['error' => 'Comando no permitido'], 403);
        }

        Artisan::call($request->command);
        $output = Artisan::output();

        Log::info('Artisan command executed', [
            'user_id' => auth()->id(),
            'command' => $request->command
        ]);

        return response()->json(['output' => $output]);
    }
}
