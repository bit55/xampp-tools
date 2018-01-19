@echo off
echo Restore database backup
echo =======================
setlocal enabledelayedexpansion
set q=1
set backups=..\backups

for /f "tokens=*" %%i in ('dir /b /s "%backups%"') do (
    echo !q!^) %%i
    set ng!q!=%%i
    set /a q+=1
)

set /p file="Select number of backup file to restore: "
set /p dbn="Enter database name: "
for /f "tokens=1-2 delims==" %%k in ('set ng') do (
	  rem echo %%k %%l
		if "%%k"=="ng%file%" (
				..\mysql\bin\mysql -e "source %%l" -u root %dbn%
				echo Restored `%%l` to `%dbn`.
				pause
				exit
		)
)
echo No selected data.
pause