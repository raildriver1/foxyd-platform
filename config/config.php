<?php
/**
 * Файл конфигурации платформы Foxyd
 * Онлайн-курсы для всех
 */

// Настройки сайта
define('SITE_NAME', 'Foxyd');
define('SITE_URL', 'http://localhost:8000');

// Настройки базы данных (SQLite)
define('DB_PATH', __DIR__ . '/../database/foxyd.db');

// Настройки безопасности
define('PASSWORD_SALT', 'foxyd_platform_2024');

// Часовой пояс
date_default_timezone_set('Europe/Moscow');

// Режим разработки
ini_set('display_errors', 1);
error_reporting(E_ALL);
