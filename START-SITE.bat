@echo off
setlocal
cd /d D:\csfr-lms

echo Waiting for Docker Desktop...
for /l %%N in (1,1,30) do (
    docker info >nul 2>&1
    if not errorlevel 1 goto docker_ready
    timeout /t 2 /nobreak >nul
)

echo Docker Desktop is not ready. Open Docker Desktop, wait for "Engine running", then run this file again.
pause
exit /b 1

:docker_ready
echo Starting CSFR local services...
docker compose up -d
if errorlevel 1 (
    echo The CSFR services could not be started.
    pause
    exit /b 1
)

echo Moodle:  http://localhost:8000
echo Landing: http://localhost:8090
start "" http://localhost:8000
start "" http://localhost:8090
endlocal
