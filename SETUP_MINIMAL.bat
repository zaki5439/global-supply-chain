@echo off
REM ============================================
REM Minimal Setup - Only Download Required Files
REM ============================================

echo.
echo ╔════════════════════════════════════════════════════════════╗
echo ║  Minimal Setup - Install Only What's Needed               ║
echo ║  For Real Data Collection from APIs                       ║
echo ╚════════════════════════════════════════════════════════════╝
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo ✗ Python is not installed
    echo   Please install Python from: https://www.python.org/downloads/
    echo   Make sure to check "Add Python to PATH" during installation
    pause
    exit /b 1
)
echo ✓ Python found

REM Install only required packages
echo.
echo Installing only required packages...
echo.

pip install requests==2.31.0
pip install python-dotenv==1.0.0
pip install pandas==2.1.3

echo.
echo ✓ Installation complete!
echo.
echo ════════════════════════════════════════════════════════════
echo.
echo Next, run the data collection script:
echo   python fetch_real_data.py
echo.
echo This will collect real data from:
echo   - World Bank API (Macroeconomic)
echo   - Open-Meteo API (Weather)
echo   - ExchangeRate-API (Currency)
echo   - GNews API (News)
echo   - REST Countries API (Geographic)
echo.
pause
