<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Currency Exchange API - Compareware</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Flag Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.11.0/css/flag-icons.min.css"/>
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
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
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .header-section p {
            font-size: 1.1rem;
            opacity: 0.95;
        }
        
        .currency-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .converter-section {
            padding: 40px;
        }
        
        .amount-input-group {
            position: relative;
            margin-bottom: 30px;
        }
        
        .amount-input {
            font-size: 2rem;
            text-align: center;
            border: 3px solid #e5e7eb;
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .amount-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        
        .currency-select {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin: 30px 0;
        }
        
        .currency-dropdown {
            flex: 1;
            max-width: 300px;
        }
        
        .currency-dropdown select {
            font-size: 1.2rem;
            padding: 15px;
            border-radius: 12px;
            border: 2px solid #e5e7eb;
            transition: all 0.3s;
        }
        
        .currency-dropdown select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        
        .swap-button {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .swap-button:hover {
            transform: rotate(180deg) scale(1.1);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .convert-btn {
            background: linear-gradient(135deg, var(--success-color), #059669);
            border: none;
            padding: 15px 50px;
            font-size: 1.2rem;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
            transition: all 0.3s;
        }
        
        .convert-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
        }
        
        .result-section {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            display: none;
        }
        
        .result-section.active {
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
        
        .result-amount {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            margin: 20px 0;
        }
        
        .exchange-rate {
            text-align: center;
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 20px;
        }
        
        .multi-currency-results {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .currency-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .currency-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .currency-card .currency-code {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .currency-card .currency-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .rate-table {
            margin-top: 30px;
        }
        
        .rate-table table {
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .rate-table th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 15px;
            font-weight: 600;
        }
        
        .rate-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .rate-table tr:last-child td {
            border-bottom: none;
        }
        
        .rate-table tr:hover {
            background-color: #f9fafb;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .loading.active {
            display: block;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3rem;
        }
        
        .alert-custom {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .quick-amounts {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        
        .quick-amount-btn {
            background: white;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .quick-amount-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: scale(1.05);
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn-secondary-custom {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(107, 114, 128, 0.4);
        }
        
        .btn-info-custom {
            background: linear-gradient(135deg, var(--info-color), #2563eb);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }
        
        .flag-icon {
            width: 30px;
            height: 20px;
            margin-right: 10px;
            border-radius: 3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-card">
            <!-- Header -->
            <div class="header-section">
                <div class="currency-icon">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <h1>Conversor de Monedas</h1>
                <p>Prueba de integración Currency Exchange API</p>
                <p class="mt-3">
                    <i class="fas fa-globe"></i> 10 monedas soportadas | 
                    <i class="fas fa-clock"></i> Actualización cada hora |
                    <i class="fas fa-shield-alt"></i> Datos en tiempo real
                </p>
            </div>

            <!-- Converter Section -->
            <div class="converter-section">
                <!-- Quick Amount Buttons -->
                <div class="quick-amounts">
                    <button class="quick-amount-btn" onclick="setAmount(100)">100</button>
                    <button class="quick-amount-btn" onclick="setAmount(500)">500</button>
                    <button class="quick-amount-btn" onclick="setAmount(1000)">1000</button>
                    <button class="quick-amount-btn" onclick="setAmount(5000)">5000</button>
                    <button class="quick-amount-btn" onclick="setAmount(10000)">10000</button>
                </div>

                <!-- Amount Input -->
                <div class="amount-input-group">
                    <label class="form-label text-center w-100 mb-3" style="font-size: 1.2rem; font-weight: 600;">
                        <i class="fas fa-dollar-sign"></i> Cantidad a convertir
                    </label>
                    <input type="number" 
                           class="form-control amount-input" 
                           id="amount" 
                           value="1000" 
                           min="0" 
                           step="0.01"
                           placeholder="Ingresa una cantidad">
                </div>

                <!-- Currency Selection -->
                <div class="currency-select">
                    <div class="currency-dropdown">
                        <label class="form-label text-center w-100 mb-2">
                            <i class="fas fa-coins"></i> De
                        </label>
                        <select class="form-select" id="fromCurrency">
                            <option value="USD" selected>🇺🇸 USD - Dólar Estadounidense</option>
                            <option value="MXN">🇲🇽 MXN - Peso Mexicano</option>
                            <option value="EUR">🇪🇺 EUR - Euro</option>
                            <option value="GBP">🇬🇧 GBP - Libra Esterlina</option>
                            <option value="CAD">🇨🇦 CAD - Dólar Canadiense</option>
                            <option value="JPY">🇯🇵 JPY - Yen Japonés</option>
                            <option value="CNY">🇨🇳 CNY - Yuan Chino</option>
                            <option value="BRL">🇧🇷 BRL - Real Brasileño</option>
                            <option value="ARS">🇦🇷 ARS - Peso Argentino</option>
                            <option value="COP">🇨🇴 COP - Peso Colombiano</option>
                        </select>
                    </div>

                    <button class="swap-button" onclick="swapCurrencies()" title="Intercambiar monedas">
                        <i class="fas fa-exchange-alt fa-lg"></i>
                    </button>

                    <div class="currency-dropdown">
                        <label class="form-label text-center w-100 mb-2">
                            <i class="fas fa-coins"></i> A
                        </label>
                        <select class="form-select" id="toCurrency">
                            <option value="USD">🇺🇸 USD - Dólar Estadounidense</option>
                            <option value="MXN" selected>🇲🇽 MXN - Peso Mexicano</option>
                            <option value="EUR">🇪🇺 EUR - Euro</option>
                            <option value="GBP">🇬🇧 GBP - Libra Esterlina</option>
                            <option value="CAD">🇨🇦 CAD - Dólar Canadiense</option>
                            <option value="JPY">🇯🇵 JPY - Yen Japonés</option>
                            <option value="CNY">🇨🇳 CNY - Yuan Chino</option>
                            <option value="BRL">🇧🇷 BRL - Real Brasileño</option>
                            <option value="ARS">🇦🇷 ARS - Peso Argentino</option>
                            <option value="COP">🇨🇴 COP - Peso Colombiano</option>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="convert-btn" onclick="convertCurrency()">
                        <i class="fas fa-sync-alt"></i> Convertir
                    </button>
                    <button class="btn btn-secondary-custom" onclick="convertToAll()">
                        <i class="fas fa-globe-americas"></i> Convertir a Todas
                    </button>
                    <button class="btn btn-info-custom" onclick="getAllRates()">
                        <i class="fas fa-chart-line"></i> Ver Tasas de Cambio
                    </button>
                </div>

                <!-- Loading -->
                <div class="loading" id="loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3" style="color: var(--primary-color); font-weight: 600;">
                        Obteniendo tasas de cambio...
                    </p>
                </div>

                <!-- Alert -->
                <div id="alertContainer"></div>

                <!-- Single Result -->
                <div class="result-section" id="singleResult">
                    <h3 class="text-center mb-4">
                        <i class="fas fa-check-circle text-success"></i> Resultado de Conversión
                    </h3>
                    <div class="result-amount" id="resultAmount"></div>
                    <div class="exchange-rate" id="exchangeRate"></div>
                </div>

                <!-- Multiple Results -->
                <div class="result-section" id="multipleResults">
                    <h3 class="text-center mb-4">
                        <i class="fas fa-globe"></i> Conversión a Múltiples Monedas
                    </h3>
                    <div class="multi-currency-results" id="multiCurrencyContainer"></div>
                </div>

                <!-- Rate Table -->
                <div class="result-section" id="rateTable">
                    <h3 class="text-center mb-4">
                        <i class="fas fa-chart-line"></i> Tabla de Tasas de Cambio
                    </h3>
                    <div class="rate-table">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Moneda Origen</th>
                                    <th>Moneda Destino</th>
                                    <th>Tasa de Cambio</th>
                                    <th>Última Actualización</th>
                                </tr>
                            </thead>
                            <tbody id="rateTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Card -->
        <div class="card mt-4" style="border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            <div class="card-body p-4">
                <h5 class="card-title">
                    <i class="fas fa-info-circle text-info"></i> Información de la API
                </h5>
                <ul class="mb-0">
                    <li><strong>Monedas Soportadas:</strong> USD, MXN, EUR, GBP, CAD, JPY, CNY, BRL, ARS, COP</li>
                    <li><strong>Caché:</strong> 1 hora</li>
                    <li><strong>Modo:</strong> Datos de ejemplo (mock data) para pruebas locales</li>
                    <li><strong>Endpoint Base:</strong> /api/currency/*</li>
                    <li><strong>Funcionalidades:</strong>
                        <ul>
                            <li>Conversión simple entre dos monedas</li>
                            <li>Conversión múltiple a todas las monedas</li>
                            <li>Consulta de tasas de cambio actuales</li>
                            <li>Soporte para 10 monedas internacionales</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Set quick amount
        function setAmount(amount) {
            document.getElementById('amount').value = amount;
        }

        // Swap currencies
        function swapCurrencies() {
            const fromCurrency = document.getElementById('fromCurrency');
            const toCurrency = document.getElementById('toCurrency');
            
            const temp = fromCurrency.value;
            fromCurrency.value = toCurrency.value;
            toCurrency.value = temp;
        }

        // Show loading
        function showLoading(show) {
            document.getElementById('loading').classList.toggle('active', show);
        }

        // Hide all results
        function hideAllResults() {
            document.getElementById('singleResult').classList.remove('active');
            document.getElementById('multipleResults').classList.remove('active');
            document.getElementById('rateTable').classList.remove('active');
        }

        // Show alert
        function showAlert(message, type = 'danger') {
            const alertContainer = document.getElementById('alertContainer');
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-custom alert-dismissible fade show mt-3`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            alertContainer.innerHTML = '';
            alertContainer.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Convert single currency
        async function convertCurrency() {
            const amount = document.getElementById('amount').value;
            const fromCurrency = document.getElementById('fromCurrency').value;
            const toCurrency = document.getElementById('toCurrency').value;

            if (!amount || amount <= 0) {
                showAlert('Por favor ingresa una cantidad válida', 'warning');
                return;
            }

            if (fromCurrency === toCurrency) {
                showAlert('Por favor selecciona monedas diferentes', 'warning');
                return;
            }

            hideAllResults();
            showLoading(true);

            try {
                const response = await fetch('/api/currency/convert', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: parseFloat(amount),
                        from: fromCurrency,
                        to: toCurrency
                    })
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('resultAmount').textContent = 
                        `${data.data.formatted_result}`;
                    document.getElementById('exchangeRate').innerHTML = 
                        `<i class="fas fa-chart-line"></i> Tasa de cambio: 1 ${fromCurrency} = ${data.data.exchange_rate} ${toCurrency}`;
                    document.getElementById('singleResult').classList.add('active');
                    showAlert('Conversión realizada exitosamente', 'success');
                } else {
                    showAlert(data.message || 'Error al convertir moneda', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error al conectar con el servidor', 'danger');
            } finally {
                showLoading(false);
            }
        }

        // Convert to all currencies
        async function convertToAll() {
            const amount = document.getElementById('amount').value;
            const fromCurrency = document.getElementById('fromCurrency').value;

            if (!amount || amount <= 0) {
                showAlert('Por favor ingresa una cantidad válida', 'warning');
                return;
            }

            hideAllResults();
            showLoading(true);

            try {
                const response = await fetch('/api/currency/convert-multiple', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: parseFloat(amount),
                        from: fromCurrency
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const container = document.getElementById('multiCurrencyContainer');
                    container.innerHTML = '';

                    Object.entries(data.data.conversions).forEach(([currency, info]) => {
                        const card = document.createElement('div');
                        card.className = 'currency-card';
                        card.innerHTML = `
                            <div class="currency-code">
                                ${getCurrencyFlag(currency)} ${currency}
                            </div>
                            <div class="currency-amount">${info.formatted}</div>
                            <small class="text-muted">Tasa: ${info.rate}</small>
                        `;
                        container.appendChild(card);
                    });

                    document.getElementById('multipleResults').classList.add('active');
                    showAlert(`Conversión a ${Object.keys(data.data.conversions).length} monedas completada`, 'success');
                } else {
                    showAlert(data.message || 'Error al convertir moneda', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error al conectar con el servidor', 'danger');
            } finally {
                showLoading(false);
            }
        }

        // Get all exchange rates
        async function getAllRates() {
            const fromCurrency = document.getElementById('fromCurrency').value;

            hideAllResults();
            showLoading(true);

            try {
                const response = await fetch(`/api/currency/rates?base=${fromCurrency}`);
                const data = await response.json();

                if (data.success) {
                    const tbody = document.getElementById('rateTableBody');
                    tbody.innerHTML = '';

                    Object.entries(data.data.rates).forEach(([currency, rate]) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td><strong>${getCurrencyFlag(fromCurrency)} ${fromCurrency}</strong></td>
                            <td><strong>${getCurrencyFlag(currency)} ${currency}</strong></td>
                            <td><span class="badge bg-primary">${rate}</span></td>
                            <td><small class="text-muted">${new Date(data.data.timestamp).toLocaleString('es-MX')}</small></td>
                        `;
                        tbody.appendChild(row);
                    });

                    document.getElementById('rateTable').classList.add('active');
                    showAlert('Tasas de cambio actualizadas', 'success');
                } else {
                    showAlert(data.message || 'Error al obtener tasas', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('Error al conectar con el servidor', 'danger');
            } finally {
                showLoading(false);
            }
        }

        // Get currency flag emoji
        function getCurrencyFlag(currency) {
            const flags = {
                'USD': '🇺🇸',
                'MXN': '🇲🇽',
                'EUR': '🇪🇺',
                'GBP': '🇬🇧',
                'CAD': '🇨🇦',
                'JPY': '🇯🇵',
                'CNY': '🇨🇳',
                'BRL': '🇧🇷',
                'ARS': '🇦🇷',
                'COP': '🇨🇴'
            };
            return flags[currency] || '🌐';
        }

        // Enter key to convert
        document.getElementById('amount').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                convertCurrency();
            }
        });

        // Auto-convert on startup (optional)
        window.addEventListener('load', function() {
            // Uncomment to auto-convert on page load
            // convertCurrency();
        });
    </script>
</body>
</html>
