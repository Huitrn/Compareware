<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test YouTube API - Compareware</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .container {
            max-width: 1400px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }
        .video-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .video-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }
        .thumbnail-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 ratio */
            overflow: hidden;
            border-radius: 10px;
        }
        .thumbnail-container img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .play-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 60px;
            color: white;
            text-shadow: 0 0 20px rgba(0, 0, 0, 0.8);
            pointer-events: none;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        .video-card:hover .play-overlay {
            opacity: 1;
        }
        .badge-mock {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .search-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="text-center text-white mb-5">
            <h1 class="display-4 fw-bold">
                🎬 YouTube API Integration Test
            </h1>
            <p class="lead">Prueba de integración con YouTube para reviews de periféricos</p>
        </div>

        <!-- Search Form -->
        <div class="search-form mb-5">
            <form method="GET" action="{{ route('test.youtube') }}">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label for="product" class="form-label fw-bold">Buscar Producto</label>
                        <input 
                            type="text" 
                            class="form-control form-control-lg" 
                            id="product" 
                            name="product" 
                            value="{{ $product ?? 'Logitech G502' }}"
                            placeholder="Ej: Logitech G502, Razer DeathAdder..."
                        >
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            🔍 Buscar Videos
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Header -->
        @if(isset($videos['success']) && $videos['success'])
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">
                                📹 Videos encontrados para: <strong>{{ $videos['product'] ?? $product }}</strong>
                            </h3>
                            <p class="text-muted mb-0">
                                Total: {{ $videos['total_results'] ?? 0 }} videos
                                @if(isset($videos['using_mock_data']) && $videos['using_mock_data'])
                                    <span class="badge badge-mock ms-2">MODO DEMOSTRACIÓN</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <small class="text-muted">
                                🕐 {{ $videos['timestamp'] ?? now() }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Videos Grid -->
            @if(isset($videos['videos']) && count($videos['videos']) > 0)
                <div class="row g-4">
                    @foreach($videos['videos'] as $video)
                        <div class="col-md-6 col-lg-4">
                            <div class="card video-card">
                                <div class="card-body p-0">
                                    <!-- Thumbnail -->
                                    <div class="thumbnail-container">
                                        <img src="{{ $video['thumbnail'] }}" alt="{{ $video['title'] }}">
                                        <div class="play-overlay">
                                            ▶️
                                        </div>
                                    </div>

                                    <!-- Video Info -->
                                    <div class="p-3">
                                        <h5 class="card-title mb-2" style="height: 60px; overflow: hidden;">
                                            {{ $video['title'] }}
                                        </h5>
                                        <p class="text-muted mb-2">
                                            <small>
                                                📺 {{ $video['channel_name'] }}
                                            </small>
                                        </p>
                                        <p class="card-text small text-muted" style="height: 60px; overflow: hidden;">
                                            {{ $video['description'] ?: 'Sin descripción disponible' }}
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <small class="text-muted">
                                                🕐 {{ \Carbon\Carbon::parse($video['published_at'])->diffForHumans() }}
                                            </small>
                                            <div>
                                                <a href="{{ $video['url'] }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-primary">
                                                    Ver en YouTube
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning">
                    ⚠️ No se encontraron videos para este producto.
                </div>
            @endif

        @else
            <div class="alert alert-danger">
                ❌ Error al obtener videos: {{ $videos['message'] ?? 'Error desconocido' }}
            </div>
        @endif

        <!-- API Info -->
        <div class="card mt-5">
            <div class="card-body">
                <h4 class="card-title mb-3">ℹ️ Información de la Integración</h4>
                <div class="row">
                    <div class="col-md-6">
                        <h6>📡 Endpoints Disponibles (API):</h6>
                        <ul class="list-unstyled">
                            <li><code>POST /api/youtube/search</code> - Buscar videos</li>
                            <li><code>POST /api/youtube/all-videos</code> - Todos los tipos</li>
                            <li><code>POST /api/youtube/video-details</code> - Detalles de video</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>🔧 Tipos de Búsqueda Soportados:</h6>
                        <ul class="list-unstyled">
                            <li>✅ <strong>review</strong> - Reviews y opiniones</li>
                            <li>✅ <strong>unboxing</strong> - Unboxings e impresiones</li>
                            <li>✅ <strong>tutorial</strong> - Tutoriales y guías</li>
                            <li>✅ <strong>comparison</strong> - Comparaciones</li>
                        </ul>
                    </div>
                </div>
                <hr>
                <h6>📝 Ejemplo de uso con AJAX:</h6>
                <pre class="bg-light p-3 rounded"><code>fetch('/api/youtube/search', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        product_name: 'Logitech G502',
        search_type: 'review',
        max_results: 5
    })
})
.then(response => response.json())
.then(data => console.log(data));</code></pre>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center mt-4">
            <a href="/" class="btn btn-outline-light btn-lg">
                ← Volver al inicio
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
