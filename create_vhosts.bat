@echo off
cd /d %~dp0
rem @echo %cd%
rem pause
..\php\php.exe create_vhosts.php
pause