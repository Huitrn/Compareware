<?php

namespace App\Services;

use App\Models\Periferico;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

/**
 * Servicio para gestionar imágenes de periféricos desde Amazon
 * Sincroniza, descarga y almacena imágenes de productos
 */
class AmazonImageService
{
    private $amazonService;
    private $storageDisk;

    public function __construct(AmazonApiService $amazonService)
    {
        $this->amazonService = $amazonService;
        $this->storageDisk = config('filesystems.default');
    }

    /**
     * Sincronizar imagen de un periférico desde Amazon
     * 
     * @param Periferico $periferico
     * @param bool $forceUpdate Si es true, actualiza aunque ya tenga imagen
     * @return array Resultado de la sincronización
     */
    public function syncPerifericoImage(Periferico $periferico, bool $forceUpdate = false): array
    {
        // Si ya tiene imagen y no es forzado, saltar
        if (!$forceUpdate && $periferico->hasImage()) {
            return [
                'success' => true,
                'skipped' => true,
                'message' => 'Periférico ya tiene imagen',
                'periferico_id' => $periferico->id
            ];
        }

        try {
            // Buscar en Amazon
            $searchTerm = $this->buildSearchTerm($periferico);
            Log::info("Buscando imagen en Amazon para: {$searchTerm}");
            
            $amazonData = $this->amazonService->findDatabaseProduct(
                $periferico->nombre,
                $periferico->marca->nombre ?? null
            );

            if (!isset($amazonData['data']['products']) || empty($amazonData['data']['products'])) {
                return [
                    'success' => false,
                    'error' => 'No se encontraron productos en Amazon',
                    'periferico_id' => $periferico->id
                ];
            }

            // Tomar el primer producto
            $product = $amazonData['data']['products'][0];
            
            if (empty($product['product_photo'])) {
                return [
                    'success' => false,
                    'error' => 'Producto sin imagen en Amazon',
                    'periferico_id' => $periferico->id
                ];
            }

            // Actualizar periférico con datos de Amazon
            $updateData = [
                'imagen_url' => $product['product_photo'],
                'imagen_alt' => $product['product_title'] ?? $periferico->nombre,
                'imagen_source' => 'amazon',
                'amazon_url' => $product['product_url'] ?? null,
                'amazon_asin' => $product['asin'] ?? null
            ];

            // Si hay más imágenes, agregarlas a la galería
            if (isset($product['product_photos']) && is_array($product['product_photos'])) {
                $updateData['galeria_imagenes'] = array_map(function($url) use ($product) {
                    return [
                        'url' => $url,
                        'alt' => $product['product_title'] ?? 'Imagen de producto',
                        'source' => 'amazon'
                    ];
                }, $product['product_photos']);
            }

            $periferico->update($updateData);

            Log::info("Imagen sincronizada exitosamente para periférico #{$periferico->id}");

            return [
                'success' => true,
                'periferico_id' => $periferico->id,
                'imagen_url' => $product['product_photo'],
                'gallery_count' => count($updateData['galeria_imagenes'] ?? []),
                'message' => 'Imagen sincronizada desde Amazon'
            ];

        } catch (\Exception $e) {
            Log::error("Error sincronizando imagen para periférico #{$periferico->id}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'periferico_id' => $periferico->id
            ];
        }
    }

    /**
     * Sincronizar imágenes de múltiples periféricos
     * 
     * @param array $perifericoIds Array de IDs o Collection de Perifericos
     * @param bool $forceUpdate
     * @return array Resumen de resultados
     */
    public function syncMultiplePerifericosImages($perifericos, bool $forceUpdate = false): array
    {
        $results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'details' => []
        ];

        $perifericosCollection = is_array($perifericos) 
            ? Periferico::whereIn('id', $perifericos)->get()
            : $perifericos;

        $results['total'] = $perifericosCollection->count();

        foreach ($perifericosCollection as $periferico) {
            $result = $this->syncPerifericoImage($periferico, $forceUpdate);
            
            if (isset($result['skipped']) && $result['skipped']) {
                $results['skipped']++;
            } elseif ($result['success']) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
            
            $results['details'][] = $result;
            
            // Pequeña pausa para no saturar la API
            usleep(500000); // 0.5 segundos
        }

        return $results;
    }

    /**
     * Descargar y almacenar imagen localmente (opcional)
     * 
     * @param Periferico $periferico
     * @return array
     */
    public function downloadAndStoreImage(Periferico $periferico): array
    {
        if (!$periferico->hasImage()) {
            return [
                'success' => false,
                'error' => 'Periférico sin imagen para descargar'
            ];
        }

        try {
            $imageUrl = $periferico->imagen_url;
            
            // Descargar imagen
            $response = Http::timeout(30)->get($imageUrl);
            
            if (!$response->successful()) {
                throw new \Exception('No se pudo descargar la imagen');
            }

            // Generar nombre único
            $extension = $this->getImageExtension($imageUrl);
            $filename = 'perifericos/' . $periferico->id . '_' . time() . '.' . $extension;
            
            // Guardar en storage
            Storage::disk($this->storageDisk)->put($filename, $response->body());
            
            // Actualizar periférico
            $periferico->update([
                'imagen_path' => $filename,
                'imagen_source' => 'local'
            ]);

            Log::info("Imagen descargada y almacenada localmente: {$filename}");

            return [
                'success' => true,
                'path' => $filename,
                'url' => Storage::url($filename)
            ];

        } catch (\Exception $e) {
            Log::error("Error descargando imagen: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Construir término de búsqueda optimizado
     */
    private function buildSearchTerm(Periferico $periferico): string
    {
        $terms = [];
        
        if ($periferico->marca) {
            $terms[] = $periferico->marca->nombre;
        }
        
        $terms[] = $periferico->nombre;
        
        if ($periferico->modelo) {
            $terms[] = $periferico->modelo;
        }

        return implode(' ', $terms);
    }

    /**
     * Obtener extensión de imagen desde URL
     */
    private function getImageExtension(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        
        return in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp']) 
            ? strtolower($extension) 
            : 'jpg';
    }

    /**
     * Generar thumbnail desde imagen (requiere Intervention Image)
     */
    public function generateThumbnail(Periferico $periferico, int $width = 300, int $height = 300): array
    {
        // TODO: Implementar con Intervention Image si está disponible
        return [
            'success' => false,
            'error' => 'Generación de thumbnails no implementada aún'
        ];
    }

    /**
     * Limpiar imágenes huérfanas del storage
     */
    public function cleanOrphanImages(): array
    {
        $deletedCount = 0;
        $errors = [];

        try {
            $storedImages = Storage::disk($this->storageDisk)->files('perifericos');
            $usedPaths = Periferico::whereNotNull('imagen_path')->pluck('imagen_path')->toArray();

            foreach ($storedImages as $image) {
                if (!in_array($image, $usedPaths)) {
                    Storage::disk($this->storageDisk)->delete($image);
                    $deletedCount++;
                }
            }

            return [
                'success' => true,
                'deleted_count' => $deletedCount
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
