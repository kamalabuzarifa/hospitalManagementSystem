@echo off
REM Script: backup_db_to_d_backups.bat
REM Description: Backup MySQL database to D:\Backups with timestamp

set DB_USER=root
set DB_PASS=
set DB_NAME=hospitalmanagementsystem
set BACKUP_DIR=D:\Backups

REM صيغة تاريخ ووقت آمنة للملفات
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do set MYDATE=%%c-%%a-%%b
for /f "tokens=1-2 delims=: " %%a in ('time /t') do set MYTIME=%%a%%b
set DATESTAMP=%MYDATE%_%MYTIME%
set DATESTAMP=%DATESTAMP:/=_%
set DATESTAMP=%DATESTAMP::=_%
set DATESTAMP=%DATESTAMP: =0%
set BACKUP_FILE=%BACKUP_DIR%\%DB_NAME%_backup_%DATESTAMP%.sql

if not exist %BACKUP_DIR% mkdir %BACKUP_DIR%

echo Running: "D:\mapp\mysql\bin\mysqldump.exe" -u%DB_USER% %DB_NAME% ^> "%BACKUP_FILE%"
"D:\mapp\mysql\bin\mysqldump.exe" -u%DB_USER% %DB_NAME% > "%BACKUP_FILE%"

if exist "%BACKUP_FILE%" (
    echo Backup successful: %BACKUP_FILE%
) else (
    echo Backup failed!
)
pause
