# ============================================================================
# 🎯 SQLMAP PROFESSIONAL SECURITY TESTING - COMPAREWARE
# ============================================================================
# Demostración profesional de testing con SQLMap
# La herramienta más potente del mundo para SQL injection testing
# ============================================================================

Write-Host "🛡️  COMPAREWARE - SQLMAP PROFESSIONAL TESTING" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "🎯 SQLMap Version: 1.9.10.5#dev" -ForegroundColor White
Write-Host "🌐 Target: http://localhost:4000" -ForegroundColor White
Write-Host "⚠️  ETHICAL TESTING ONLY - Own systems" -ForegroundColor Yellow
Write-Host ""

$BaseURL = "http://localhost:4000"
$SQLMapPath = "d:\Repositorio\Bdd Compareware\JavaS\api-node\security\sqlmap"

# Función para ejecutar SQLMap con logging
function Invoke-SQLMapTest {
    param(
        [string]$TestName,
        [string]$Command,
        [string]$Description
    )
    
    Write-Host "🔍 $TestName" -ForegroundColor Yellow
    Write-Host "────────────────────────────────────────" -ForegroundColor Gray
    Write-Host "📝 $Description" -ForegroundColor Cyan
    Write-Host "💻 Comando: $Command" -ForegroundColor Gray
    Write-Host ""
    
    Write-Host "🚀 Ejecutando..." -ForegroundColor Green
    
    # Ejecutar el comando y capturar la salida
    $startTime = Get-Date
    try {
        $result = Invoke-Expression $Command 2>&1
        $endTime = Get-Date
        $duration = ($endTime - $startTime).TotalSeconds
        
        Write-Host "✅ Completado en $([math]::Round($duration,2)) segundos" -ForegroundColor Green
        Write-Host ""
        
        # Analizar resultados básicos
        if ($result -match "403|blocked|forbidden") {
            Write-Host "🛡️  RESULTADO: WAF BLOQUEÓ EL ATAQUE" -ForegroundColor Green
        }
        elseif ($result -match "vulnerable|injection") {
            Write-Host "🚨 RESULTADO: VULNERABILIDAD DETECTADA" -ForegroundColor Red
        }
        else {
            Write-Host "ℹ️  RESULTADO: Sin vulnerabilidades obvias detectadas" -ForegroundColor Blue
        }
    }
    catch {
        Write-Host "❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
    
    Write-Host ""
    Write-Host "═══════════════════════════════════════════" -ForegroundColor Gray
    Write-Host ""
    Start-Sleep -Seconds 2
}

# ============================================================================
# TEST 1: DETECCIÓN BÁSICA DE SQL INJECTION
# ============================================================================

Write-Host "🎯 FASE 1: DETECCIÓN BÁSICA" -ForegroundColor Cyan
Write-Host ""

$cmd1 = "cd '$SQLMapPath'; python sqlmap.py -u '$BaseURL/api/users?id=1' --batch --smart --level=1 --risk=1"
Invoke-SQLMapTest -TestName "Detección Básica GET" -Command $cmd1 -Description "Test inicial para detectar SQL injection en parámetro GET"

# ============================================================================
# TEST 2: TESTING DE FORMULARIO DE LOGIN (POST)
# ============================================================================

Write-Host "🎯 FASE 2: TESTING POST LOGIN" -ForegroundColor Cyan
Write-Host ""

# Crear archivo de datos POST
$postData = 'username=admin&password=test'
$postData | Out-File -FilePath "$SQLMapPath\login_data.txt" -Encoding ASCII

$cmd2 = "cd '$SQLMapPath'; python sqlmap.py -u '$BaseURL/api/auth/login' --data-raw='username=admin&password=test' --batch --smart --level=1 --risk=1"
Invoke-SQLMapTest -TestName "Login Form POST" -Command $cmd2 -Description "Test de SQL injection en formulario de login (método POST)"

# ============================================================================
# TEST 3: TESTING AVANZADO - LEVEL 2
# ============================================================================

Write-Host "🎯 FASE 3: TESTING AVANZADO" -ForegroundColor Cyan  
Write-Host ""

$cmd3 = "cd '$SQLMapPath'; python sqlmap.py -u '$BaseURL/api/users?id=1' --batch --smart --level=2 --risk=2 --technique=BEUST"
Invoke-SQLMapTest -TestName "Test Avanzado Nivel 2" -Command $cmd3 -Description "Testing avanzado con múltiples técnicas (Boolean, Error, Union, Stack, Time)"

# ============================================================================
# TEST 4: BYPASS TECHNIQUES
# ============================================================================

Write-Host "🎯 FASE 4: TÉCNICAS DE BYPASS" -ForegroundColor Cyan
Write-Host ""

$cmd4 = "cd '$SQLMapPath'; python sqlmap.py -u '$BaseURL/api/users?id=1' --batch --smart --tamper=space2comment,equaltolike --level=3 --risk=2"
Invoke-SQLMapTest -TestName "Bypass Techniques" -Command $cmd4 -Description "Testing con técnicas de bypass (tamper scripts) para evadir WAF"

# ============================================================================
# TEST 5: USER-AGENT Y HEADERS TESTING
# ============================================================================

Write-Host "🎯 FASE 5: HEADERS & USER-AGENT" -ForegroundColor Cyan
Write-Host ""

$cmd5 = "cd '$SQLMapPath'; python sqlmap.py -u '$BaseURL/api/users?id=1' --batch --smart --level=3 --risk=1 --test-parameter='User-Agent,Referer'"
Invoke-SQLMapTest -TestName "Headers Testing" -Command $cmd5 -Description "Testing de SQL injection en headers HTTP (User-Agent, Referer)"

# ============================================================================
# TEST 6: TIME-BASED BLIND INJECTION
# ============================================================================

Write-Host "🎯 FASE 6: TIME-BASED TESTING" -ForegroundColor Cyan
Write-Host ""

$cmd6 = "cd '$SQLMapPath'; python sqlmap.py -u '$BaseURL/api/users?id=1' --batch --smart --technique=T --time-sec=5"
Invoke-SQLMapTest -TestName "Time-based Blind" -Command $cmd6 -Description "Testing específico de time-based blind SQL injection"

# ============================================================================
# ANÁLISIS DE LOGS
# ============================================================================

Write-Host "📊 ANÁLISIS POST-TESTING" -ForegroundColor Cyan
Write-Host "═════════════════════════" -ForegroundColor Cyan

# Verificar logs de seguridad
$today = Get-Date -Format "yyyy-MM-dd"
$logPath = "..\logs\security_$today.log"

if (Test-Path $logPath) {
    Write-Host "📋 Analizando logs de seguridad..." -ForegroundColor Yellow
    
    $totalLogs = (Get-Content $logPath | Measure-Object).Count
    $sqlInjectionBlocked = (Get-Content $logPath | Select-String "SQL_INJECTION_BLOCKED").Count
    $wafBlocked = (Get-Content $logPath | Select-String "WAF_BLOCKED").Count
    
    Write-Host "📈 ESTADÍSTICAS DE SEGURIDAD:" -ForegroundColor Green
    Write-Host "   • Total eventos logged: $totalLogs" -ForegroundColor White
    Write-Host "   • SQL Injections bloqueados: $sqlInjectionBlocked" -ForegroundColor Green
    Write-Host "   • Total WAF blocks: $wafBlocked" -ForegroundColor Green
    
    if ($sqlInjectionBlocked -gt 0) {
        Write-Host ""
        Write-Host "🛡️  EXCELENTE: Tu WAF bloqueó $sqlInjectionBlocked ataques SQL injection" -ForegroundColor Green
    }
} else {
    Write-Host "⚠️  No se encontraron logs de seguridad para hoy" -ForegroundColor Yellow
}

# ============================================================================
# EVALUACIÓN FINAL DE SEGURIDAD
# ============================================================================

Write-Host ""
Write-Host "🏆 EVALUACIÓN FINAL DE SEGURIDAD" -ForegroundColor Cyan
Write-Host "═════════════════════════════════" -ForegroundColor Cyan

Write-Host "📋 RESUMEN DE TESTING SQLMAP:" -ForegroundColor Yellow
Write-Host "   ✅ Detección básica completada" -ForegroundColor Green
Write-Host "   ✅ Testing POST forms completado" -ForegroundColor Green  
Write-Host "   ✅ Testing avanzado nivel 2 completado" -ForegroundColor Green
Write-Host "   ✅ Técnicas de bypass probadas" -ForegroundColor Green
Write-Host "   ✅ Headers testing completado" -ForegroundColor Green
Write-Host "   ✅ Time-based testing completado" -ForegroundColor Green

Write-Host ""
Write-Host "🎓 PARA TU PROYECTO ESCOLAR:" -ForegroundColor Cyan
Write-Host "   • Demuestra testing profesional con SQLMap" -ForegroundColor White
Write-Host "   • Implementación de WAF multicapa" -ForegroundColor White
Write-Host "   • Logging y monitoreo de seguridad" -ForegroundColor White
Write-Host "   • Técnicas ofensivas y defensivas" -ForegroundColor White

Write-Host ""
Write-Host "📚 COMANDOS ADICIONALES PARA PROBAR:" -ForegroundColor Yellow
Write-Host "─────────────────────────────────────" -ForegroundColor Gray
Write-Host "# Enumerar databases:" -ForegroundColor White
Write-Host "python sqlmap.py -u '$BaseURL/api/users?id=1' --dbs --batch" -ForegroundColor Gray
Write-Host ""
Write-Host "# Enumerar tablas:" -ForegroundColor White  
Write-Host "python sqlmap.py -u '$BaseURL/api/users?id=1' --tables --batch" -ForegroundColor Gray
Write-Host ""
Write-Host "# Dump data:" -ForegroundColor White
Write-Host "python sqlmap.py -u '$BaseURL/api/users?id=1' --dump --batch" -ForegroundColor Gray
Write-Host ""
Write-Host "# WAF identification:" -ForegroundColor White
Write-Host "python sqlmap.py -u '$BaseURL/api/users?id=1' --identify-waf --batch" -ForegroundColor Gray

Write-Host ""
Write-Host "⚠️  RECORDATORIO ÉTICO:" -ForegroundColor Red
Write-Host "   Solo usa estas técnicas en tus propios sistemas" -ForegroundColor Yellow
Write-Host "   El testing no autorizado es ilegal" -ForegroundColor Yellow
Write-Host ""
Write-Host "🎉 DEMOSTRACIÓN SQLMAP COMPLETADA" -ForegroundColor Green
Write-Host "═════════════════════════════════════" -ForegroundColor Green