@echo off
chcp 65001 >nul
title ClubHub Setup
echo ========================================
echo   ClubHub - Database Setup
echo ========================================
echo.
php api/setup.php
echo.
pause
