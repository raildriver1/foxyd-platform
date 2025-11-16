<!DOCTYPE html>
<html lang="ru">
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
            
            /* Тёмная тема (по умолчанию) */
            --bg-primary: #171a20;
            --bg-secondary: #23262f;
            --bg-tertiary: #393c41;
            --bg-elevated: #0a0b0d;
            --text-primary: #ffffff;
            --text-secondary: rgba(255, 255, 255, 0.7);
            --text-tertiary: rgba(255, 255, 255, 0.5);
            --border-color: rgba(255, 255, 255, 0.1);
            --card-bg: linear-gradient(135deg, #23262f 0%, rgba(35, 38, 47, 0.8) 100%);
            --input-bg: rgba(255, 255, 255, 0.05);
            --shadow: rgba(0, 0, 0, 0.3);
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
            --border-color: rgba(23, 26, 32, 0.1);
            --card-bg: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            --input-bg: #f5f7fa;
            --shadow: rgba(23, 26, 32, 0.1);
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--white);
            background: var(--dark);
            overflow-x: hidden;
        }
        
        /* === НАВБАР В СТИЛЕ TESLA === */
        .navbar {
            background: rgba(23, 26, 32, 0.8);
            backdrop-filter: blur(20px) saturate(180%);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .navbar.scrolled {
            background: rgba(23, 26, 32, 0.95);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3);
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
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            transition: transform 0.3s;
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
        }
        
        .nav-links {
            display: flex;
            gap: 2.5rem;
            align-items: center;
        }
        
        .nav-links a {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s;
            position: relative;
            opacity: 0.9;
        }
        
        .nav-links a:hover {
            opacity: 1;
            color: var(--primary-orange);
        }
        
        /* === КНОПКИ === */
        .btn {
            display: inline-block;
            padding: 0.75rem 1.8rem;
            border-radius: 50px;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-orange) 0%, var(--secondary-orange) 100%);
            color: white;
            box-shadow: 0 4px 20px rgba(255, 107, 53, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(255, 107, 53, 0.5);
        }
        
        .btn-secondary {
            background: transparent;
            color: var(--primary-orange);
            border: 2px solid var(--primary-orange);
        }
        
        .btn-secondary:hover {
            background: var(--primary-orange);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-ghost {
            background: rgba(255, 255, 255, 0.05);
            color: var(--white);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
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
        }
        
        /* === КАРТОЧКИ === */
        .card {
            background: linear-gradient(135deg, var(--dark-gray) 0%, rgba(35, 38, 47, 0.8) 100%);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 2rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
        }
        
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 107, 53, 0.3);
        }
        
        .card-header {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .card-title {
            font-size: 2rem;
            color: var(--white);
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        /* === FLASH СООБЩЕНИЯ === */
        .flash {
            padding: 1.2rem 1.8rem;
            margin-bottom: 2rem;
            border-radius: 15px;
            font-weight: 500;
            animation: slideDown 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .flash-success {
            background: rgba(76, 217, 100, 0.2);
            color: #4cd964;
            border: 1px solid rgba(76, 217, 100, 0.3);
        }
        
        .flash-error {
            background: rgba(255, 59, 48, 0.2);
            color: #ff3b30;
            border: 1px solid rgba(255, 59, 48, 0.3);
        }
        
        .flash-info {
            background: rgba(52, 199, 89, 0.2);
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
            color: var(--white);
            font-size: 0.95rem;
        }
        
        .form-control, select, textarea {
            width: 100%;
            padding: 1rem 1.3rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.05);
            color: var(--white);
        }
        
        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23ffffff' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 3rem;
        }
        
        select option {
            background: var(--dark-gray);
            color: var(--white);
            padding: 1rem;
        }
        
        .form-control:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
            background: rgba(255, 255, 255, 0.08);
        }
        
        /* === СЕТКА === */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
        }
        
        /* === БЕЙДЖИ === */
        .badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }
        
        .badge-orange {
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);
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
        
        /* === ПРОГРЕСС БАР === */
        .progress-container {
            margin: 1.5rem 0;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .progress-bar {
            width: 100%;
            height: 10px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary-orange), var(--secondary-orange));
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 0 15px rgba(255, 107, 53, 0.5);
            position: relative;
            overflow: hidden;
        }
        
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }
        
        /* === АДАПТИВНОСТЬ === */
        
        /* Гамбургер меню */
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
            background: var(--white);
            transition: all 0.3s;
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
                background: rgba(23, 26, 32, 0.98);
                backdrop-filter: blur(20px);
                flex-direction: column;
                gap: 0;
                padding: 2rem;
                transform: translateY(-100%);
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            }
            
            .nav-links.active {
                transform: translateY(0);
                opacity: 1;
                visibility: visible;
            }
            
            .nav-links a {
                padding: 1rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }
            
            .nav-links a:last-child {
                border-bottom: none;
            }
            
            .container {
                padding: 0 1.5rem;
            }
            
            .grid {
                grid-template-columns: 1fr;
            }
            
            .card {
                padding: 1.8rem;
            }
            
            .main-content {
                padding-top: 6rem;
            }
        }
        
        /* === ЭФФЕКТЫ ПРИ СКРОЛЛЕ === */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
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
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }
    </style>
    
    <script>
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
        
        // Анимация появления элементов при скролле
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('.card').forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</head>
<body>
    <!-- Навбар в стиле Tesla -->
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
            </div>
        </div>
    </nav>
    
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
