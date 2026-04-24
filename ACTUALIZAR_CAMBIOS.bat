@echo off
color 0B
title JUSEA CMN v2.0 - Actualizar Cambios

echo.
echo  ============================================================
echo   JUSEA CMN v2.0 - Actualizacion de archivos modificados
echo  ============================================================
echo.
echo  Este script copia los archivos actualizados por Claude
echo  hacia la carpeta que sirve XAMPP (htdocs\JuseaCMN_v2).
echo.

set "ORIGEN=%~dp0"
set "DEST=C:\xampp\htdocs\JuseaCMN_v2"

:: Verificar que existe la carpeta destino
if not exist "%DEST%" (
    echo  [ERROR] No se encontro la carpeta:
    echo          %DEST%
    echo.
    echo  Verifique que XAMPP esta instalado y el sistema fue
    echo  instalado con INSTALAR_JUSEA.bat previamente.
    echo.
    pause
    exit /b 1
)

echo  Origen : %ORIGEN%
echo  Destino: %DEST%
echo.
echo  Copiando archivos modificados...
echo.

:: --- DocumentGenerator.php (logica de generacion DOCX/PDF) ---
echo  [ 1/10] DocumentGenerator.php...
copy /Y "%ORIGEN%app\Libraries\DocumentGenerator.php" "%DEST%\app\Libraries\DocumentGenerator.php" >nul
if errorlevel 1 ( echo        ERROR al copiar DocumentGenerator.php ) else ( echo        OK )

:: --- SancionController.php (metodos descarga, revisor) ---
echo  [ 2/10] SancionController.php...
copy /Y "%ORIGEN%app\Controllers\SancionController.php" "%DEST%\app\Controllers\SancionController.php" >nul
if errorlevel 1 ( echo        ERROR al copiar SancionController.php ) else ( echo        OK )

:: --- Routes.php (rutas API incluyendo IA) ---
echo  [ 3/10] Routes.php...
copy /Y "%ORIGEN%app\Config\Routes.php" "%DEST%\app\Config\Routes.php" >nul
if errorlevel 1 ( echo        ERROR al copiar Routes.php ) else ( echo        OK )

:: --- main.php (layout principal) ---
echo  [ 4/10] main.php...
copy /Y "%ORIGEN%app\Views\layouts\main.php" "%DEST%\app\Views\layouts\main.php" >nul
if errorlevel 1 ( echo        ERROR al copiar main.php ) else ( echo        OK )

:: --- historial/index.php (botones de descarga) ---
echo  [ 5/10] historial/index.php...
copy /Y "%ORIGEN%app\Views\historial\index.php" "%DEST%\app\Views\historial\index.php" >nul
if errorlevel 1 ( echo        ERROR al copiar historial/index.php ) else ( echo        OK )

:: --- SancionModel.php ---
echo  [ 6/11] SancionModel.php...
copy /Y "%ORIGEN%app\Models\SancionModel.php" "%DEST%\app\Models\SancionModel.php" >nul
if errorlevel 1 ( echo        ERROR al copiar SancionModel.php ) else ( echo        OK )

:: --- PersonaModel.php (validacion sin alpha_space para nombres con acentos o caracteres especiales) ---
echo  [ 7/11] PersonaModel.php...
copy /Y "%ORIGEN%app\Models\PersonaModel.php" "%DEST%\app\Models\PersonaModel.php" >nul
if errorlevel 1 ( echo        ERROR al copiar PersonaModel.php ) else ( echo        OK )

:: --- ApiController.php (busqueda + endpoint IA motivos + endpoint sugerir normativa RDF/CDFFAA) ---
echo  [ 8/11] ApiController.php...
copy /Y "%ORIGEN%app\Controllers\ApiController.php" "%DEST%\app\Controllers\ApiController.php" >nul
if errorlevel 1 ( echo        ERROR al copiar ApiController.php ) else ( echo        OK )

:: --- form_cadetes.php (busqueda + boton IA) ---
echo  [ 9/11] form_cadetes.php...
copy /Y "%ORIGEN%app\Views\sancion\form_cadetes.php" "%DEST%\app\Views\sancion\form_cadetes.php" >nul
if errorlevel 1 ( echo        ERROR al copiar form_cadetes.php ) else ( echo        OK )

:: --- form_cuadros.php (boton IA) ---
echo  [10/11] form_cuadros.php...
copy /Y "%ORIGEN%app\Views\sancion\form_cuadros.php" "%DEST%\app\Views\sancion\form_cuadros.php" >nul
if errorlevel 1 ( echo        ERROR al copiar form_cuadros.php ) else ( echo        OK )

:: --- .env.example ---
echo  [11/11] .env.example...
copy /Y "%ORIGEN%.env.example" "%DEST%\.env.example" >nul
if errorlevel 1 ( echo        ERROR al copiar .env.example ) else ( echo        OK )

:: NOTA: El archivo .env de XAMPP NO se sobreescribe automaticamente
:: para preservar la ANTHROPIC_API_KEY que ya configuraste.
:: Si necesitas editar variables de entorno, hacelo directamente en:
::   C:\xampp\htdocs\JuseaCMN_v2\.env

echo.
echo  ============================================================

:: Limpiar cache de CodeIgniter si existe
if exist "%DEST%\writable\cache" (
    echo  Limpiando cache de CodeIgniter...
    del /Q "%DEST%\writable\cache\*" >nul 2>&1
    echo  Cache limpiada.
)

echo.
echo  Actualizacion completada correctamente.
echo.
echo  IMPORTANTE: Si Apache sigue mostrando la version anterior,
echo  reinicie Apache desde el Panel de XAMPP y recargue con
echo  Ctrl+Shift+R en el navegador.
echo.
pause
