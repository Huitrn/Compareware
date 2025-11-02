<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Servicio para integración con YouTube API
 * Obtiene videos de reviews, unboxings y tutoriales de productos
 */
class YouTubeApiService
{
    private $apiKey;
    private $rapidApiKey;
    private $rapidApiHost;
    private $baseUrl;
    private $maxResults;
    private $useMockData;

    public function __construct()
    {
        $this->apiKey = config('services.youtube.api_key');
        $this->rapidApiKey = config('services.youtube.rapidapi_key');
        $this->rapidApiHost = config('services.youtube.rapidapi_host');
        $this->baseUrl = config('services.youtube.base_url');
        $this->maxResults = config('services.youtube.max_results', 5);
        
        // Usar datos mock en desarrollo/staging
        $this->useMockData = empty($this->rapidApiKey) || 
                           str_contains($this->rapidApiKey ?? '', 'test') ||
                           config('app.env') === 'local';
    }

    /**
     * Buscar videos de reviews para un producto específico
     * 
     * @param string $productName Nombre del producto
     * @param string $searchType Tipo de búsqueda: 'review', 'unboxing', 'tutorial', 'comparison'
     * @param int $maxResults Número máximo de resultados (default: 5)
     * @return array
     */
    public function searchProductVideos($productName, $searchType = 'review', $maxResults = null)
    {
        $maxResults = $maxResults ?? $this->maxResults;
        
        // Construir query de búsqueda optimizado
        $searchQuery = $this->buildSearchQuery($productName, $searchType);
        
        Log::info("🎬 YouTube API: Buscando videos", [
            'product' => $productName,
            'type' => $searchType,
            'query' => $searchQuery
        ]);

        if ($this->useMockData) {
            return $this->getMockVideoData($productName, $searchType);
        }

        try {
            $cacheKey = "youtube_videos_" . md5($searchQuery . $maxResults);
            
            // Verificar caché (24 horas)
            if (Cache::has($cacheKey)) {
                Log::info("✅ YouTube API: Datos desde caché");
                return Cache::get($cacheKey);
            }

            // Llamada a RapidAPI YouTube v3
            $response = Http::withHeaders([
                'X-RapidAPI-Key' => $this->rapidApiKey,
                'X-RapidAPI-Host' => $this->rapidApiHost,
            ])->get($this->baseUrl . '/search', [
                'q' => $searchQuery,
                'part' => 'snippet',
                'type' => 'video',
                'maxResults' => $maxResults,
                'order' => 'relevance',
                'videoDuration' => 'medium', // Videos de 4-20 minutos
                'videoDefinition' => 'high',
            ]);

            if ($response->successful()) {
                $data = $this->formatVideoResponse($response->json(), $productName, $searchType);
                
                // Guardar en caché por 24 horas
                Cache::put($cacheKey, $data, 86400);
                
                Log::info("✅ YouTube API: Búsqueda exitosa", [
                    'videos_found' => count($data['videos'] ?? [])
                ]);
                
                return $data;
            } else {
                Log::error("❌ YouTube API Error: " . $response->status(), [
                    'response' => $response->body()
                ]);
                return $this->getMockVideoData($productName, $searchType);
            }

        } catch (\Exception $e) {
            Log::error("❌ YouTube API Exception: " . $e->getMessage());
            return $this->getMockVideoData($productName, $searchType);
        }
    }

    /**
     * Obtener detalles completos de un video específico
     * 
     * @param string $videoId ID del video de YouTube
     * @return array
     */
    public function getVideoDetails($videoId)
    {
        if ($this->useMockData) {
            return $this->getMockVideoDetails($videoId);
        }

        try {
            $cacheKey = "youtube_video_" . $videoId;
            
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $response = Http::withHeaders([
                'X-RapidAPI-Key' => $this->rapidApiKey,
                'X-RapidAPI-Host' => $this->rapidApiHost,
            ])->get($this->baseUrl . '/videos', [
                'part' => 'snippet,statistics,contentDetails',
                'id' => $videoId,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $formatted = $this->formatVideoDetails($data);
                
                Cache::put($cacheKey, $formatted, 86400);
                
                return $formatted;
            }

            return $this->getMockVideoDetails($videoId);

        } catch (\Exception $e) {
            Log::error("❌ YouTube API Exception (video details): " . $e->getMessage());
            return $this->getMockVideoDetails($videoId);
        }
    }

    /**
     * Construir query de búsqueda optimizado
     */
    private function buildSearchQuery($productName, $searchType)
    {
        $typeKeywords = [
            'review' => 'review honest opinion',
            'unboxing' => 'unboxing first impressions',
            'tutorial' => 'tutorial how to setup guide',
            'comparison' => 'comparison vs alternatives',
        ];

        $keywords = $typeKeywords[$searchType] ?? 'review';
        
        return "{$productName} {$keywords}";
    }

    /**
     * Formatear respuesta de búsqueda de videos
     */
    private function formatVideoResponse($apiResponse, $productName, $searchType)
    {
        $videos = [];

        if (isset($apiResponse['items']) && is_array($apiResponse['items'])) {
            foreach ($apiResponse['items'] as $item) {
                $videos[] = [
                    'video_id' => $item['id']['videoId'] ?? '',
                    'title' => $item['snippet']['title'] ?? 'Sin título',
                    'description' => $item['snippet']['description'] ?? '',
                    'thumbnail' => $item['snippet']['thumbnails']['high']['url'] ?? 
                                 $item['snippet']['thumbnails']['medium']['url'] ?? '',
                    'channel_name' => $item['snippet']['channelTitle'] ?? 'Desconocido',
                    'channel_id' => $item['snippet']['channelId'] ?? '',
                    'published_at' => $item['snippet']['publishedAt'] ?? '',
                    'url' => 'https://www.youtube.com/watch?v=' . ($item['id']['videoId'] ?? ''),
                    'embed_url' => 'https://www.youtube.com/embed/' . ($item['id']['videoId'] ?? ''),
                ];
            }
        }

        return [
            'success' => true,
            'product' => $productName,
            'search_type' => $searchType,
            'total_results' => count($videos),
            'videos' => $videos,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Formatear detalles de un video específico
     */
    private function formatVideoDetails($apiResponse)
    {
        if (!isset($apiResponse['items'][0])) {
            return ['success' => false, 'message' => 'Video no encontrado'];
        }

        $video = $apiResponse['items'][0];

        return [
            'success' => true,
            'video_id' => $video['id'] ?? '',
            'title' => $video['snippet']['title'] ?? '',
            'description' => $video['snippet']['description'] ?? '',
            'channel_name' => $video['snippet']['channelTitle'] ?? '',
            'published_at' => $video['snippet']['publishedAt'] ?? '',
            'thumbnail' => $video['snippet']['thumbnails']['high']['url'] ?? '',
            'duration' => $video['contentDetails']['duration'] ?? '',
            'view_count' => $video['statistics']['viewCount'] ?? 0,
            'like_count' => $video['statistics']['likeCount'] ?? 0,
            'comment_count' => $video['statistics']['commentCount'] ?? 0,
            'url' => 'https://www.youtube.com/watch?v=' . ($video['id'] ?? ''),
            'embed_url' => 'https://www.youtube.com/embed/' . ($video['id'] ?? ''),
        ];
    }

    /**
     * Datos mock para desarrollo/demostración
     */
    private function getMockVideoData($productName, $searchType)
    {
        Log::info("🎭 YouTube API: Usando datos MOCK para demostración");

        $mockVideos = [
            [
                'video_id' => 'dQw4w9WgXcQ',
                'title' => "{$productName} - Review Completo en Español",
                'description' => "Review detallado del {$productName}. Analizamos diseño, ergonomía, rendimiento y precio.",
                'thumbnail' => 'https://via.placeholder.com/480x360/FF0000/FFFFFF?text=' . urlencode($productName),
                'channel_name' => 'Tech Reviews ES',
                'channel_id' => 'mock_channel_1',
                'published_at' => now()->subDays(7)->toIso8601String(),
                'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'embed_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
            ],
            [
                'video_id' => 'jNQXAC9IVRw',
                'title' => "{$productName} Unboxing y Primeras Impresiones",
                'description' => "Unboxing del {$productName}. ¿Vale la pena? Te lo contamos todo.",
                'thumbnail' => 'https://via.placeholder.com/480x360/0000FF/FFFFFF?text=Unboxing',
                'channel_name' => 'Gaming Unboxing',
                'channel_id' => 'mock_channel_2',
                'published_at' => now()->subDays(14)->toIso8601String(),
                'url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
                'embed_url' => 'https://www.youtube.com/embed/jNQXAC9IVRw',
            ],
            [
                'video_id' => 'oHg5SJYRHA0',
                'title' => "¿Merece la pena el {$productName}? - Análisis Honesto",
                'description' => "Después de 2 meses de uso, te digo si el {$productName} cumple lo que promete.",
                'thumbnail' => 'https://via.placeholder.com/480x360/00FF00/000000?text=Review',
                'channel_name' => 'ProTech Reviews',
                'channel_id' => 'mock_channel_3',
                'published_at' => now()->subDays(30)->toIso8601String(),
                'url' => 'https://www.youtube.com/watch?v=oHg5SJYRHA0',
                'embed_url' => 'https://www.youtube.com/embed/oHg5SJYRHA0',
            ],
        ];

        return [
            'success' => true,
            'product' => $productName,
            'search_type' => $searchType,
            'total_results' => count($mockVideos),
            'videos' => $mockVideos,
            'timestamp' => now()->toIso8601String(),
            'using_mock_data' => true,
        ];
    }

    /**
     * Detalles mock de un video
     */
    private function getMockVideoDetails($videoId)
    {
        return [
            'success' => true,
            'video_id' => $videoId,
            'title' => 'Video de Demostración',
            'description' => 'Este es un video de demostración para modo desarrollo.',
            'channel_name' => 'Canal Demo',
            'published_at' => now()->subDays(7)->toIso8601String(),
            'thumbnail' => 'https://via.placeholder.com/480x360/CCCCCC/000000?text=Demo+Video',
            'duration' => 'PT10M30S',
            'view_count' => 150000,
            'like_count' => 5000,
            'comment_count' => 320,
            'url' => 'https://www.youtube.com/watch?v=' . $videoId,
            'embed_url' => 'https://www.youtube.com/embed/' . $videoId,
            'using_mock_data' => true,
        ];
    }

    /**
     * Buscar múltiples tipos de videos para un producto
     * 
     * @param string $productName
     * @return array
     */
    public function getAllProductVideos($productName)
    {
        return [
            'reviews' => $this->searchProductVideos($productName, 'review', 3),
            'unboxings' => $this->searchProductVideos($productName, 'unboxing', 2),
            'tutorials' => $this->searchProductVideos($productName, 'tutorial', 2),
        ];
    }
}
