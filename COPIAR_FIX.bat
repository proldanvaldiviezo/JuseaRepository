@echo off
color 0A
echo.
echo  Copiando archivos corregidos...
echo.
copy /Y "%~dp0public\index.php" "C:\xampp\htdocs\JuseaCMN_v2\public\index.php"
copy /Y "%~dp0.env" "C:\xampp\htdocs\JuseaCMN_v2\.env"
copy /Y "%~dp0.env.example" "C:\xampp\htdocs\JuseaCMN_v2\.env.example"
copy /Y "%~dp0public\fix.php" "C:\xampp\htdocs\JuseaCMN_v2\public\fix.php"
echo.
echo  [OK] Archivos copiados. Abriendo el sistema...
echo.
start http://localhost/JuseaCMN_v2/public/
pause >nul
