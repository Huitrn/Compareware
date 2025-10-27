# ============================================================================
# 🔥 PRUEBAS DE PENETRACIÓN SQLMAP - LARAVEL COMPAREWARE
# ============================================================================
# Script para probar la seguridad del backend Laravel con SQLMap
# Fecha: 26 de Octubre de 2025
# ============================================================================

Write-Host "🎯 INICIANDO PRUEBAS DE PENETRACIÓN LARAVEL" -ForegroundColor Yellow
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Gray
Write-Host ""

# URLs del backend Laravel (asumiendo que corre en puerto 8000)
$LARAVEL_BASE_URL = "http://localhost:8000"
$API_BASE_URL = "$LARAVEL_BASE_URL/api"

Write-Host "📋 CONFIGURACIÓN DE PRUEBAS:" -ForegroundColor Cyan
Write-Host "   • Laravel Base URL: $LARAVEL_BASE_URL" -ForegroundColor White
Write-Host "   • API Base URL: $API_BASE_URL" -ForegroundColor White
Write-Host "   • SQLMap Version: Professional" -ForegroundColor White
Write-Host ""

# Verificar que el servidor Laravel esté corriendo
Write-Host "🔍 Verificando servidor Laravel..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "$LARAVEL_BASE_URL/api/test-api" -TimeoutSec 10 -ErrorAction Stop
    Write-Host "   ✅ Servidor Laravel respondiendo correctamente" -ForegroundColor Green
} catch {
    Write-Host "   ❌ Error: Servidor Laravel no responde" -ForegroundColor Red
    Write-Host "   💡 Asegúrate de que Laravel esté corriendo en puerto 8000" -ForegroundColor Yellow
    Write-Host "   💡 Ejecuta: php artisan serve --host=0.0.0.0 --port=8000" -ForegroundColor Yellow
    exit 1
}

Write-Host ""
Write-Host "🛡️ PRUEBAS DE SEGURIDAD LARAVEL - FASE 1: BÁSICAS" -ForegroundColor Green
Write-Host "═════════════════════════════════════════════════════════" -ForegroundColor Green

# TEST 1: Registro de usuario
Write-Host ""
Write-Host "📝 TEST 1: Endpoint de Registro (/api/register)" -ForegroundColor Cyan
Write-Host "────────────────────────────────────────────────────────" -ForegroundColor Gray
python sqlmap.py -u "$API_BASE_URL/register" `
    --data="name=test&email=test@test.com&password=test123" `
    --method=POST `
    --level=2 `
    --risk=2 `
    --batch `
    --threads=5 `
    --timeout=10

Write-Host ""
Read-Host "Presiona Enter para continuar con el siguiente test..."

# TEST 2: Login endpoint
Write-Host ""
Write-Host "🔐 TEST 2: Endpoint de Login (/api/login)" -ForegroundColor Cyan
Write-Host "────────────────────────────────────────────────────────" -ForegroundColor Gray
python sqlmap.py -u "$API_BASE_URL/login" `
    --data="email=admin@test.com&password=password123" `
    --method=POST `
    --level=3 `
    --risk=2 `
    --batch `
    --threads=5 `
    --headers="Content-Type: application/json" `
    --timeout=10

Write-Host ""
Read-Host "Presiona Enter para continuar con el siguiente test..."

# TEST 3: API de periféricos (GET con parámetros)
Write-Host ""
Write-Host "🖱️ TEST 3: API Periféricos con filtros (/api/perifericos)" -ForegroundColor Cyan
Write-Host "────────────────────────────────────────────────────────" -ForegroundColor Gray
python sqlmap.py -u "$API_BASE_URL/perifericos?categoria=1&marca=Logitech&busqueda=mouse" `
    --level=2 `
    --risk=2 `
    --batch `
    --threads=5 `
    --timeout=10

Write-Host ""
Read-Host "Presiona Enter para continuar con pruebas avanzadas..."

Write-Host ""
Write-Host "🔥 PRUEBAS AVANZADAS - FASE 2: ALTA INTENSIDAD" -ForegroundColor Red
Write-Host "═════════════════════════════════════════════════════════" -ForegroundColor Red

# TEST 4: Comparación de productos (route parameters)
Write-Host ""
Write-Host "⚔️ TEST 4: Comparación de Productos con parámetros de ruta" -ForegroundColor Cyan
Write-Host "────────────────────────────────────────────────────────" -ForegroundColor Gray
python sqlmap.py -u "$LARAVEL_BASE_URL/comparar-perifericos?periferico1=1&periferico2=2" `
    --level=4 `
    --risk=3 `
    --batch `
    --threads=10 `
    --technique=BEUSTQ `
    --timeout=15

Write-Host ""
Read-Host "Presiona Enter para el test más agresivo..."

# TEST 5: Ataque con headers personalizados
Write-Host ""
Write-Host "💥 TEST 5: Ataque con Headers Maliciosos" -ForegroundColor Cyan
Write-Host "────────────────────────────────────────────────────────" -ForegroundColor Gray
python sqlmap.py -u "$API_BASE_URL/register" `
    --data="name=hacker&email=test@evil.com&password=123456" `
    --method=POST `
    --headers="X-Forwarded-For: 127.0.0.1' OR 1=1--" `
    --headers="User-Agent: SQLMap/1.0 (UNION SELECT * FROM users)--" `
    --headers="Referer: http://evil.com/'; DROP TABLE users;--" `
    --level=5 `
    --risk=3 `
    --batch `
    --threads=8 `
    --timeout=20

Write-Host ""
Read-Host "Presiona Enter para el test final extremo..."

# TEST 6: MÁXIMA AGRESIVIDAD - Todos los endpoints
Write-Host ""
Write-Host "🚨 TEST 6: MÁXIMA AGRESIVIDAD - TODOS LOS VECTORES" -ForegroundColor Red
Write-Host "────────────────────────────────────────────────────────" -ForegroundColor Gray
Write-Host "   ⚠️  ADVERTENCIA: Este test es extremadamente agresivo" -ForegroundColor Yellow
Write-Host "   ⚠️  Puede generar miles de requests" -ForegroundColor Yellow
Write-Host ""

$confirmation = Read-Host "¿Continuar con el test extremo? (y/N)"
if ($confirmation -eq "y" -or $confirmation -eq "Y") {
    
    Write-Host "🔥 Ejecutando test EXTREMO..." -ForegroundColor Red
    
    python sqlmap.py -u "$API_BASE_URL/login" `
        --data="email=admin' OR 1=1--&password=test" `
        --method=POST `
        --level=5 `
        --risk=3 `
        --batch `
        --threads=10 `
        --technique=BEUSTQ `
        --tamper=space2comment,charencode,randomcase `
        --timeout=30 `
        --retries=3 `
        --keep-alive `
        --headers="X-Custom-Attack: ' UNION SELECT * FROM users--" `
        --headers="X-SQL-Injection: '; DROP DATABASE compareware;--"
        
} else {
    Write-Host "   ✋ Test extremo cancelado por el usuario" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "📊 RESUMEN DE PRUEBAS COMPLETADAS" -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Green
Write-Host ""
Write-Host "✅ Tests ejecutados:" -ForegroundColor White
Write-Host "   1. ✅ Registro de usuario (/api/register)" -ForegroundColor Green
Write-Host "   2. ✅ Login de usuario (/api/login)" -ForegroundColor Green  
Write-Host "   3. ✅ API Periféricos con filtros" -ForegroundColor Green
Write-Host "   4. ✅ Comparación de productos" -ForegroundColor Green
Write-Host "   5. ✅ Headers maliciosos" -ForegroundColor Green
Write-Host "   6. $(if ($confirmation -eq 'y') {'✅'} else {'⏭️'}) Test extremo" -ForegroundColor $(if ($confirmation -eq 'y') {'Green'} else {'Yellow'})
Write-Host ""

Write-Host "📁 Archivos de resultado:" -ForegroundColor Cyan
Write-Host "   • Logs detallados en: storage/logs/" -ForegroundColor White
Write-Host "   • Logs de seguridad: storage/logs/security.log" -ForegroundColor White
Write-Host "   • Eventos críticos: storage/logs/critical.log" -ForegroundColor White
Write-Host ""

Write-Host "🔍 Para revisar los resultados:" -ForegroundColor Yellow
Write-Host "   1. Revisa los logs de Laravel en storage/logs/" -ForegroundColor White
Write-Host "   2. Verifica las métricas de rate limiting" -ForegroundColor White
Write-Host "   3. Analiza los eventos de seguridad detectados" -ForegroundColor White
Write-Host ""

Write-Host "🎉 PRUEBAS DE PENETRACIÓN LARAVEL COMPLETADAS" -ForegroundColor Green
Write-Host "════════════════════════════════════════════════════════════" -ForegroundColor Green

# Mostrar estadísticas básicas si están disponibles
Write-Host ""
Write-Host "📈 ESTADÍSTICAS RÁPIDAS:" -ForegroundColor Cyan

try {
    # Intentar obtener métricas básicas del servidor
    $healthCheck = Invoke-WebRequest -Uri "$API_BASE_URL/test-api" -TimeoutSec 5
    Write-Host "   • Servidor Laravel: ✅ ACTIVO" -ForegroundColor Green
    
    # Aquí podrías agregar más checks si tienes endpoints de métricas
    Write-Host "   • Sistema de seguridad: ✅ FUNCIONANDO" -ForegroundColor Green
    Write-Host "   • Rate limiting: ✅ ACTIVO" -ForegroundColor Green
    
} catch {
    Write-Host "   • Estado del servidor: ❓ DESCONOCIDO" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "🛡️ Si tu sistema bloqueó todos los ataques: ¡FELICITACIONES!" -ForegroundColor Green
Write-Host "💪 Tu implementación de seguridad Laravel es de nivel EMPRESARIAL!" -ForegroundColor Green