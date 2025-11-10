<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Periferico;
use App\Services\AmazonImageService;
use Illuminate\Support\Facades\DB;

class SyncAmazonImagesCommand extends Command
{
    /**
     * Nombre y firma del comando
     */
    protected $signature = 'amazon:sync-images
                            {--all : Sincronizar todos los periféricos, incluso los que ya tienen imagen}
                            {--only-missing : Solo sincronizar periféricos sin imagen (default)}
                            {--limit=10 : Número máximo de periféricos a procesar}
                            {--download : Descargar imágenes localmente además de guardar URLs}
                            {--categoria= : Solo sincronizar periféricos de una categoría específica}';

    /**
     * Descripción del comando
     */
    protected $description = 'Sincroniza imágenes de periféricos desde Amazon Product Advertising API';

    private $imageService;

    public function __construct(AmazonImageService $imageService)
    {
        parent::__construct();
        $this->imageService = $imageService;
    }

    /**
     * Ejecutar comando
     */
    public function handle()
    {
        $this->info('🚀 Iniciando sincronización de imágenes desde Amazon...');
        $this->newLine();

        // Determinar qué periféricos procesar
        $query = Periferico::with(['marca', 'categoria']);

        // Filtros
        if ($this->option('only-missing') || !$this->option('all')) {
            $query->whereNull('imagen_url');
            $this->info('📋 Modo: Solo periféricos sin imagen');
        } else {
            $this->info('📋 Modo: Todos los periféricos (forzar actualización)');
        }

        if ($categoria = $this->option('categoria')) {
            $query->whereHas('categoria', function($q) use ($categoria) {
                $q->where('nombre', 'like', "%{$categoria}%");
            });
            $this->info("📁 Categoría: {$categoria}");
        }

        $limit = (int) $this->option('limit');
        $query->limit($limit);

        $perifericos = $query->get();

        if ($perifericos->isEmpty()) {
            $this->warn('⚠️  No se encontraron periféricos para procesar');
            return 0;
        }

        $this->info("📦 Periféricos a procesar: {$perifericos->count()}");
        $this->newLine();

        // Confirmar antes de proceder
        if (!$this->confirm('¿Continuar con la sincronización?', true)) {
            $this->info('❌ Operación cancelada');
            return 0;
        }

        // Barra de progreso
        $bar = $this->output->createProgressBar($perifericos->count());
        $bar->start();

        $stats = [
            'total' => $perifericos->count(),
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'errors' => []
        ];

        foreach ($perifericos as $periferico) {
            $result = $this->imageService->syncPerifericoImage(
                $periferico,
                $this->option('all')
            );

            if (isset($result['skipped']) && $result['skipped']) {
                $stats['skipped']++;
            } elseif ($result['success']) {
                $stats['success']++;
                
                // Descargar localmente si se especifica
                if ($this->option('download')) {
                    $this->imageService->downloadAndStoreImage($periferico);
                }
            } else {
                $stats['failed']++;
                $stats['errors'][] = [
                    'id' => $periferico->id,
                    'nombre' => $periferico->nombre,
                    'error' => $result['error'] ?? 'Error desconocido'
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Mostrar resultados
        $this->displayResults($stats);

        return 0;
    }

    /**
     * Mostrar resultados de la sincronización
     */
    private function displayResults(array $stats): void
    {
        $this->info('✅ Sincronización completada');
        $this->newLine();

        // Tabla de resumen
        $this->table(
            ['Métrica', 'Cantidad'],
            [
                ['Total procesados', $stats['total']],
                ['✅ Exitosos', $stats['success']],
                ['⏭️  Omitidos', $stats['skipped']],
                ['❌ Fallidos', $stats['failed']],
            ]
        );

        // Mostrar errores si existen
        if (!empty($stats['errors'])) {
            $this->newLine();
            $this->error('❌ Errores encontrados:');
            $this->newLine();

            $errorTable = array_map(function($error) {
                return [
                    $error['id'],
                    substr($error['nombre'], 0, 40),
                    substr($error['error'], 0, 60)
                ];
            }, array_slice($stats['errors'], 0, 10));

            $this->table(
                ['ID', 'Nombre', 'Error'],
                $errorTable
            );

            if (count($stats['errors']) > 10) {
                $remaining = count($stats['errors']) - 10;
                $this->warn("... y {$remaining} errores más");
            }
        }

        // Estadísticas finales
        $this->newLine();
        $successRate = $stats['total'] > 0 
            ? round(($stats['success'] / $stats['total']) * 100, 2) 
            : 0;

        $this->info("📊 Tasa de éxito: {$successRate}%");
    }
}
