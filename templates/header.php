<!DOCTYPE html>
<html lang="ru" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Foxyd' ?> - Онлайн-обучение</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-orange: #ff6b35;
            --secondary-orange: #ff8c42;
            --transition-speed: 0.4s;
            --transition-ease: cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Тёмная тема (по умолчанию) */
        [data-theme="dark"] {
            --bg-primary: #171a20;
            --bg-secondary: #23262f;
            --bg-tertiary: #393c41;
            --bg-elevated: #0a0b0d;
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
            --text-tertiary: rgba(255, 255, 255, 0.5);
            --border-color: rgba(255, 255, 255, 0.1);
            --card-bg-start: #23262f;
            --card-bg-end: rgba(35, 38, 47, 0.8);
            --input-bg: rgba(255, 255, 255, 0.05);
            --shadow: rgba(0, 0, 0, 0.3);
            --shadow-hover: rgba(0, 0, 0, 0.4);
            --navbar-bg: rgba(23, 26, 32, 0.8);
            --navbar-bg-scrolled: rgba(23, 26, 32, 0.95);
            --medium-gray: rgba(255, 255, 255, 0.6);
            --dark-gray: rgba(255, 255, 255, 0.9);
            --dark: #171a20;
            --bg-dark: #0a0b0d;
            --white: #ffffff;
        }
        
        /* Светлая тема */
        [data-theme="light"] {
            --bg-primary: #f5f7fa;
            --bg-secondary: #ffffff;
            --bg-tertiary: #e8ecef;
            --bg-elevated: #ffffff;
            --text-primary: #171a20;
            --text-secondary: rgba(23, 26, 32, 0.7);
            --text-tertiary: rgba(23, 26, 32, 0.5);
            --border-color: rgba(23, 26, 32, 0.15);
            --card-bg-start: #ffffff;
            --card-bg-end: #f8f9fa;
            --input-bg: #f5f7fa;
            --shadow: rgba(23, 26, 32, 0.1);
            --shadow-hover: rgba(23, 26, 32, 0.15);
            --navbar-bg: rgba(255, 255, 255, 0.8);
            --navbar-bg-scrolled: rgba(255, 255, 255, 0.95);
            --medium-gray: rgba(23, 26, 32, 0.6);
            --dark-gray: rgba(23, 26, 32, 0.9);
            --dark: #171a20;
            --bg-dark: #0a0b0d;
            --white: #ffffff;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background: var(--bg-primary);
            overflow-x: hidden;
            transition: background var(--transition-speed) var(--transition-ease), 
                        color var(--transition-speed) var(--transition-ease);
        }
        
        /* === НАВБАР === */
        .navbar {
            background: var(--navbar-bg);
            backdrop-filter: blur(20px) saturate(180%);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all var(--transition-speed) var(--transition-ease);
        }
        
        .navbar.scrolled {
            background: var(--navbar-bg-scrolled);
            box-shadow: 0 2px 20px var(--shadow);
        }
        
        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.2rem 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            transition: transform var(--transition-speed) var(--transition-ease);
            letter-spacing: -0.5px;
        }
        
        .logo:hover {
            transform: scale(1.02);
        }
        
        .logo-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
            transition: box-shadow var(--transition-speed) var(--transition-ease);
        }
        
        .logo:hover .logo-icon {
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.6);
        }
        
        .nav-links {
            display: flex;
            gap: 2.5rem;
            align-items: center;
        }
        
        .nav-links a {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all var(--transition-speed) var(--transition-ease);
            position: relative;
            opacity: 0.9;
        }
        
        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary-orange);
            transition: width var(--transition-speed) var(--transition-ease);
        }
        
        .nav-links a:hover {
            opacity: 1;
            color: var(--primary-orange);
        }
        
        .nav-links a:hover::after {
            width: 100%;
        }
        
        /* === ПЕРЕКЛЮЧАТЕЛЬ ТЕМЫ === */
        .theme-toggle {
            position: relative;
            width: 60px;
            height: 30px;
            background: var(--bg-tertiary);
            border-radius: 50px;
            cursor: pointer;
            transition: all var(--transition-speed) var(--transition-ease);
            border: 2px solid var(--border-color);
        }
        
        .theme-toggle:hover {
            background: var(--bg-secondary);
            border-color: var(--primary-orange);
        }
        
        .theme-toggle-slider {
            position: absolute;
            top: 2px;
            left: 2px;
            width: 22px;
            height: 22px;
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            border-radius: 50%;
            transition: transform var(--transition-speed) var(--transition-ease);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            box-shadow: 0 2px 10px rgba(255, 107, 53, 0.3);
        }
        
        [data-theme="light"] .theme-toggle-slider {
            transform: translateX(30px);
        }
        
        /* === КНОПКИ === */
        .btn {
            display: inline-block;
            padding: 0.75rem 1.8rem;
            border-radius: 50px;
            text-decoration: none;
            transition: all var(--transition-speed) var(--transition-ease);
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
            color: white !important;
            box-shadow: 0 4px 20px rgba(255, 107, 53, 0.4);
        }
        
        .btn-primary:hover {
            color: white !important;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 30px rgba(255, 107, 53, 0.6);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--primary-orange) !important;
            border: 2px solid var(--primary-orange);
        }
        
        .btn-secondary:hover {
            background: var(--primary-orange);
            color: white !important;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 6px 20px rgba(255, 107, 53, 0.4);
        }
        
        .btn-ghost {
            background: var(--input-bg);
            color: var(--text-primary) !important;
            border: 1px solid var(--border-color);
        }
        
        .btn-ghost:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary) !important;
            transform: translateY(-2px);
        }
        
        /* Улучшенная видимость btn-ghost в светлой теме */
        [data-theme="light"] .btn-ghost {
            background: #ffffff;
            border: 1.5px solid rgba(23, 26, 32, 0.25);
            box-shadow: 0 1px 3px rgba(23, 26, 32, 0.05);
        }
        
        [data-theme="light"] .btn-ghost:hover {
            background: #f5f7fa;
            border-color: rgba(23, 26, 32, 0.35);
            box-shadow: 0 2px 8px rgba(23, 26, 32, 0.1);
        }
        
        /* === КОНТЕЙНЕРЫ === */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 3rem;
        }
        
        .main-content {
            min-height: calc(100vh - 200px);
            padding-top: 6rem;
            padding-bottom: 4rem;
            animation: pageTransition 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @keyframes pageTransition {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* === КАРТОЧКИ === */
        .card {
            background: linear-gradient(135deg, var(--card-bg-start) 0%, var(--card-bg-end) 100%);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px var(--shadow);
            margin-bottom: 2rem;
            transition: all 0.4s var(--transition-ease);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
        }
        
        /* Более видимые границы для светлой темы */
        [data-theme="light"] .card {
            border: 1px solid rgba(23, 26, 32, 0.12);
            box-shadow: 0 4px 20px rgba(23, 26, 32, 0.08),
                        0 1px 3px rgba(23, 26, 32, 0.06);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px var(--shadow-hover);
            border-color: rgba(255, 107, 53, 0.25);
        }
        
        [data-theme="light"] .card:hover {
            box-shadow: 0 8px 30px rgba(23, 26, 32, 0.12),
                        0 2px 8px rgba(23, 26, 32, 0.08);
            border-color: rgba(255, 107, 53, 0.3);
        }
        
        .card-header {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .card-title {
            font-size: 2rem;
            color: var(--text-primary);
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        /* === FLASH СООБЩЕНИЯ === */
        .flash {
            padding: 1.2rem 1.8rem;
            margin-bottom: 2rem;
            border-radius: 15px;
            font-weight: 500;
            animation: slideDown 0.6s var(--transition-ease);
            backdrop-filter: blur(10px);
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .flash-success {
            background: rgba(76, 217, 100, 0.15);
            color: #4cd964;
            border: 1px solid rgba(76, 217, 100, 0.3);
        }
        
        .flash-error {
            background: rgba(255, 59, 48, 0.15);
            color: #ff3b30;
            border: 1px solid rgba(255, 59, 48, 0.3);
        }
        
        .flash-info {
            background: rgba(52, 199, 89, 0.15);
            color: #34c759;
            border: 1px solid rgba(52, 199, 89, 0.3);
        }
        
        /* === ФОРМЫ === */
        .form-group {
            margin-bottom: 1.8rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.7rem;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.95rem;
        }
        
        .form-control, select, textarea {
            width: 100%;
            padding: 1rem 1.3rem;
            border: 1.5px solid var(--border-color);
            border-radius: 12px;
            font-size: 1rem;
            transition: all var(--transition-speed) var(--transition-ease);
            background: var(--input-bg);
            color: var(--text-primary);
        }
        
        .form-control:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
            transform: translateY(-2px);
        }
        
        /* Улучшенная видимость элементов формы в светлой теме */
        [data-theme="light"] .form-control,
        [data-theme="light"] select,
        [data-theme="light"] textarea {
            border: 1.5px solid rgba(23, 26, 32, 0.25);
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(23, 26, 32, 0.05);
        }
        
        [data-theme="light"] .form-control:hover,
        [data-theme="light"] select:hover,
        [data-theme="light"] textarea:hover {
            border-color: rgba(23, 26, 32, 0.35);
        }
        
        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath fill='%23ff6b35' d='M0 0l6 8 6-8z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 3rem;
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        /* === БЕЙДЖИ === */
        .badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all var(--transition-speed) var(--transition-ease);
        }
        
        .badge-orange {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            color: white;
            box-shadow: 0 2px 10px rgba(255, 107, 53, 0.3);
        }
        
        .badge-green {
            background: rgba(76, 217, 100, 0.2);
            color: #4cd964;
            border: 1px solid rgba(76, 217, 100, 0.3);
        }
        
        .badge-gray {
            background: var(--input-bg);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }
        
        .badge-success {
            background: rgba(76, 217, 100, 0.2);
            color: #4cd964;
            border: 1px solid rgba(76, 217, 100, 0.3);
        }
        
        .badge-info {
            background: rgba(52, 199, 89, 0.2);
            color: #34c759;
            border: 1px solid rgba(52, 199, 89, 0.3);
        }
        
        /* === СКЕЛЕТОН ЛОАДЕР === */
        .skeleton {
            background: linear-gradient(
                90deg,
                var(--bg-secondary) 0%,
                var(--bg-tertiary) 50%,
                var(--bg-secondary) 100%
            );
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
            border-radius: 8px;
        }
        
        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }
            100% {
                background-position: 200% 0;
            }
        }
        
        /* === МОБИЛЬНОЕ МЕНЮ === */
        .mobile-menu-btn {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 10px;
            z-index: 1001;
        }
        
        .mobile-menu-btn span {
            width: 25px;
            height: 3px;
            background: var(--text-primary);
            transition: all var(--transition-speed) var(--transition-ease);
            border-radius: 3px;
        }
        
        .mobile-menu-btn.active span:nth-child(1) {
            transform: rotate(45deg) translate(8px, 8px);
        }
        
        .mobile-menu-btn.active span:nth-child(2) {
            opacity: 0;
        }
        
        .mobile-menu-btn.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }
        
        /* === АДАПТИВНОСТЬ === */
        @media (max-width: 1024px) {
            .navbar-container {
                padding: 1rem 2rem;
            }
            
            .container {
                padding: 0 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .navbar-container {
                padding: 1rem 1.5rem;
            }
            
            .mobile-menu-btn {
                display: flex;
            }
            
            .nav-links {
                position: fixed;
                top: 80px;
                left: 0;
                right: 0;
                background: var(--navbar-bg-scrolled);
                backdrop-filter: blur(20px);
                flex-direction: column;
                gap: 0;
                padding: 2rem;
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all 0.4s var(--transition-ease);
                box-shadow: 0 10px 40px var(--shadow);
            }
            
            .nav-links.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }
            
            .nav-links a {
                padding: 1rem;
                border-bottom: 1px solid var(--border-color);
                width: 100%;
            }
            
            .nav-links a:last-child {
                border-bottom: none;
            }
            
            .container {
                padding: 0 1.5rem;
            }
            
            .card {
                padding: 1.8rem;
            }
            
            .main-content {
                padding-top: 5rem;
            }
            
            .theme-toggle {
                margin-left: auto;
            }
        }
        
        /* === АНИМАЦИИ ПОЯВЛЕНИЯ === */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeIn 0.8s var(--transition-ease) forwards;
        }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .scale-in {
            animation: scaleIn 0.5s var(--transition-ease);
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0.95);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        /* === УВЕДОМЛЕНИЯ === */
        .notification-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            color: white;
            border-radius: 50%;
            padding: 3px 7px;
            font-size: 0.7rem;
            font-weight: bold;
            box-shadow: 0 2px 10px rgba(255, 107, 53, 0.5);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 2px 10px rgba(255, 107, 53, 0.5);
            }
            50% {
                transform: scale(1.15);
                box-shadow: 0 4px 15px rgba(255, 107, 53, 0.7);
            }
        }
    </style>
    
    <script>
        // Управление темой
        document.addEventListener('DOMContentLoaded', function() {
            const html = document.documentElement;
            const savedTheme = localStorage.getItem('theme') || 'dark';
            html.setAttribute('data-theme', savedTheme);
        });
        
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        }
        
        // Эффект прокрутки для навбара
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Мобильное меню
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const navLinks = document.getElementById('navLinks');
            
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function() {
                    this.classList.toggle('active');
                    navLinks.classList.toggle('active');
                });
                
                // Закрыть меню при клике на ссылку
                navLinks.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenuBtn.classList.remove('active');
                        navLinks.classList.remove('active');
                    });
                });
            }
        });
    </script>
</head>
<body>
    <!-- Навбар -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="/" class="logo">
                <div class="logo-icon">🦊</div>
                <span>Foxyd</span>
            </a>
            
            <!-- Гамбургер меню для мобильных -->
            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <div class="nav-links" id="navLinks">
                <a href="/">Главная</a>
                <a href="/courses">Курсы</a>
                
                <?php if (isLoggedIn()): ?>
                    <?php
                    $stmtUnread = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
                    $stmtUnread->execute([getCurrentUserId()]);
                    $unreadNotifications = $stmtUnread->fetchColumn();
                    ?>
                    
                    <a href="/calendar">📅 Календарь</a>
                    <a href="/cabinet" style="position: relative;">
                        Мой прогресс
                        <?php if ($unreadNotifications > 0): ?>
                            <span class="notification-badge"><?= $unreadNotifications ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (isAdmin()): ?>
                        <a href="/admin">Админ</a>
                    <?php endif; ?>
                    
                    <a href="/logout" class="btn btn-ghost">Выход</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-secondary">Вход</a>
                    <a href="/register" class="btn btn-primary">Начать обучение</a>
                <?php endif; ?>
                
                <!-- Переключатель темы -->
                <div class="theme-toggle" onclick="toggleTheme()" title="Переключить тему">
                    <div class="theme-toggle-slider">
                        <span id="themeIcon">🌙</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <script>
        // Обновление иконки темы
        function updateThemeIcon() {
            const theme = document.documentElement.getAttribute('data-theme');
            const icon = document.getElementById('themeIcon');
            if (icon) {
                icon.textContent = theme === 'dark' ? '🌙' : '☀️';
            }
        }
        
        // Переопределение toggleTheme с обновлением иконки
        function toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon();
        }
        
        document.addEventListener('DOMContentLoaded', updateThemeIcon);
    </script>
    
    <!-- Основной контент -->
    <div class="main-content">
        <div class="container">
            <?php 
            $flash = getFlash();
            if ($flash): 
            ?>
                <div class="flash flash-<?= $flash['type'] ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>
