@echo off
REM Use the project-local PHP runtime (Laravel requires PHP >= 8.3).
"%~dp0.runtime\php83\php.exe" %*
