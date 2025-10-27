# 🎯 Comandos Rápidos de Testing SQL Injection para PowerShell
# Proyecto: Compareware - Desarrollo Backend
# Ejecuta desde: JavaS\api-node\security\

# Configuración
$BaseURL = "http://localhost:4000"

Write-Host "🛡️  COMPAREWARE - COMANDOS RÁPIDOS DE TESTING SQL" -ForegroundColor Cyan
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "⚠️  RECORDATORIO: Solo para testing de tu propio sistema" -ForegroundColor Yellow
Write-Host ""

# Función helper para testing rápido
function Test-URL {
    param([string]$URL, [string]$Description)
    
    try {
        Write-Host "🧪 $Description" -ForegroundColor Blue
        $response = Invoke-WebRequest -Uri $URL -TimeoutSec 5 -ErrorAction SilentlyContinue
        
        if ($response.StatusCode -eq 403) {
            Write-Host "   ✅ BLOQUEADO (403) - Seguro" -ForegroundColor Green
        }
        elseif ($response.StatusCode -eq 200) {
            if ($response.Content -match "mysql|sql|error|syntax") {
                Write-Host "   🚨 VULNERABLE - Error de BD detectado" -ForegroundColor Red
            }
            else {
                Write-Host "   ⚠️  PASÓ (200) - Revisar respuesta" -ForegroundColor Yellow
            }
        }
        else {
            Write-Host "   ℹ️  Código: $($response.StatusCode)" -ForegroundColor Cyan
        }
    }
    catch {
        if ($_.Exception.Message -match "timeout") {
            Write-Host "   ⏱️  TIMEOUT - Posible time-based injection" -ForegroundColor Yellow
        }
        else {
            Write-Host "   ❌ ERROR: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    
    Start-Sleep -Milliseconds 500  # Rate limiting

Write-Host "🔍 TESTS BÁSICOS DE SQL INJECTION" -ForegroundColor Yellow
Write-Host "────────────────────────────────────" -ForegroundColor Yellow

# 1. Tests básicos
Test-URL "$BaseURL/api/users/1'" "Comilla simple"
Test-URL "$BaseURL/api/users/1;" "Punto y coma" 
Test-URL "$BaseURL/api/users/1--" "Comentario SQL"

Write-Host ""
Write-Host "🔗 TESTS DE UNION-BASED INJECTION" -ForegroundColor Yellow
Write-Host "──────────────────────────────────────" -ForegroundColor Yellow

# 2. Union-based tests
Test-URL "$BaseURL/api/users?id=1' UNION SELECT 1,2,3--" "UNION básico"
Test-URL "$BaseURL/api/users?id=1' UNION SELECT null,null,null--" "UNION con NULL"

Write-Host ""
Write-Host "🔍 TESTS DE BOOLEAN BLIND" -ForegroundColor Yellow  
Write-Host "─────────────────────────────" -ForegroundColor Yellow

# 3. Boolean blind tests
Test-URL "$BaseURL/api/users?id=1' AND 1=1--" "Condición TRUE"
Test-URL "$BaseURL/api/users?id=1' AND 1=2--" "Condición FALSE"
Test-URL "$BaseURL/api/users?id=1' OR 1=1--" "OR TRUE"

Write-Host ""
Write-Host "🚪 TESTS DE AUTHENTICATION BYPASS" -ForegroundColor Yellow
Write-Host "─────────────────────────────────────" -ForegroundColor Yellow

# 4. Auth bypass tests (POST)
try {
    Write-Host "🧪 Admin bypass básico" -ForegroundColor Blue
    $body = '{"username":"admin'\''--","password":"anything"}'
    $response = Invoke-WebRequest -Uri "$BaseURL/api/auth/login" -Method POST -Body $body -ContentType "application/json" -TimeoutSec 5 -ErrorAction SilentlyContinue
    
    if ($response.StatusCode -eq 403) {
        Write-Host "   ✅ BLOQUEADO (403) - Seguro" -ForegroundColor Green
    }
    elseif ($response.StatusCode -eq 200 -and $response.Content -match "success.*true") {
        Write-Host "   🚨 VULNERABLE - Login bypass exitoso" -ForegroundColor Red
    }
    else {
        Write-Host "   ✅ SEGURO - Login falló como esperado" -ForegroundColor Green
    }
}
catch {
    Write-Host "   ❌ ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

Start-Sleep -Milliseconds 500

try {
    Write-Host "🧪 OR 1=1 bypass" -ForegroundColor Blue
    $body = '{"username":"admin'\'' OR 1=1--","password":""}'
    $response = Invoke-WebRequest -Uri "$BaseURL/api/auth/login" -Method POST -Body $body -ContentType "application/json" -TimeoutSec 5 -ErrorAction SilentlyContinue
    
    if ($response.StatusCode -eq 403) {
        Write-Host "   ✅ BLOQUEADO (403) - Seguro" -ForegroundColor Green
    }
    elseif ($response.StatusCode -eq 200 -and $response.Content -match "success.*true") {
        Write-Host "   🚨 VULNERABLE - Authentication bypass" -ForegroundColor Red
    }
    else {
        Write-Host "   ✅ SEGURO - Bypass falló" -ForegroundColor Green
    }
}
catch {
    Write-Host "   ❌ ERROR: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "⏱️  TESTS DE TIME-BASED INJECTION" -ForegroundColor Yellow
Write-Host "─────────────────────────────────────" -ForegroundColor Yellow

# 5. Time-based tests
$timeStart = Get-Date
try {
    Write-Host "🧪 MySQL SLEEP test" -ForegroundColor Blue
    $response = Invoke-WebRequest -Uri "$BaseURL/api/users?id=1' AND SLEEP(3)--" -TimeoutSec 8 -ErrorAction SilentlyContinue
    $timeElapsed = ((Get-Date) - $timeStart).TotalSeconds
    
    if ($response.StatusCode -eq 403) {
        Write-Host "   ✅ BLOQUEADO (403) en $([Math]::Round($timeElapsed,1))s" -ForegroundColor Green
    }
    elseif ($timeElapsed -gt 2.5) {
        Write-Host "   🚨 POSIBLE VULNERABILIDAD - Delay de $([Math]::Round($timeElapsed,1))s detectado" -ForegroundColor Red
    }
    else {
        Write-Host "   ✅ SEGURO - Sin delay anómalo ($([Math]::Round($timeElapsed,1))s)" -ForegroundColor Green
    }
}
catch {
    $timeElapsed = ((Get-Date) - $timeStart).TotalSeconds
    if ($_.Exception.Message -match "timeout" -and $timeElapsed -gt 2.5) {
        Write-Host "   🚨 POSIBLE VULNERABILIDAD - Timeout después de $([Math]::Round($timeElapsed,1))s" -ForegroundColor Red
    }
    else {
        Write-Host "   ✅ SEGURO - Error normal: $($_.Exception.Message)" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "🎭 TESTS DE BYPASS TECHNIQUES" -ForegroundColor Yellow  
Write-Host "────────────────────────────────" -ForegroundColor Yellow

# 6. Bypass technique tests
Test-URL "$BaseURL/api/users?id=1%27%20OR%201%3D1--" "URL Encoding bypass"
Test-URL "$BaseURL/api/users?id=1' UN/**/ION SE/**/LECT 1,2,3--" "Comment bypass"
Test-URL "$BaseURL/api/users?id=1' UnIoN sElEcT 1,2,3--" "Case variation bypass"

Write-Host ""
Write-Host "═══════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "✅ TESTING COMPLETADO" -ForegroundColor Green
Write-Host ""
Write-Host "📊 INTERPRETACIÓN DE RESULTADOS:" -ForegroundColor Cyan
Write-Host "   ✅ BLOQUEADO/SEGURO = Tu WAF está funcionando correctamente" -ForegroundColor Green
Write-Host "   🚨 VULNERABLE = Necesita atención inmediata" -ForegroundColor Red  
Write-Host "   ⚠️  REVISAR = Puede requerir investigación adicional" -ForegroundColor Yellow
Write-Host ""
Write-Host "💡 PRÓXIMOS PASOS:" -ForegroundColor Cyan
Write-Host "   1. Si todo está BLOQUEADO: ¡Excelente seguridad!" -ForegroundColor Green
Write-Host "   2. Si hay VULNERABLES: Revisar configuración del WAF" -ForegroundColor Yellow
Write-Host "   3. Consultar logs: npm run security:logs" -ForegroundColor Cyan
Write-Host "   4. Testing completo: .\InteractiveTesting.ps1" -ForegroundColor Cyan
Write-Host ""

# Mostrar comandos adicionales
Write-Host "🛠️  COMANDOS ÚTILES:" -ForegroundColor Cyan
Write-Host "   Ver logs en tiempo real:" -ForegroundColor Yellow
Write-Host "   Get-Content -Path ..\logs\security_$(Get-Date -Format 'yyyy-MM-dd').log -Wait -Tail 10" -ForegroundColor Gray
Write-Host ""
Write-Host "   Status del sistema:" -ForegroundColor Yellow  
Write-Host "   Invoke-WebRequest -Uri '$BaseURL/security/status' | ConvertFrom-Json" -ForegroundColor Gray
Write-Host ""
Write-Host "   Testing interactivo completo:" -ForegroundColor Yellow
Write-Host "   .\InteractiveTesting.ps1" -ForegroundColor Gray
Write-Host ""
Write-Host "📄 Para testing avanzado, consulta: MANUAL_TESTING_GUIDE.md" -ForegroundColor Cyan