# ============================================================================
# 🎯 SQL INJECTION QUICK TEST - COMPAREWARE (VERSIÓN SIMPLE)
# ============================================================================

$BaseURL = "http://localhost:4000"
$TestCount = 0
$BlockedCount = 0
$VulnerableCount = 0

Write-Host "🛡️  COMPAREWARE SQL INJECTION TEST" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════" -ForegroundColor Cyan
Write-Host "🎯 Target: $BaseURL" -ForegroundColor White
Write-Host ""

function Test-SQLInjection {
    param(
        [string]$TestURL,
        [string]$TestName
    )
    
    $script:TestCount++
    Write-Host "🧪 Testing: $TestName" -ForegroundColor Blue
    
    try {
        $response = Invoke-RestMethod -Uri $TestURL -TimeoutSec 5 -ErrorAction Stop
        
        # Si llegamos aquí, el request pasó
        if ($response -match "error|mysql|postgresql|sql|syntax") {
            Write-Host "   🚨 VULNERABLE - Error de BD detectado" -ForegroundColor Red
            $script:VulnerableCount++
        } else {
            Write-Host "   ⚠️  PASÓ (200) - Revisar manualmente" -ForegroundColor Yellow
        }
    }
    catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        
        if ($statusCode -eq 403) {
            Write-Host "   ✅ BLOQUEADO (403) - WAF funcionando" -ForegroundColor Green
            $script:BlockedCount++
        }
        elseif ($statusCode -eq 404) {
            Write-Host "   ℹ️  Endpoint no encontrado (404)" -ForegroundColor Cyan
        }
        else {
            Write-Host "   ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    
    Start-Sleep -Milliseconds 500
}

# ============================================================================
# TESTS BÁSICOS
# ============================================================================

Write-Host "🔍 TESTS BÁSICOS" -ForegroundColor Yellow
Write-Host "───────────────" -ForegroundColor Yellow

Test-SQLInjection "$BaseURL/api/users?id=1'" "Comilla simple"
Test-SQLInjection "$BaseURL/api/users?id=1--" "Comentario SQL"
Test-SQLInjection "$BaseURL/api/users?id=1;" "Punto y coma"
Test-SQLInjection "$BaseURL/api/users?id=1' OR 1=1--" "OR injection"

Write-Host ""
Write-Host "🔗 UNION-BASED TESTS" -ForegroundColor Yellow  
Write-Host "───────────────────" -ForegroundColor Yellow

Test-SQLInjection "$BaseURL/api/users?id=1' UNION SELECT 1,2,3--" "UNION básico"
Test-SQLInjection "$BaseURL/api/users?id=1' UNION SELECT null,null--" "UNION NULL"

Write-Host ""
Write-Host "🎭 BYPASS TESTS" -ForegroundColor Yellow
Write-Host "──────────────" -ForegroundColor Yellow

Test-SQLInjection "$BaseURL/api/users?id=1%27%20OR%201%3D1--" "URL encoding"
Test-SQLInjection "$BaseURL/api/users?id=1' UnIoN sElEcT 1--" "Case variation"

# ============================================================================
# AUTH BYPASS (usando WebRequest para POST)
# ============================================================================

Write-Host ""
Write-Host "🚪 AUTHENTICATION BYPASS" -ForegroundColor Yellow
Write-Host "────────────────────────" -ForegroundColor Yellow

try {
    Write-Host "🧪 Testing: Admin bypass" -ForegroundColor Blue
    
    $adminBypass = @{
        username = "admin'--"
        password = "anything"
    } | ConvertTo-Json
    
    $response = Invoke-WebRequest -Uri "$BaseURL/api/auth/login" -Method POST -Body $adminBypass -ContentType "application/json" -TimeoutSec 5 -ErrorAction SilentlyContinue
    
    if ($response.StatusCode -eq 403) {
        Write-Host "   ✅ BLOQUEADO (403) - WAF funcionando" -ForegroundColor Green
        $BlockedCount++
    }
    elseif ($response.StatusCode -eq 200 -and $response.Content -match "success.*true") {
        Write-Host "   🚨 CRÍTICO - Authentication bypass exitoso" -ForegroundColor Red
        $VulnerableCount++
    }
    else {
        Write-Host "   ✅ SEGURO - Login falló correctamente" -ForegroundColor Green  
    }
    $TestCount++
}
catch {
    if ($_.Exception.Response.StatusCode.value__ -eq 403) {
        Write-Host "   ✅ BLOQUEADO (403) - WAF funcionando" -ForegroundColor Green
        $BlockedCount++
    } else {
        Write-Host "   ❌ Error: $($_.Exception.Message)" -ForegroundColor Red
    }
    $TestCount++
}

# ============================================================================
# TIME-BASED TEST
# ============================================================================

Write-Host ""
Write-Host "⏱️  TIME-BASED TEST" -ForegroundColor Yellow
Write-Host "──────────────────" -ForegroundColor Yellow

Write-Host "🧪 Testing: SLEEP injection" -ForegroundColor Blue
$startTime = Get-Date

try {
    $response = Invoke-RestMethod -Uri "$BaseURL/api/users?id=1' AND SLEEP(3)--" -TimeoutSec 6 -ErrorAction Stop
    $elapsed = ((Get-Date) - $startTime).TotalSeconds
    
    if ($elapsed -ge 2.5) {
        Write-Host "   🚨 VULNERABLE - Delay detectado ($([math]::Round($elapsed,1))s)" -ForegroundColor Red
        $VulnerableCount++
    } else {
        Write-Host "   ✅ SEGURO - Sin delay anómalo ($([math]::Round($elapsed,1))s)" -ForegroundColor Green
    }
}
catch {
    $elapsed = ((Get-Date) - $startTime).TotalSeconds
    
    if ($_.Exception.Response.StatusCode.value__ -eq 403) {
        Write-Host "   ✅ BLOQUEADO (403) - WAF funcionando ($([math]::Round($elapsed,1))s)" -ForegroundColor Green
        $BlockedCount++
    } else {
        Write-Host "   ❌ Error después de $([math]::Round($elapsed,1))s" -ForegroundColor Red
    }
}

$TestCount++

# ============================================================================
# RESULTADOS
# ============================================================================

Write-Host ""
Write-Host "📊 RESUMEN FINAL" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════" -ForegroundColor Cyan
Write-Host "🧪 Tests ejecutados: $TestCount" -ForegroundColor White
Write-Host "✅ Tests bloqueados: $BlockedCount" -ForegroundColor Green  
Write-Host "🚨 Vulnerabilidades: $VulnerableCount" -ForegroundColor Red

Write-Host ""
if ($VulnerableCount -eq 0 -and $BlockedCount -gt 0) {
    Write-Host "🛡️  RESULTADO: SISTEMA SEGURO" -ForegroundColor Green
    Write-Host "   Tu WAF está protegiendo correctamente" -ForegroundColor Green
}
elseif ($VulnerableCount -gt 0) {
    Write-Host "🚨 RESULTADO: VULNERABILIDADES DETECTADAS" -ForegroundColor Red  
    Write-Host "   ⚠️  REVISAR SISTEMA INMEDIATAMENTE" -ForegroundColor Red
}
else {
    Write-Host "⚠️  RESULTADO: REVISAR CONFIGURACIÓN" -ForegroundColor Yellow
    Write-Host "   Pocos tests fueron bloqueados" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "📋 Próximos pasos:" -ForegroundColor Cyan
Write-Host "   1. Revisar logs: Get-Content ..\logs\security_*.log" -ForegroundColor White
Write-Host "   2. Test interactivo: .\InteractiveTesting.ps1" -ForegroundColor White
Write-Host "   3. Documentación: .\README_TESTING.md" -ForegroundColor White

Write-Host ""
Write-Host "═══════════════════════════════════════" -ForegroundColor Cyan