@echo off
REM ============================================
REM Open Cache Monitor Demo in Browser
REM ============================================

echo.
echo ╔════════════════════════════════════════════════════════════╗
echo ║  Opening Cache Monitor Demo...                            ║
echo ╚════════════════════════════════════════════════════════════╝
echo.

REM Open main hub
start http://localhost:8002/

REM Wait a moment
timeout /t 2 /nobreak

REM Open cache monitor demo
start http://localhost:8002/cache-monitor-demo.html

echo.
echo ✅ Browser akan membuka dengan:
echo    1. Main Hub: http://localhost:8002/
echo    2. Cache Monitor Demo: http://localhost:8002/cache-monitor-demo.html
echo.
echo 📌 Di cache monitor demo, klik tombol berikut:
echo    - "Mulai Simulasi Live" untuk melihat data update real-time
echo    - "Auto-Update" untuk auto-refresh setiap 5 detik
echo    - "Simulasi Request" untuk trigger simulasi request
echo.
pause
