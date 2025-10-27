# Comandos Manuales SQL Injection - Compareware
Write-Host "COMANDOS MANUALES DE TESTING SQL INJECTION" -ForegroundColor Cyan
Write-Host "===========================================" -ForegroundColor Cyan

$base = "http://localhost:4000"
Write-Host "Target: $base" -ForegroundColor White
Write-Host ""

# Test 1: Comilla simple
Write-Host "1. TEST COMILLA SIMPLE" -ForegroundColor Yellow
$url1 = "$base/api/users?id=1'"
Write-Host "URL: $url1" -ForegroundColor Gray
try {
    $r1 = Invoke-RestMethod -Uri $url1 -TimeoutSec 5
    Write-Host "RESULTADO: PASO (200) - Revisar" -ForegroundColor Yellow
} catch {
    if ($_.Exception.Response.StatusCode.value__ -eq 403) {
        Write-Host "RESULTADO: BLOQUEADO (403) - SEGURO" -ForegroundColor Green
    } else {
        Write-Host "RESULTADO: ERROR - $($_.Exception.Message)" -ForegroundColor Red
    }
}
Write-Host ""

# Test 2: Comentario SQL  
Write-Host "2. TEST COMENTARIO SQL" -ForegroundColor Yellow
$url2 = "$base/api/users?id=1--"
Write-Host "URL: $url2" -ForegroundColor Gray
try {
    $r2 = Invoke-RestMethod -Uri $url2 -TimeoutSec 5
    Write-Host "RESULTADO: PASO (200) - Revisar" -ForegroundColor Yellow
} catch {
    if ($_.Exception.Response.StatusCode.value__ -eq 403) {
        Write-Host "RESULTADO: BLOQUEADO (403) - SEGURO" -ForegroundColor Green  
    } else {
        Write-Host "RESULTADO: ERROR - $($_.Exception.Message)" -ForegroundColor Red
    }
}
Write-Host ""

# Test 3: OR Injection
Write-Host "3. TEST OR INJECTION" -ForegroundColor Yellow
$url3 = "$base/api/users?id=1' OR 1=1--"
Write-Host "URL: $url3" -ForegroundColor Gray
try {
    $r3 = Invoke-RestMethod -Uri $url3 -TimeoutSec 5
    Write-Host "RESULTADO: PASO (200) - REVISAR URGENTE" -ForegroundColor Red
} catch {
    if ($_.Exception.Response.StatusCode.value__ -eq 403) {
        Write-Host "RESULTADO: BLOQUEADO (403) - SEGURO" -ForegroundColor Green
    } else {
        Write-Host "RESULTADO: ERROR - $($_.Exception.Message)" -ForegroundColor Red  
    }
}
Write-Host ""

# Test 4: UNION Injection
Write-Host "4. TEST UNION INJECTION" -ForegroundColor Yellow
$url4 = "$base/api/users?id=1' UNION SELECT 1,2,3--"
Write-Host "URL: $url4" -ForegroundColor Gray  
try {
    $r4 = Invoke-RestMethod -Uri $url4 -TimeoutSec 5
    Write-Host "RESULTADO: PASO (200) - REVISAR URGENTE" -ForegroundColor Red
} catch {
    if ($_.Exception.Response.StatusCode.value__ -eq 403) {
        Write-Host "RESULTADO: BLOQUEADO (403) - SEGURO" -ForegroundColor Green
    } else {
        Write-Host "RESULTADO: ERROR - $($_.Exception.Message)" -ForegroundColor Red
    }
}
Write-Host ""

# Test 5: URL Encoding Bypass
Write-Host "5. TEST URL ENCODING BYPASS" -ForegroundColor Yellow
$url5 = "$base/api/users?id=1%27%20OR%201%3D1--"
Write-Host "URL: $url5" -ForegroundColor Gray
try {
    $r5 = Invoke-RestMethod -Uri $url5 -TimeoutSec 5  
    Write-Host "RESULTADO: PASO (200) - BYPASS DETECTADO" -ForegroundColor Red
} catch {
    if ($_.Exception.Response.StatusCode.value__ -eq 403) {
        Write-Host "RESULTADO: BLOQUEADO (403) - SEGURO" -ForegroundColor Green
    } else {
        Write-Host "RESULTADO: ERROR - $($_.Exception.Message)" -ForegroundColor Red
    }
}
Write-Host ""

# Test 6: Time-based (simple)
Write-Host "6. TEST TIME-BASED INJECTION" -ForegroundColor Yellow
$url6 = "$base/api/users?id=1' AND SLEEP(3)--"
Write-Host "URL: $url6" -ForegroundColor Gray
$start = Get-Date
try {
    $r6 = Invoke-RestMethod -Uri $url6 -TimeoutSec 6
    $elapsed = ((Get-Date) - $start).TotalSeconds
    if ($elapsed -ge 2.5) {
        Write-Host "RESULTADO: VULNERABLE - Delay de $($elapsed)s detectado" -ForegroundColor Red
    } else {
        Write-Host "RESULTADO: PASO ($($elapsed)s) - Revisar" -ForegroundColor Yellow  
    }
} catch {
    $elapsed = ((Get-Date) - $start).TotalSeconds
    if ($_.Exception.Response.StatusCode.value__ -eq 403) {
        Write-Host "RESULTADO: BLOQUEADO (403) en $($elapsed)s - SEGURO" -ForegroundColor Green
    } else {
        Write-Host "RESULTADO: ERROR en $($elapsed)s - $($_.Exception.Message)" -ForegroundColor Red
    }
}
Write-Host ""

Write-Host "COMANDOS MANUALES DISPONIBLES:" -ForegroundColor Cyan
Write-Host "==============================" -ForegroundColor Cyan
Write-Host ""
Write-Host "# Test básico de comilla:" -ForegroundColor White
Write-Host "Invoke-RestMethod -Uri `"$base/api/users?id=1'`"" -ForegroundColor Gray
Write-Host ""
Write-Host "# Test de comentario:" -ForegroundColor White  
Write-Host "Invoke-RestMethod -Uri `"$base/api/users?id=1--`"" -ForegroundColor Gray
Write-Host ""
Write-Host "# Test OR injection:" -ForegroundColor White
Write-Host "Invoke-RestMethod -Uri `"$base/api/users?id=1' OR 1=1--`"" -ForegroundColor Gray
Write-Host ""
Write-Host "# Revisar logs:" -ForegroundColor White
Write-Host "Get-Content ..\logs\security_*.log | Select-String SQL" -ForegroundColor Gray
Write-Host ""
Write-Host "# Status del sistema:" -ForegroundColor White
Write-Host "Invoke-RestMethod -Uri `"$base/security/status`"" -ForegroundColor Gray