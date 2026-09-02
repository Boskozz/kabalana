@echo off
chcp 65001 >nul
cd /d "%~dp0"

echo === Sauvegarde git : add + commit + push ===
echo.

git add -A
if errorlevel 1 goto :error

git diff --cached --quiet
if not errorlevel 1 (
    echo Rien a commiter : tout est deja a jour sur la branche.
    goto :end
)

for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format 'yyyy-MM-dd HH:mm'"') do set "DATETIME=%%i"

git commit -m "Mise a jour du %DATETIME%"
if errorlevel 1 goto :error

git push
if errorlevel 1 goto :error

echo.
echo === Sauvegarde envoyee sur GitHub avec succes. ===
goto :end

:error
echo.
echo [!] Une erreur est survenue : rien n'a ete pousse.
exit /b 1

:end
exit /b 0
