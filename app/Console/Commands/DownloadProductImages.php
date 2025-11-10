<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Periferico;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DownloadProductImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:download
                            {--storage=files : Método de almacenamiento: files (archivos) o database (BLOB)}
                            {--overwrite : Sobrescribir imágenes existentes}
                            {--limit=50 : Número máximo de productos a procesar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Descarga y almacena imágenes de productos localmente (archivos o base de datos)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $storage = $this->option('storage');
        $overwrite = $this->option('overwrite');
        $limit = (int) $this->option('limit');

        $this->info("🖼️  Descargando imágenes de productos...");
        $this->newLine();
        $this->info("📁 Almacenamiento: " . ($storage === 'database' ? 'Base de datos (BLOB)' : 'Archivos locales'));
        
        // Obtener productos con imagen_url pero sin imagen local
        $query = Periferico::whereNotNull('imagen_url');
        
        if (!$overwrite) {
            if ($storage === 'database') {
                $query->whereNull('imagen_blob');
            } else {
                $query->whereNull('imagen_path');
            }
        }
        
        $productos = $query->limit($limit)->get();
        
        if ($productos->isEmpty()) {
            $this->warn("⚠️  No hay productos para procesar");
            return 0;
        }
        
        $this->info("📦 Productos a procesar: {$productos->count()}");
        $this->newLine();
        
        if (!$this->confirm('¿Continuar?', true)) {
            return 0;
        }
        
        $bar = $this->output->createProgressBar($productos->count());
        $bar->start();
        
        $stats = [
            'total' => $productos->count(),
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($productos as $producto) {
            try {
                if ($storage === 'database') {
                    $result = $this->saveToDatabase($producto);
                } else {
                    $result = $this->saveToFiles($producto);
                }
                
                if ($result) {
                    $stats['success']++;
                } else {
                    $stats['failed']++;
                }
            } catch (\Exception $e) {
                $stats['failed']++;
                $stats['errors'][] = [
                    'producto' => $producto->nombre,
                    'error' => $e->getMessage()
                ];
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Mostrar resultados
        $this->info("✅ Descarga completada");
        $this->newLine();
        
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Total procesados', $stats['total']],
                ['✅ Exitosos', $stats['success']],
                ['❌ Fallidos', $stats['failed']],
            ]
        );
        
        if (!empty($stats['errors'])) {
            $this->newLine();
            $this->error("❌ Errores encontrados:");
            foreach ($stats['errors'] as $error) {
                $this->line("  - {$error['producto']}: {$error['error']}");
            }
        }
        
        $this->newLine();
        $this->info("📊 Tasa de éxito: " . round(($stats['success'] / $stats['total']) * 100) . "%");
        
        return 0;
    }
    
    /**
     * Guardar imagen como archivo en storage
     */
    private function saveToFiles(Periferico $producto): bool
    {
        try {
            $response = Http::timeout(30)->get($producto->imagen_url);
            
            if (!$response->successful()) {
                return false;
            }
            
            // Obtener tipo MIME
            $mimeType = $response->header('Content-Type') ?? 'image/jpeg';
            $extension = $this->getExtensionFromMime($mimeType);
            
            // Generar nombre de archivo único
            $filename = Str::slug($producto->nombre) . '-' . $producto->id . '.' . $extension;
            $path = 'images/perifericos/' . $filename;
            
            // Guardar archivo
            Storage::disk('public')->put($path, $response->body());
            
            // Actualizar producto
            $producto->update([
                'imagen_path' => $path,
                'imagen_mime_type' => $mimeType,
                'imagen_source' => 'local'
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Guardar imagen como BLOB en la base de datos
     */
    private function saveToDatabase(Periferico $producto): bool
    {
        try {
            $response = Http::timeout(30)->get($producto->imagen_url);
            
            if (!$response->successful()) {
                return false;
            }
            
            // Obtener tipo MIME
            $mimeType = $response->header('Content-Type') ?? 'image/jpeg';
            
            // Guardar en base de datos
            $producto->update([
                'imagen_blob' => $response->body(),
                'imagen_mime_type' => $mimeType,
                'imagen_source' => 'database'
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Obtener extensión de archivo desde tipo MIME
     */
    private function getExtensionFromMime(string $mimeType): string
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
        ];
        
        return $extensions[$mimeType] ?? 'jpg';
    }
}

