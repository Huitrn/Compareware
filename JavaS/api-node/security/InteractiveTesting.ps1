# 🎯 Script Interactivo de Testing Manual SQL Injection - Windows PowerShell
# Proyecto: Compareware - Desarrollo Backend

# Configuración
$BaseURL = "http://localhost:3000"
$LogFile = "manual_test_results_$(Get-Date -Format 'yyyyMMdd_HHmmss').log"

# Función para mostrar banner
function Show-Banner {
    Write-Host "╔══════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
    Write-Host "║                    🛡️  COMPAREWARE SQL INJECTION                 ║" -ForegroundColor Cyan  
    Write-Host "║                        MANUAL TESTING SUITE                     ║" -ForegroundColor Cyan
    Write-Host "║                                                                  ║" -ForegroundColor Cyan
    Write-Host "║  ⚠️  SOLO PARA TESTING DE TU PROPIO SISTEMA - USO EDUCATIVO     ║" -ForegroundColor Yellow
    Write-Host "╚══════════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
    Write-Host ""
}

# Función para logging
function Write-TestLog {
    param(
        [string]$Message,
        [string]$Color = "White"
    )
    
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    $logEntry = "[$timestamp] $Message"
    
    Add-Content -Path $LogFile -Value $logEntry
    Write-Host $Message -ForegroundColor $Color
}

# Función de testing principal
function Test-SQLPayload {
    param(
        [string]$Description,
        [string]$URL,
        [string]$Method = "GET",
        [string]$Body = "",
        [string]$Expected = "blocked"
    )
    
    Write-Host "`n🧪 Testing: $Description" -ForegroundColor Blue
    Write-Host "URL: $URL" -ForegroundColor Magenta
    
    try {
        $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
        
        if ($Method -eq "GET") {
            $response = Invoke-WebRequest -Uri $URL -Method GET -TimeoutSec 10 -ErrorAction SilentlyContinue
        } else {
            $headers = @{"Content-Type" = "application/json"}
            $response = Invoke-WebRequest -Uri $URL -Method $Method -Body $Body -Headers $headers -TimeoutSec 10 -ErrorAction SilentlyContinue
        }
        
        $stopwatch.Stop()
        $timeElapsed = $stopwatch.ElapsedMilliseconds
        
        # Análisis de la respuesta
        $statusCode = $response.StatusCode
        $responseBody = $response.Content
        
        if ($statusCode -eq 403 -or $responseBody -match "blocked|ACCESS_DENIED|security") {
            Write-TestLog "✅ BLOCKED - $Description (HTTP: $statusCode, Time: ${timeElapsed}ms)" "Green"
            return $true
        }
        elseif ($statusCode -eq 200) {
            if ($responseBody -match "mysql_|PostgreSQL|ORA-|SQL Server|syntax error") {
                Write-TestLog "🚨 VULNERABLE! Database error exposed - $Description" "Red"
                Write-Host "Error details: $($responseBody.Substring(0, [Math]::Min(200, $responseBody.Length)))..." -ForegroundColor Red
                return $false
            }
            elseif ($responseBody -match "users|admin|database|table") {
                Write-TestLog "⚠️  SUSPICIOUS RESPONSE - Possible data leakage - $Description" "Yellow" 
                return $null
            }
            else {
                Write-TestLog "⚠️  PASSED - No obvious vulnerability signs - $Description (Time: ${timeElapsed}ms)" "Yellow"
                return $null
            }
        }
        else {
            Write-TestLog "ℹ️  OTHER RESPONSE - HTTP $statusCode - $Description (Time: ${timeElapsed}ms)" "Cyan"
            return $true
        }
    }
    catch {
        if ($_.Exception.Message -match "timeout") {
            Write-TestLog "⏱️  TIMEOUT - Possible time-based injection - $Description" "Yellow"
            return $null
        }
        else {
            Write-TestLog "❌ ERROR - $($_.Exception.Message) - $Description" "Red"
            return $true
        }
    }
}

# Verificar servidor
function Test-ServerConnection {
    Write-Host "🔍 Verificando conexión al servidor..." -ForegroundColor Blue
    
    try {
        $response = Invoke-WebRequest -Uri $BaseURL -TimeoutSec 5 -ErrorAction Stop
        Write-Host "✅ Servidor accesible en $BaseURL" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "❌ Servidor no accesible en $BaseURL" -ForegroundColor Red
        Write-Host "Por favor, inicia tu servidor primero:" -ForegroundColor Yellow
        Write-Host "  cd JavaS/api-node && npm start" -ForegroundColor Cyan
        return $false
    }
}

# Menu principal
function Show-Menu {
    Write-Host "`n📋 MENU DE TESTING" -ForegroundColor Cyan
    Write-Host "1. 🎯 Test Básico de Vulnerabilidades"
    Write-Host "2. 🔍 Test de Boolean-Based Blind SQL Injection"
    Write-Host "3. 🔗 Test de Union-Based SQL Injection"
    Write-Host "4. ⏱️  Test de Time-Based Blind SQL Injection"
    Write-Host "5. 💥 Test de Error-Based SQL Injection"
    Write-Host "6. 🚪 Test de Authentication Bypass"
    Write-Host "7. 🎭 Test de Bypass Techniques"
    Write-Host "8. 🚀 Test Completo (Todos los anteriores)"
    Write-Host "9. 📊 Ver Resultados y Estadísticas"
    Write-Host "0. ❌ Salir"
    Write-Host ""
    $choice = Read-Host "Selecciona una opción (0-9)"
    return $choice
}

# Tests individuales
function Test-Basic {
    Write-Host "`n🎯 INICIANDO TESTS BÁSICOS" -ForegroundColor Yellow
    Write-Host "Estos tests verifican respuestas a caracteres SQL comunes"
    
    Test-SQLPayload "Comilla simple" "$BaseURL/api/users/1'"
    Test-SQLPayload "Punto y coma" "$BaseURL/api/users/1;"
    Test-SQLPayload "Comentario SQL" "$BaseURL/api/users/1--"
    Test-SQLPayload "Comentario de bloque" "$BaseURL/api/users/1/*"
    
    Write-Host "`n✅ Tests básicos completados" -ForegroundColor Green
}

function Test-BooleanBlind {
    Write-Host "`n🔍 INICIANDO BOOLEAN-BASED BLIND SQL INJECTION TESTS" -ForegroundColor Yellow
    
    Test-SQLPayload "Condición TRUE (1=1)" "$BaseURL/api/users?id=1' AND 1=1--"
    Test-SQLPayload "Condición FALSE (1=2)" "$BaseURL/api/users?id=1' AND 1=2--"
    Test-SQLPayload "OR TRUE" "$BaseURL/api/users?id=1' OR 1=1--"
    Test-SQLPayload "Verificación longitud BD" "$BaseURL/api/users?id=1' AND LENGTH(DATABASE())>5--"
    Test-SQLPayload "Extracción carácter" "$BaseURL/api/users?id=1' AND SUBSTRING(DATABASE(),1,1)='c'--"
    
    Write-Host "`n✅ Tests boolean blind completados" -ForegroundColor Green
}

function Test-UnionBased {
    Write-Host "`n🔗 INICIANDO UNION-BASED SQL INJECTION TESTS" -ForegroundColor Yellow
    
    Test-SQLPayload "UNION básico" "$BaseURL/api/users?id=1' UNION SELECT 1,2,3--"
    Test-SQLPayload "UNION con NULL" "$BaseURL/api/users?id=1' UNION SELECT null,null,null--"
    Test-SQLPayload "Extraer info del sistema" "$BaseURL/api/users?id=1' UNION SELECT database(),user(),version()--"
    Test-SQLPayload "Listar tablas" "$BaseURL/api/users?id=1' UNION SELECT table_name,null,null FROM information_schema.tables--"
    
    Write-Host "`n✅ Tests union-based completados" -ForegroundColor Green
}

function Test-TimeBased {
    Write-Host "`n⏱️  INICIANDO TIME-BASED BLIND SQL INJECTION TESTS" -ForegroundColor Yellow
    Write-Host "Nota: Cada test puede tardar hasta 5 segundos si es vulnerable" -ForegroundColor Cyan
    
    Test-SQLPayload "MySQL SLEEP" "$BaseURL/api/users?id=1' AND SLEEP(3)--"
    Test-SQLPayload "PostgreSQL SLEEP" "$BaseURL/api/users?id=1'; SELECT PG_SLEEP(3)--"
    Test-SQLPayload "SQL Server WAITFOR" "$BaseURL/api/users?id=1'; WAITFOR DELAY '00:00:03'--"
    
    Write-Host "`n✅ Tests time-based completados" -ForegroundColor Green
}

function Test-ErrorBased {
    Write-Host "`n💥 INICIANDO ERROR-BASED SQL INJECTION TESTS" -ForegroundColor Yellow
    
    Test-SQLPayload "Error de sintaxis básico" "$BaseURL/api/users?id=1''"
    Test-SQLPayload "MySQL EXTRACTVALUE" "$BaseURL/api/users?id=1' AND EXTRACTVALUE(1,CONCAT(0x7e,(SELECT database()),0x7e))--"
    Test-SQLPayload "PostgreSQL CAST error" "$BaseURL/api/users?id=1' AND CAST((SELECT version()) AS int)--"
    
    Write-Host "`n✅ Tests error-based completados" -ForegroundColor Green
}

function Test-AuthBypass {
    Write-Host "`n🚪 INICIANDO AUTHENTICATION BYPASS TESTS" -ForegroundColor Yellow
    
    $body1 = '{"username":"admin'\''--","password":"anything"}'
    Test-SQLPayload "Admin bypass básico" "$BaseURL/api/auth/login" "POST" $body1
    
    $body2 = '{"username":"admin'\'' OR 1=1--","password":""}'
    Test-SQLPayload "OR 1=1 bypass" "$BaseURL/api/auth/login" "POST" $body2
    
    $body3 = '{"username":"admin'\''/*","password":"anything"}'
    Test-SQLPayload "Comentario bypass" "$BaseURL/api/auth/login" "POST" $body3
    
    Write-Host "`n✅ Tests authentication bypass completados" -ForegroundColor Green
}

function Test-BypassTechniques {
    Write-Host "`n🎭 INICIANDO BYPASS TECHNIQUES TESTS" -ForegroundColor Yellow
    
    # URL Encoding
    Test-SQLPayload "URL Encoding" "$BaseURL/api/users?id=1%27%20OR%201%3D1--"
    
    # Comment bypass
    Test-SQLPayload "Comment bypass UNION" "$BaseURL/api/users?id=1' UN/**/ION SE/**/LECT 1,2,3--"
    
    # Case variation  
    Test-SQLPayload "Case variation" "$BaseURL/api/users?id=1' UnIoN sElEcT 1,2,3--"
    
    Write-Host "`n✅ Tests bypass techniques completados" -ForegroundColor Green
}

function Test-FullSuite {
    Write-Host "`n🚀 EJECUTANDO SUITE COMPLETA DE TESTS" -ForegroundColor Magenta
    
    Test-Basic
    Test-BooleanBlind
    Test-UnionBased
    Test-TimeBased
    Test-ErrorBased
    Test-AuthBypass
    Test-BypassTechniques
    
    Show-Statistics
}

function Show-Statistics {
    Write-Host "`n📊 ESTADÍSTICAS DE TESTING" -ForegroundColor Cyan
    Write-Host "═══════════════════════════════════════"
    
    if (Test-Path $LogFile) {
        $logContent = Get-Content $LogFile
        
        $totalTests = ($logContent | Select-String "Testing:").Count
        $blockedTests = ($logContent | Select-String "✅ BLOCKED").Count
        $vulnerableTests = ($logContent | Select-String "🚨 VULNERABLE").Count
        $suspiciousTests = ($logContent | Select-String "⚠️").Count
        
        Write-Host "📝 Total de tests ejecutados: $totalTests" -ForegroundColor Blue
        Write-Host "🛡️  Tests bloqueados (seguro): $blockedTests" -ForegroundColor Green
        Write-Host "🚨 Vulnerabilidades encontradas: $vulnerableTests" -ForegroundColor Red
        Write-Host "⚠️  Respuestas sospechosas: $suspiciousTests" -ForegroundColor Yellow
        
        if ($totalTests -gt 0) {
            $securityScore = [Math]::Round(($blockedTests * 100) / $totalTests)
            Write-Host "📊 Score de seguridad: ${securityScore}%" -ForegroundColor Cyan
            
            switch ($securityScore) {
                {$_ -ge 90} { Write-Host "🎯 Nivel de seguridad: EXCELENTE" -ForegroundColor Green }
                {$_ -ge 70} { Write-Host "🎯 Nivel de seguridad: BUENO" -ForegroundColor Blue }
                {$_ -ge 50} { Write-Host "🎯 Nivel de seguridad: NECESITA MEJORAS" -ForegroundColor Yellow }
                default { Write-Host "🎯 Nivel de seguridad: CRÍTICO" -ForegroundColor Red }
            }
        }
        
        Write-Host "`n📄 Log detallado guardado en: $LogFile" -ForegroundColor Cyan
        
        Write-Host "`n📋 ÚLTIMOS RESULTADOS:" -ForegroundColor Yellow
        Get-Content $LogFile | Select-Object -Last 10 | ForEach-Object { Write-Host "  $_" }
    }
    else {
        Write-Host "No se encontró archivo de log" -ForegroundColor Red
    }
}

# Función principal
function Start-InteractiveTesting {
    Show-Banner
    
    Write-Host "Inicializando sistema de testing..." -ForegroundColor Blue
    Write-Host "Log file: $LogFile" -ForegroundColor Cyan
    
    # Verificar servidor
    if (-not (Test-ServerConnection)) {
        return
    }
    
    Write-Host "`n💡 Tip: Los resultados se guardan automáticamente en $LogFile" -ForegroundColor Yellow
    
    # Loop principal
    do {
        $choice = Show-Menu
        
        switch ($choice) {
            "1" { Test-Basic }
            "2" { Test-BooleanBlind }
            "3" { Test-UnionBased }
            "4" { Test-TimeBased }
            "5" { Test-ErrorBased }
            "6" { Test-AuthBypass }
            "7" { Test-BypassTechniques }
            "8" { Test-FullSuite }
            "9" { Show-Statistics }
            "0" { 
                Write-Host "`n👋 ¡Gracias por usar Compareware Security Testing Suite!" -ForegroundColor Green
                Write-Host "📄 Resultados guardados en: $LogFile" -ForegroundColor Cyan
                break
            }
            default { 
                Write-Host "❌ Opción inválida. Por favor selecciona 0-9." -ForegroundColor Red
            }
        }
        
        if ($choice -ne "0") {
            Write-Host ""
            Read-Host "Presiona Enter para continuar"
        }
        
    } while ($choice -ne "0")
}

# Comandos rápidos adicionales
function Test-QuickSQLI {
    Write-Host "🚀 QUICK SQL INJECTION TEST" -ForegroundColor Cyan
    
    # Test esencial
    $test1 = Test-SQLPayload "Boolean test" "$BaseURL/api/users?id=1' AND 1=1--"
    $test2 = Test-SQLPayload "Union test" "$BaseURL/api/users?id=1' UNION SELECT 1,2,3--"
    
    $body = '{"username":"admin'\'' OR 1=1--","password":""}'
    $test3 = Test-SQLPayload "Auth bypass test" "$BaseURL/api/auth/login" "POST" $body
    
    Write-Host "✅ Quick test completed!" -ForegroundColor Green
}

# Exportar funciones para uso individual
Export-ModuleMember -Function Start-InteractiveTesting, Test-QuickSQLI

# Si se ejecuta directamente, iniciar testing interactivo
if ($MyInvocation.InvocationName -ne '.') {
    Start-InteractiveTesting
}