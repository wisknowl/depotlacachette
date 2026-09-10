@echo off
set "DESKTOP=%USERPROFILE%\Desktop"
set "SHORTCUT=%DESKTOP%\Depot La Cachette.url"
(
echo [InternetShortcut]
echo URL=http://localhost/webapp/public
echo IconFile=C:\xampp\htdocs\webapp\public\assets\img\favicon_io\favicon.ico
echo IconIndex=0
) > "%SHORTCUT%"
echo Raccourci cree sur le Bureau avec succes !
