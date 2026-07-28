@echo off
REM Global Supply Chain Risk Intelligence Platform - Startup Script
REM =================================================================

echo ========================================
echo Global Supply Chain Risk Intelligence Platform
echo ========================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Python is not installed or not in PATH
    echo Please install Python 3.8+ from https://www.python.org/
    pause
    exit /b 1
)

REM Create virtual environment if it doesn't exist
if not exist "venv" (
    echo Creating virtual environment...
    python -m venv venv
    if %errorlevel% neq 0 (
        echo ERROR: Failed to create virtual environment
        pause
        exit /b 1
    )
    echo Virtual environment created successfully
)

REM Activate virtual environment
echo Activating virtual environment...
call venv\Scripts\activate.bat

REM Install dependencies
echo Installing Python dependencies...
pip install -r requirements.txt
if %errorlevel% neq 0 (
    echo ERROR: Failed to install dependencies
    pause
    exit /b 1
)

REM Initialize database
echo Initializing database...
python -c "from app import app, db; app.app_context().push(); db.create_all(); print('Database initialized successfully')"

REM Start Flask server
echo.
echo ========================================
echo Starting Flask API Server...
echo Server will be available at: http://localhost:5000
echo Dashboard will be available at: http://localhost:5000/
echo Press Ctrl+C to stop the server
echo ========================================
echo.

python app.py

pause
