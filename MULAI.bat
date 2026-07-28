@echo off
REM ============================================
REM Start Everything - Get Real Data
REM ============================================

echo.
echo ╔════════════════════════════════════════════════════════════╗
echo ║     Download Real Data from 6 APIs                        ║
echo ║     (Minimal installation - only what's needed)           ║
echo ╚════════════════════════════════════════════════════════════╝
echo.

REM Check Python
python --version >nul 2>&1
if errorlevel 1 (
    echo ✗ Python not installed
    echo.
    echo Please install Python from:
    echo   https://www.python.org/downloads/
    echo.
    echo Make sure to check:
    echo   [✓] Add Python to PATH
    echo.
    pause
    exit /b 1
)

echo ✓ Python installed
echo.

REM Install minimal packages
echo Installing required packages...
echo.

pip install requests==2.31.0 --quiet
pip install python-dotenv==1.0.0 --quiet
pip install pandas==2.1.3 --quiet

echo ✓ Packages installed
echo.

REM Run data collection
echo ════════════════════════════════════════════════════════════
echo.
echo Starting data collection from 6 APIs...
echo.
echo This will collect:
echo   ✓ Macroeconomic data (World Bank)
echo   ✓ Weather data (Open-Meteo)
echo   ✓ Exchange rates (ExchangeRate API)
echo   ✓ News articles (GNews)
echo   ✓ Geographic data (REST Countries)
echo   ✓ Port data (World Port Index)
echo.
echo ════════════════════════════════════════════════════════════
echo.

python fetch_real_data.py

echo.
echo ════════════════════════════════════════════════════════════
echo.
echo ✓ Complete!
echo.
echo Data saved to: collected_data/
echo.
echo Files created:
echo   • world_bank_data.json
echo   • weather_data.json
echo   • exchange_rates.json
echo   • news_data.json
echo   • geographic_data.json
echo   • ports_data.json
echo.
echo Total size: ~420 KB
echo.
echo ════════════════════════════════════════════════════════════
echo.
pause
