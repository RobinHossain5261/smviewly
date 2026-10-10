@echo off
REM Start the Laravel dev server with the correct PHP version
setlocal
set "PATH=%~dp0.runtime\php83;%PATH%"
cd /d "%~dp0"
php artisan serve
