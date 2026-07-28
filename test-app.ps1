# Test Supply Chain Application

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "TESTING SUPPLY CHAIN PLATFORM" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan

# Test 1: Check if server is running
Write-Host "[1] Checking if server is running on http://127.0.0.1:8000..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000" -Method GET -TimeoutSec 5 -ErrorAction Stop
    Write-Host "checkmark Server is running (Status: $($response.StatusCode))" -ForegroundColor Green
}
catch {
    Write-Host "X Server is not responding" -ForegroundColor Red
    exit 1
}

# Test 2: Check Dashboard
Write-Host "`n[2] Testing Dashboard page..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/dashboard" -Method GET -TimeoutSec 5 -ErrorAction Stop
    if ($response.Content -like "*Global Country Dashboard*") {
        Write-Host "checkmark Dashboard page loaded successfully" -ForegroundColor Green
    } else {
        Write-Host "X Dashboard page missing content" -ForegroundColor Red
    }
}
catch {
    Write-Host "X Dashboard page failed: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 3: Check News page
Write-Host "`n[3] Testing News & Sentiment page..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/news" -Method GET -TimeoutSec 5 -ErrorAction Stop
    if ($response.Content -like "*News & Sentiment*") {
        Write-Host "checkmark News page loaded successfully" -ForegroundColor Green
    } else {
        Write-Host "X News page missing content" -ForegroundColor Red
    }
}
catch {
    Write-Host "X News page failed: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 4: Test Countries API
Write-Host "`n[4] Testing Countries API endpoint..." -ForegroundColor Yellow
try {
    $uri = "http://127.0.0.1:8000/api/countries?limit=3"
    $response = Invoke-WebRequest -Uri $uri -Method GET -TimeoutSec 5 -ErrorAction Stop
    $data = $response.Content | ConvertFrom-Json
    if ($data.data.Count -gt 0) {
        Write-Host "checkmark Countries API returns data (Total: $($data.meta.total))" -ForegroundColor Green
        Write-Host "  Sample countries:" -ForegroundColor Gray
    } else {
        Write-Host "X No countries returned from API" -ForegroundColor Red
    }
}
catch {
    Write-Host "X Countries API failed: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 5: Test Risk API
Write-Host "`n[5] Testing Risk Intelligence API endpoint..." -ForegroundColor Yellow
try {
    $uri = "http://127.0.0.1:8000/api/risk?country=Indonesia&code=IDN"
    $response = Invoke-WebRequest -Uri $uri -Method GET -TimeoutSec 5 -ErrorAction Stop
    $data = $response.Content | ConvertFrom-Json
    if ($data.status -eq "success") {
        Write-Host "checkmark Risk API working (Risk Score: $($data.data.overall_risk_score)/100)" -ForegroundColor Green
    } else {
        Write-Host "! Risk API returned: $($data.message)" -ForegroundColor Yellow
    }
}
catch {
    Write-Host "X Risk API failed: $($_.Exception.Message)" -ForegroundColor Red
}

# Test 6: Database verification
Write-Host "`n[6] Verifying database..." -ForegroundColor Yellow
if (Test-Path "C:\Users\ACER\supply-chain-app\database\database.sqlite") {
    Write-Host "checkmark SQLite database file exists" -ForegroundColor Green
} else {
    Write-Host "X Database file not found" -ForegroundColor Red
}

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "TESTS COMPLETED" -ForegroundColor Green
Write-Host "========================================`n" -ForegroundColor Cyan
Write-Host "Dashboard: http://127.0.0.1:8000/dashboard" -ForegroundColor Blue
Write-Host "News Page: http://127.0.0.1:8000/news" -ForegroundColor Blue
Write-Host "API Docs: http://127.0.0.1:8000/api/countries" -ForegroundColor Blue
