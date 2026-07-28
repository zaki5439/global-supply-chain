@echo off
REM ============================================
REM Global Supply Chain Risk Intelligence Platform
REM Redis Caching Layer - Complete Startup Script
REM ============================================

echo.
echo ╔════════════════════════════════════════════════════════════╗
echo ║  Global Supply Chain Risk Intelligence Platform            ║
echo ║  Redis Caching Layer - Complete Startup                   ║
echo ╚════════════════════════════════════════════════════════════╝
echo.

REM Check if Redis is running
echo [1/5] Checking Redis status...
redis-cli ping >nul 2>&1
if errorlevel 1 (
    echo ⚠️  Redis not running! Starting Redis...
    start redis-server
    timeout /t 3 /nobreak
    echo ✓ Redis started
) else (
    echo ✓ Redis is running
)

REM Verify Redis connection
echo [2/5] Verifying Redis connection...
redis-cli ping
if errorlevel 1 (
    echo ✗ Failed to connect to Redis
    echo Please ensure Redis is installed and running
    pause
    exit /b 1
)
echo ✓ Redis connection verified

REM Clear old cache (optional)
echo [3/5] Initializing Redis cache...
redis-cli FLUSHDB
echo ✓ Cache initialized

REM Start Python backend
echo [4/5] Starting FastAPI backend with Redis caching...
echo.
echo ⏱️  Starting on http://localhost:8000
echo 📊 Cache Monitor: http://localhost:8002/cache-monitor.html
echo.
start cmd /k python main.py

REM Wait for backend to start
timeout /t 3 /nobreak

REM Start PHP frontend
echo [5/5] Starting PHP frontend server...
echo.
echo 🌐 Dashboard: http://localhost:8002/dashboard-integrated.html
echo.
start cmd /k php -S localhost:8002 -t public

REM Give servers time to start
timeout /t 2 /nobreak

REM Show success message
echo.
echo ╔════════════════════════════════════════════════════════════╗
echo ║  ✅ System Started Successfully!                           ║
echo ╚════════════════════════════════════════════════════════════╝
echo.
echo 📌 ENDPOINTS:
echo    Backend API:      http://localhost:8000
echo    Frontend UI:      http://localhost:8002/dashboard-integrated.html
echo    Cache Monitor:    http://localhost:8002/cache-monitor.html
echo    Cache Stats:      http://localhost:8000/api/cache/stats
echo.
echo 📝 TEST COMMANDS:
echo    Check cache stats:
echo      curl http://localhost:8000/api/cache/stats
echo.
echo    Fetch country (will cache):
echo      curl http://localhost:8000/api/country/Germany
echo.
echo    Clear cache pattern:
echo      curl -X POST http://localhost:8000/api/cache/clear?pattern=country:*
echo.
echo ⏹️  To stop: Close both terminal windows
echo.
pause
