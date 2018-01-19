@echo off

rem * This file is part of `xampp-tools` project

echo Create database backup
echo ======================

set /p dbn="Enter database name: "
set mytime=%date:~-4%%date:~3,2%%date:~0,2%-%time:~0,2%%time:~3,2%
if "%dbn%"=="" (
	echo Dumping failed. Database name not specified.
) else (
	echo Dumping the database `%dbn%`...
	..\mysql\bin\mysqldump.exe -u root --opt --complete-insert %dbn% >> ..\backups\%dbn%-%mytime%.sql
	echo Dumping completed.
)
pause
