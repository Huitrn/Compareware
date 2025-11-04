<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Google Shopping API - Compareware</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4285f4;
            --secondary-color: #34a853;
            --danger-color: #ea4335;
            --warning-color: #fbbc04;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .header-section {
            background: linear-gradient(135deg, #4285f4, #34a853);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .shopping-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .search-section {
            padding: 40px;
        }
        
        .search-box {
            position: relative;
            margin-bottom: 30px;
        }
        
        .search-input {
            width: 100%;
            padding: 20px 60px 20px 20px;
            font-size: 1.2rem;
            border: 3px solid #e5e7eb;
            border-radius: 50px;
            transition: all 0.3s;
        }
        
        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(66, 133, 244, 0.25);
            outline: none;
        }
        
        .search-btn {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .search-btn:hover {
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 4px 15px rgba(66, 133, 244, 0.4);
        }
        
        .quick-searches {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        .quick-search-btn {
            background: white;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .quick-search-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.05);
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-compare {
            background: linear-gradient(135deg, var(--warning-color), #f9a825);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(251, 188, 4, 0.4);
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }
        
        .loading.active {
            display: block;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3rem;
        }
        
        .results-section {
            padding: 40px;
            display: none;
        }
        
        .results-section.active {
            display: block;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f3f4f6;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 40px;
        }
        
        .product-store {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 10px;
        }
        
        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
            color: #fbbf24;
            margin-bottom: 10px;
        }
        
        .product-delivery {
            font-size: 0.8rem;
            color: #10b981;
            margin-bottom: 10px;
        }
        
        .best-price-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .comparison-stats {
            background: linear-gradient(135deg, #e0f2fe, #dbeafe);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .stat-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .alert-custom {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-card">
            <!-- Header -->
            <div class="header-section">
                <div class="shopping-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h1>Google Shopping API</h1>
                <p>Busca y compara precios en múltiples tiendas online</p>
                <p class="mt-3">
                    <i class="fas fa-store"></i> 8+ Tiendas | 
                    <i class="fas fa-tags"></i> Comparación en tiempo real |
                    <i class="fas fa-chart-line"></i> Análisis de precios
                </p>
            </div>

            <!-- Search Section -->
            <div class="search-section">
                <!-- Quick Searches -->
                <div class="quick-searches">
                    <button class="quick-search-btn" onclick="quickSearch('Logitech G502')">
                        <i class="fas fa-mouse"></i> Logitech G502
                    </button>
                    <button class="quick-search-btn" onclick="quickSearch('Razer BlackWidow')">
                        <i class="fas fa-keyboard"></i> Razer BlackWidow
                    </button>
                    <button class="quick-search-btn" onclick="quickSearch('HyperX Cloud II')">
                        <i class="fas fa-headphones"></i> HyperX Cloud II
                    </button>
                    <button class="quick-search-btn" onclick="quickSearch('Corsair K70')">
                        ⌨️ Corsair K70
                    </button>
                </div>

                <!-- Search Box -->
                <div class="search-box">
                    <input type="text" 
                           class="search-input" 
                           id="searchQuery" 
                           placeholder="Busca un producto... (ej: Logitech G502)"
                           onkeypress="handleEnter(event)">
                    <button class="search-btn" onclick="searchProducts()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn btn-primary btn-lg" onclick="searchProducts()">
                        <i class="fas fa-search"></i> Buscar Productos
                    </button>
                    <button class="btn btn-compare btn-lg" onclick="comparePrices()">
                        <i class="fas fa-balance-scale"></i> Comparar Precios
                    </button>
                </div>

                <!-- Loading -->
                <div class="loading" id="loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3" style="color: var(--primary-color); font-weight: 600;">
                        Buscando en tiendas online...
                    </p>
                </div>

                <!-- Alert Container -->
                <div id="alertContainer" class="mt-3"></div>
            </div>

            <!-- Results Section -->
            <div class="results-section" id="resultsSection">
                <!-- Comparison Stats -->
                <div class="comparison-stats" id="comparisonStats" style="display: none;">
                    <h4 class="text-center mb-4">
                        <i class="fas fa-chart-bar"></i> Análisis de Precios
                    </h4>
                    <div class="stat-row" id="statsContainer"></div>
                </div>

                <!-- Results Header -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 id="resultsTitle">
                        <i class="fas fa-box-open"></i> Resultados de búsqueda
                    </h3>
                    <div>
                        <select class="form-select" id="sortSelect" onchange="sortResults()">
                            <option value="price-asc">Precio: Menor a Mayor</option>
                            <option value="price-desc">Precio: Mayor a Menor</option>
                            <option value="rating">Mejor Valorados</option>
                            <option value="store">Por Tienda</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="product-grid" id="productsGrid"></div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card mt-4" style="border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="card-body p-4">
                <h5 class="card-title">
                    <i class="fas fa-info-circle text-info"></i> Información de la API
                </h5>
                <ul class="mb-0">
                    <li><strong>Tiendas Soportadas:</strong> Amazon, Mercado Libre, Best Buy, Liverpool, Elektra, Walmart, Coppel, Cyberpuerta</li>
                    <li><strong>Caché:</strong> 6 horas</li>
                    <li><strong>Modo:</strong> Datos de ejemplo (mock data) para pruebas locales</li>
                    <li><strong>Endpoint Base:</strong> /api/google-shopping/*</li>
                    <li><strong>Funcionalidades:</strong>
                        <ul>
                            <li>Búsqueda de productos en múltiples tiendas</li>
                            <li>Comparación de precios en tiempo real</li>
                            <li>Análisis estadístico de precios (mínimo, máximo, promedio)</li>
                            <li>Identificación del mejor precio disponible</li>
                            <li>Información de disponibilidad y tiempos de entrega</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let currentResults = [];
        
        // Quick search
        function quickSearch(query) {
            document.getElementById('searchQuery').value = query;
            searchProducts();
        }
        
        // Handle Enter key
        function handleEnter(event) {
            if (event.key === 'Enter') {
                searchProducts();
            }
        }
        
        // Show loading
        function showLoading(show) {
            document.getElementById('loading').classList.toggle('active', show);
        }
        
        // Show alert
        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-custom alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alertDiv);
            
            setTimeout(() => alertDiv.remove(), 5000);
        }
        
        // Search products
        async function searchProducts() {
            const query = document.getElementById('searchQuery').value.trim();
            
            if (!query) {
                showAlert('Por favor ingresa un término de búsqueda', 'warning');
                return;
            }
            
            showLoading(true);
            document.getElementById('resultsSection').classList.remove('active');
            document.getElementById('comparisonStats').style.display = 'none';
            
            try {
                const response = await fetch('/api/google-shopping/search', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ query: query, limit: 10 })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentResults = data.data.products;
                    displayResults(currentResults, query);
                    showAlert(`Se encontraron ${currentResults.length} productos`, 'success');
                } else {
                    showAlert(data.message || 'No se encontraron productos', 'warning');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error al buscar productos: ' + error.message, 'danger');
            } finally {
                showLoading(false);
            }
        }
        
        // Compare prices
        async function comparePrices() {
            const query = document.getElementById('searchQuery').value.trim();
            
            if (!query) {
                showAlert('Por favor ingresa un término de búsqueda', 'warning');
                return;
            }
            
            showLoading(true);
            document.getElementById('resultsSection').classList.remove('active');
            
            try {
                const response = await fetch('/api/google-shopping/compare-prices', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ query: query, limit: 20 })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentResults = data.data.products;
                    displayResults(currentResults, query);
                    displayComparisonStats(data.data.price_analysis);
                    showAlert('Comparación de precios completada', 'success');
                } else {
                    showAlert(data.message || 'Error en la comparación', 'warning');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error al comparar precios: ' + error.message, 'danger');
            } finally {
                showLoading(false);
            }
        }
        
        // Display comparison stats
        function displayComparisonStats(stats) {
            if (!stats || !stats.lowest) return;
            
            const container = document.getElementById('statsContainer');
            container.innerHTML = `
                <div class="stat-card">
                    <div class="stat-label">💚 Precio Más Bajo</div>
                    <div class="stat-value" style="color: #10b981;">
                        $${stats.lowest.price.toLocaleString('es-MX', {minimumFractionDigits: 2})}
                    </div>
                    <small>${stats.lowest.store}</small>
                </div>
                <div class="stat-card">
                    <div class="stat-label">📊 Precio Promedio</div>
                    <div class="stat-value" style="color: #3b82f6;">
                        $${stats.average.toLocaleString('es-MX', {minimumFractionDigits: 2})}
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">💰 Ahorro Potencial</div>
                    <div class="stat-value" style="color: #f59e0b;">
                        ${stats.savings_percentage}%
                    </div>
                    <small>$${stats.difference.toLocaleString('es-MX', {minimumFractionDigits: 2})}</small>
                </div>
                <div class="stat-card">
                    <div class="stat-label">🏪 Total Tiendas</div>
                    <div class="stat-value">${Object.keys(stats).length > 3 ? Object.keys(stats).length - 3 : 8}</div>
                </div>
            `;
            
            document.getElementById('comparisonStats').style.display = 'block';
        }
        
        // Display results
        function displayResults(products, query) {
            const grid = document.getElementById('productsGrid');
            const title = document.getElementById('resultsTitle');
            
            title.innerHTML = `<i class="fas fa-box-open"></i> ${products.length} resultados para "${query}"`;
            
            grid.innerHTML = '';
            
            // Find lowest price
            const lowestPrice = Math.min(...products.map(p => p.price_numeric).filter(p => p > 0));
            
            products.forEach(product => {
                const isBestPrice = product.price_numeric === lowestPrice && product.price_numeric > 0;
                
                const card = document.createElement('div');
                card.className = 'product-card';
                card.onclick = () => window.open(product.url, '_blank');
                
                card.innerHTML = `
                    <img src="${product.image || 'https://via.placeholder.com/200x200?text=Sin+Imagen'}" 
                         alt="${product.title}" 
                         class="product-image"
                         onerror="this.src='https://via.placeholder.com/200x200?text=Sin+Imagen'">
                    <div class="product-info">
                        <div class="product-store">
                            <i class="fas fa-store"></i> ${product.store}
                        </div>
                        <div class="product-title">${product.title}</div>
                        <div class="product-price">
                            ${product.price}
                            ${isBestPrice ? '<span class="best-price-badge">¡Mejor Precio!</span>' : ''}
                        </div>
                        ${product.rating ? `
                            <div class="product-rating">
                                <i class="fas fa-star"></i>
                                ${product.rating} (${product.reviews_count} reseñas)
                            </div>
                        ` : ''}
                        ${product.delivery ? `
                            <div class="product-delivery">
                                <i class="fas fa-shipping-fast"></i> ${product.delivery}
                            </div>
                        ` : ''}
                        <div class="text-center mt-2">
                            <span class="badge bg-primary">${product.availability}</span>
                        </div>
                    </div>
                `;
                
                grid.appendChild(card);
            });
            
            document.getElementById('resultsSection').classList.add('active');
        }
        
        // Sort results
        function sortResults() {
            const sortBy = document.getElementById('sortSelect').value;
            
            switch(sortBy) {
                case 'price-asc':
                    currentResults.sort((a, b) => a.price_numeric - b.price_numeric);
                    break;
                case 'price-desc':
                    currentResults.sort((a, b) => b.price_numeric - a.price_numeric);
                    break;
                case 'rating':
                    currentResults.sort((a, b) => (b.rating || 0) - (a.rating || 0));
                    break;
                case 'store':
                    currentResults.sort((a, b) => a.store.localeCompare(b.store));
                    break;
            }
            
            const query = document.getElementById('searchQuery').value;
            displayResults(currentResults, query);
        }
    </script>
</body>
</html>
