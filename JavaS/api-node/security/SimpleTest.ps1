# SQL Injection Quick Test - Compareware
# Ejecutar desde: JavaS\api-node\security\

$BaseURL = "http://localhost:4000"
$TestCount = 0
$BlockedCount = 0
$VulnerableCount = 0

Write-Host "COMPAREWARE SQL INJECTION TEST" -ForegroundColor Cyan
Write-Host "==============================" -ForegroundColor Cyan
Write-Host "Target: $BaseURL" -ForegroundColor White
Write-Host ""

function Test-URL {
    param([string]$URL, [string]$Name)
    
    $script:TestCount++
    Write-Host "Testing: $Name" -ForegroundColor Blue
    
    try {
        $response = Invoke-RestMethod -Uri $URL -TimeoutSec 5
        Write-Host "   PASSED (200) - Revisar respuesta" -ForegroundColor Yellow
    }
    catch {
        $statusCode = $_.Exception.Response.StatusCode.value__
        
        if ($statusCode -eq 403) {
            Write-Host "   BLOCKED (403) - WAF working" -ForegroundColor Green
            $script:BlockedCount++
        }
        elseif ($statusCode -eq 404) {
            Write-Host "   NOT FOUND (404)" -ForegroundColor Cyan
        }
        else {
            Write-Host "   ERROR: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    Start-Sleep -Milliseconds 300
}

# Basic Tests
Write-Host "BASIC TESTS" -ForegroundColor Yellow
Write-Host "-----------" -ForegroundColor Yellow

Test-URL "$BaseURL/api/users?id=1'" "Single quote"
Test-URL "$BaseURL/api/users?id=1--" "SQL comment" 
Test-URL "$BaseURL/api/users?id=1;" "Semicolon"
Test-URL "$BaseURL/api/users?id=1' OR 1=1--" "OR injection"

Write-Host ""
Write-Host "UNION TESTS" -ForegroundColor Yellow
Write-Host "-----------" -ForegroundColor Yellow

Test-URL "$BaseURL/api/users?id=1' UNION SELECT 1,2,3--" "UNION basic"
Test-URL "$BaseURL/api/users?id=1' UNION SELECT null,null--" "UNION NULL"

Write-Host ""
Write-Host "BYPASS TESTS" -ForegroundColor Yellow  
Write-Host "------------" -ForegroundColor Yellow

Test-URL "$BaseURL/api/users?id=1%27%20OR%201%3D1--" "URL encoding"
Test-URL "$BaseURL/api/users?id=1' UnIoN sElEcT 1--" "Case variation"

# Auth Bypass Test
Write-Host ""
Write-Host "AUTH BYPASS TEST" -ForegroundColor Yellow
Write-Host "----------------" -ForegroundColor Yellow

Write-Host "Testing: Admin bypass" -ForegroundColor Blue

try {
    $body = '{"username":"admin'\''--","password":"test"}'
    $response = Invoke-WebRequest -Uri "$BaseURL/api/auth/login" -Method POST -Body $body -ContentType "application/json" -TimeoutSec 5
    
    if ($response.StatusCode -eq 403) {
        Write-Host "   BLOCKED (403) - WAF working" -ForegroundColor Green
        $BlockedCount++
    }
    elseif ($response.Content -match "success.*true") {
        Write-Host "   CRITICAL - Authentication bypassed!" -ForegroundColor Red
        $VulnerableCount++
    }
    else {
        Write-Host "   SECURE - Login failed as expected" -ForegroundColor Green
    }
}
catch {
    if ($_.Exception.Response.StatusCode.value__ -eq 403) {
        Write-Host "   BLOCKED (403) - WAF working" -ForegroundColor Green
        $BlockedCount++
    }
    else {
        Write-Host "   ERROR: $($_.Exception.Message)" -ForegroundColor Red
    }
}

$TestCount++

# Time-based Test
Write-Host ""
Write-Host "TIME-BASED TEST" -ForegroundColor Yellow
Write-Host "---------------" -ForegroundColor Yellow

Write-Host "Testing: SLEEP injection" -ForegroundColor Blue
$start = Get-Date

try {
    Invoke-RestMethod -Uri "$BaseURL/api/users?id=1' AND SLEEP(3)--" -TimeoutSec 6
    $elapsed = ((Get-Date) - $start).TotalSeconds
    
    if ($elapsed -ge 2.5) {
        Write-Host "   VULNERABLE - Delay detected ($([math]::Round($elapsed,1))s)" -ForegroundColor Red
        $VulnerableCount++
    }
    else {
        Write-Host "   SECURE - No anomalous delay ($([math]::Round($elapsed,1))s)" -ForegroundColor Green
    }
}
catch {
    $elapsed = ((Get-Date) - $start).TotalSeconds
    
    if ($_.Exception.Response.StatusCode.value__ -eq 403) {
        Write-Host "   BLOCKED (403) - WAF working ($([math]::Round($elapsed,1))s)" -ForegroundColor Green
        $BlockedCount++
    }
    else {
        Write-Host "   ERROR after $([math]::Round($elapsed,1))s" -ForegroundColor Red
    }
}

$TestCount++

# Results Summary  
Write-Host ""
Write-Host "FINAL RESULTS" -ForegroundColor Cyan
Write-Host "=============" -ForegroundColor Cyan
Write-Host "Tests executed: $TestCount" -ForegroundColor White
Write-Host "Tests blocked: $BlockedCount" -ForegroundColor Green
Write-Host "Vulnerabilities: $VulnerableCount" -ForegroundColor Red

Write-Host ""
if ($VulnerableCount -eq 0 -and $BlockedCount -gt 0) {
    Write-Host "RESULT: SYSTEM SECURE" -ForegroundColor Green
    Write-Host "Your WAF is working correctly" -ForegroundColor Green
}
elseif ($VulnerableCount -gt 0) {
    Write-Host "RESULT: VULNERABILITIES DETECTED" -ForegroundColor Red
    Write-Host "REVIEW SYSTEM IMMEDIATELY" -ForegroundColor Red
}
else {
    Write-Host "RESULT: REVIEW CONFIGURATION" -ForegroundColor Yellow
    Write-Host "Few tests were blocked" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Check logs: Get-Content ..\logs\security_*.log" -ForegroundColor White
Write-Host "2. Interactive test: .\InteractiveTesting.ps1" -ForegroundColor White
Write-Host "3. Documentation: .\README_TESTING.md" -ForegroundColor White