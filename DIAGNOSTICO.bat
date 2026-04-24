@echo off
color 0E
echo.
echo  ============================================
echo   JUSEA CMN v2.0 - Diagnostico profundo
echo  ============================================
echo.
C:\xampp\php\php.exe "%~dp0diagnostico.php"
echo.
echo  Abriendo diagnostico en el navegador...
start http://localhost/JuseaCMN_v2/public/ver_boot.php
echo.
pause
