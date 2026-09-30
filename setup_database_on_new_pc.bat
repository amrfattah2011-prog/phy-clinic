@echo off
chcp 65001 >nul
cd /d "%~dp0"
call "%~dp02_RESTORE_DATABASE.bat"