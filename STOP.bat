@echo off
echo Stopping all services...
echo.

REM Kill all PHP processes (Laravel services)
taskkill /F /IM php.exe 2>nul

REM Kill Node processes (Frontend)
taskkill /F /IM node.exe 2>nul

echo.
echo All services stopped!
timeout /t 2 /nobreak >nul

