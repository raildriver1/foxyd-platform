<?php
/**
 * Главный роутер приложения Foxyd
 * Точка входа для всех запросов
 */

// Запуск сессии
session_start();

// Подключение конфигурации и зависимостей
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Получаем URI запроса
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Убираем параметры GET из URI
$request_uri = strtok($request_uri, '?');

// Убираем базовый путь если есть
$base_path = str_replace('/index.php', '', $script_name);
if ($base_path !== '/' && strpos($request_uri, $base_path) === 0) {
    $request_uri = substr($request_uri, strlen($base_path));
}

// Если путь пустой - делаем корень
if (empty($request_uri) || $request_uri === '/') {
    $request_uri = '/';
}

// Роутинг
switch ($request_uri) {
    case '/':
        require __DIR__ . '/pages/home.php';
        break;
    
    case '/login':
        require __DIR__ . '/pages/login.php';
        break;
    
    case '/register':
        require __DIR__ . '/pages/register.php';
        break;
    
    case '/logout':
        require __DIR__ . '/modules/auth/logout.php';
        break;
    
    case '/courses':
        require __DIR__ . '/pages/courses.php';
        break;
    
    case '/course':
        require __DIR__ . '/pages/course.php';
        break;
    
    case '/progress':
        require __DIR__ . '/pages/progress.php';
        break;
    
    case '/calendar':
        require __DIR__ . '/pages/calendar.php';
        break;
    
    case '/cabinet':
        require __DIR__ . '/pages/cabinet.php';
        break;
    
    case '/admin':
        require __DIR__ . '/pages/admin.php';
        break;
    
    default:
        // 404 страница
        http_response_code(404);
        require __DIR__ . '/pages/404.php';
        break;
}
