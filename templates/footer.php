        </div>
    </div>
    
    <!-- Футер в стиле Tesla -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-grid">
                    <div class="footer-col">
                        <div class="footer-logo">
                            <div class="logo-icon">🦊</div>
                            <span>Foxyd</span>
                        </div>
                        <p class="footer-description">
                            Современная платформа онлайн-обучения<br>
                            для тех, кто стремится к знаниям
                        </p>
                    </div>
                    
                    <div class="footer-col">
                        <h4>Обучение</h4>
                        <ul>
                            <li><a href="/courses">Все курсы</a></li>
                            <li><a href="/calendar">Календарь</a></li>
                            <li><a href="/cabinet">Мой прогресс</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-col">
                        <h4>Поддержка</h4>
                        <ul>
                            <li><a href="https://t.me/egorkin_21" target="_blank">Telegram</a></li>
                            <li><a href="mailto:support@foxyd.ru">Email</a></li>

                        </ul>
                    </div>
                    
                    <div class="footer-col">
                        <h4>О платформе</h4>
                        <ul>
                            <li><a href="#">О нас</a></li>
                            <li><a href="#">Инструкторы</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="footer-bottom">
                    <p>&copy; <?= date('Y') ?> Foxyd. Все права защищены.</p>
                    <p class="made-with-love">
                        Сделано с <span class="heart">❤️</span> для Проектной деятельности
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <style>
        .footer {
            background: linear-gradient(180deg, var(--dark) 0%, var(--bg-dark) 100%);
            color: rgba(255, 255, 255, 0.7);
            padding: 4rem 0 2rem;
            margin-top: 6rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        [data-theme="light"] .footer {
            background: linear-gradient(180deg, #f5f7fa 0%, #e8ecef 100%);
            color: rgba(23, 26, 32, 0.7);
            border-top: 1px solid rgba(23, 26, 32, 0.12);
        }
        
        .footer-content {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .footer-grid {
            display: grid;
            grid-template-columns: 2fr repeat(3, 1fr);
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .footer-col h4 {
            color: var(--white);
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1.2rem;
            letter-spacing: 0.3px;
        }
        
        [data-theme="light"] .footer-col h4 {
            color: var(--text-primary);
        }
        
        .footer-col ul {
            list-style: none;
        }
        
        .footer-col ul li {
            margin-bottom: 0.8rem;
        }
        
        .footer-col ul li a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.95rem;
        }
        
        [data-theme="light"] .footer-col ul li a {
            color: rgba(23, 26, 32, 0.6);
        }
        
        .footer-col ul li a:hover {
            color: var(--primary-orange);
            padding-left: 5px;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1.2rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--white);
        }
        
        [data-theme="light"] .footer-logo {
            color: var(--text-primary);
        }
        
        .footer-logo .logo-icon {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .footer-description {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.95rem;
            line-height: 1.6;
            margin-top: 1rem;
        }
        
        [data-theme="light"] .footer-description {
            color: rgba(23, 26, 32, 0.5);
        }
        
        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
        }
        
        [data-theme="light"] .footer-bottom {
            border-top: 1px solid rgba(23, 26, 32, 0.12);
        }
        
        .made-with-love {
            color: rgba(255, 255, 255, 0.5);
        }
        
        [data-theme="light"] .made-with-love {
            color: rgba(23, 26, 32, 0.5);
        }
        
        .heart {
            color: var(--primary-orange);
            animation: heartbeat 1.5s infinite;
            display: inline-block;
        }
        
        @keyframes heartbeat {
            0%, 100% {
                transform: scale(1);
            }
            25% {
                transform: scale(1.1);
            }
            50% {
                transform: scale(1);
            }
        }
        
        @media (max-width: 1024px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .footer-col:first-child {
                grid-column: 1 / -1;
            }
        }
        
        @media (max-width: 768px) {
            .footer {
                padding: 3rem 0 1.5rem;
            }
            
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .footer-bottom {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
        }
    </style>
</body>
</html>
