<?php
/**
 * Главная страница Foxyd
 * Ультра-современный дизайн
 */

$stmt = $conn->query("
    SELECT c.*, u.name as instructor_name 
    FROM courses c 
    LEFT JOIN users u ON c.instructor_id = u.id 
    ORDER BY c.students_count DESC 
    LIMIT 4
");
$topCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = 'Главная';
include __DIR__ . '/../templates/header.php';
?>

<!-- Героический баннер -->
<div class="hero-section">
    <div class="hero-content">
        <div class="hero-text">
            <h1 class="hero-title">
                Обучение будущего<br>
                <span class="gradient-text">уже здесь</span>
            </h1>
            <p class="hero-description">
                Освойте востребованные навыки с лучшими инструкторами<br>
                Начните свой путь к успеху прямо сейчас
            </p>
            <?php if (!isLoggedIn()): ?>
                <div class="hero-buttons">
                    <a href="/register" class="btn btn-primary btn-large">Начать бесплатно</a>
                    <a href="/courses" class="btn btn-secondary btn-large">Обзор курсов</a>
                </div>
            <?php else: ?>
                <div class="hero-buttons">
                    <a href="/courses" class="btn btn-primary btn-large">Выбрать курс</a>
                    <a href="/cabinet" class="btn btn-secondary btn-large">Мой прогресс</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="hero-visual">
            <div class="floating-card floating-card-1">
                <div class="card-icon">🎯</div>
                <div class="card-text">
                    <div class="card-title">Практика</div>
                    <div class="card-subtitle">Реальные проекты</div>
                </div>
            </div>
            <div class="floating-card floating-card-2">
                <div class="card-icon">⚡</div>
                <div class="card-text">
                    <div class="card-title">Быстро</div>
                    <div class="card-subtitle">Обучение в темпе</div>
                </div>
            </div>
            <div class="floating-card floating-card-3">
                <div class="card-icon">🏆</div>
                <div class="card-text">
                    <div class="card-title">Результат</div>
                    <div class="card-subtitle">Сертификаты</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Статистика -->
<div class="stats-section">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">1000+</div>
            <div class="stat-label">Студентов</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">50+</div>
            <div class="stat-label">Курсов</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">98%</div>
            <div class="stat-label">Успешных</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">4.9★</div>
            <div class="stat-label">Рейтинг</div>
        </div>
    </div>
</div>

<!-- Популярные курсы -->
<div class="courses-section">
    <div class="section-header">
        <h2 class="section-title">Популярные курсы</h2>
        <a href="/courses" class="section-link">Все курсы →</a>
    </div>
    
    <div class="courses-grid">
        <?php foreach ($topCourses as $course): ?>
            <div class="course-card">
                <div class="course-header">
                    <span class="course-level"><?= $course['level'] === 'beginner' ? 'Начальный' : ($course['level'] === 'intermediate' ? 'Средний' : 'Продвинутый') ?></span>
                    <span class="course-rating">★ <?= number_format($course['rating'], 1) ?></span>
                </div>
                
                <h3 class="course-title"><?= e($course['title']) ?></h3>
                <p class="course-description"><?= e($course['short_description']) ?></p>
                
                <div class="course-meta">
                    <div class="meta-item">
                        <span>👤 <?= $course['students_count'] ?></span>
                    </div>
                    <div class="meta-item">
                        <span>⏱️ <?= formatDuration($course['duration']) ?></span>
                    </div>
                </div>
                
                <div class="course-footer">
                    <div class="course-price"><?= formatPrice($course['price']) ?></div>
                    <a href="/course?id=<?= $course['id'] ?>" class="btn btn-primary">Подробнее</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Наши инструкторы -->
<div class="instructors-section">
    <div class="section-header">
        <h2 class="section-title">Наши инструкторы</h2>
    </div>
    
    <?php
    // Получаем инструкторов
    $stmt = $conn->query("
        SELECT id, name, email 
        FROM users 
        WHERE role = 'instructor'
        ORDER BY name
        LIMIT 3
    ");
    $instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <div class="instructors-grid">
        <?php foreach ($instructors as $instructor): ?>
            <div class="instructor-card">
                <div class="instructor-avatar">
                    <?= strtoupper(substr($instructor['name'], 0, 1)) ?>
                </div>
                <h3><?= e($instructor['name']) ?></h3>
                <p class="instructor-email"><?= e($instructor['email']) ?></p>
                <a href="/calendar" class="btn btn-secondary">Записаться</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Преимущества -->
<div class="features-section">
    <h2 class="section-title">Почему выбирают Foxyd?</h2>
    
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">🎓</div>
            <h3>Экспертные инструкторы</h3>
            <p>Учитесь у профессионалов с многолетним опытом</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Отслеживание прогресса</h3>
            <p>Визуализация ваших достижений в реальном времени</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">💼</div>
            <h3>Практические проекты</h3>
            <p>Реальные кейсы для вашего портфолио</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🌐</div>
            <h3>Гибкий график</h3>
            <p>Обучайтесь в удобное время из любой точки</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>Мобильный доступ</h3>
            <p>Полная адаптация под мобильные устройства</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🎯</div>
            <h3>Персональный подход</h3>
            <p>Индивидуальные консультации с инструкторами</p>
        </div>
    </div>
</div>

<style>
    /* === ГЕРОИЧЕСКИЙ БАННЕР === */
    .hero-section {
        padding: 6rem 0 8rem;
        position: relative;
        overflow: hidden;
    }
    
    .hero-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 800px;
        height: 800px;
        background: radial-gradient(circle, rgba(255, 107, 53, 0.1) 0%, transparent 70%);
        animation: pulse-bg 8s infinite;
    }
    
    @keyframes pulse-bg {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.2); opacity: 0.3; }
    }
    
    .hero-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 4rem;
        align-items: center;
        position: relative;
        z-index: 1;
    }
    
    .hero-title {
        font-size: 4rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 1.5rem;
        letter-spacing: -2px;
    }
    
    .gradient-text {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .hero-description {
        font-size: 1.2rem;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 2.5rem;
    }
    
    .hero-buttons {
        display: flex;
        gap: 1.5rem;
    }
    
    .btn-large {
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
    }
    
    /* === ПЛАВАЮЩИЕ КАРТОЧКИ === */
    .hero-visual {
        position: relative;
        height: 400px;
    }
    
    .floating-card {
        position: absolute;
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        animation: float 6s ease-in-out infinite;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    }
    
    .floating-card-1 { top: 10%; left: 10%; animation-delay: 0s; }
    .floating-card-2 { top: 45%; right: 15%; animation-delay: 2s; }
    .floating-card-3 { bottom: 15%; left: 20%; animation-delay: 4s; }
    
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    
    .card-icon {
        font-size: 2rem;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .card-title { font-weight: 700; font-size: 1.1rem; }
    .card-subtitle { font-size: 0.9rem; color: rgba(255, 255, 255, 0.6); }
    
    /* === СТАТИСТИКА === */
    .stats-section {
        background: linear-gradient(135deg, var(--dark-gray), var(--medium-gray));
        padding: 4rem 3rem;
        border-radius: 25px;
        margin: 4rem 0;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 3rem;
    }
    
    .stat-card { text-align: center; }
    
    .stat-number {
        font-size: 3rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.7);
    }
    
    /* === КУРСЫ === */
    .courses-section { margin: 6rem 0; }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 3rem;
    }
    
    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        letter-spacing: -1px;
    }
    
    .section-link {
        color: var(--primary-orange);
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        transition: all 0.3s;
    }
    
    .section-link:hover {
        gap: 1rem;
    }
    
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
    }
    
    .course-card {
        background: linear-gradient(135deg, var(--dark-gray), rgba(35, 38, 47, 0.8));
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.4s;
        display: flex;
        flex-direction: column;
    }
    
    .course-card:hover {
        transform: translateY(-10px);
        border-color: rgba(255, 107, 53, 0.3);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    }
    
    .course-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .course-level {
        background: rgba(255, 107, 53, 0.2);
        color: var(--primary-orange);
        padding: 0.4rem 0.9rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .course-rating {
        color: #ffd700;
        font-weight: 600;
    }
    
    .course-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    
    .course-description {
        color: rgba(255, 255, 255, 0.6);
        margin-bottom: 1.5rem;
        flex-grow: 1;
    }
    
    .course-meta {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }
    
    .meta-item {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.9rem;
    }
    
    .course-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .course-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-orange);
    }
    
    /* === ПРЕИМУЩЕСТВА === */
    .features-section {
        margin: 6rem 0;
        text-align: center;
    }
    
    /* === ИНСТРУКТОРЫ === */
    .instructors-section {
        margin: 6rem 0;
    }
    
    .instructors-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
    }
    
    .instructor-card {
        background: linear-gradient(135deg, var(--dark-gray), rgba(35, 38, 47, 0.8));
        padding: 3rem 2rem;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.4s;
        text-align: center;
    }
    
    .instructor-card:hover {
        transform: translateY(-10px);
        border-color: rgba(255, 107, 53, 0.3);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    }
    
    .instructor-avatar {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
        color: white;
        margin: 0 auto 1.5rem;
        box-shadow: 0 10px 40px rgba(255, 107, 53, 0.4);
    }
    
    .instructor-card h3 {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }
    
    .instructor-email {
        color: rgba(255, 255, 255, 0.6);
        margin-bottom: 1.5rem;
    }
    
    .features-section {
        margin: 6rem 0;
        text-align: center;
    }
    
    .features-section .section-title {
        margin-bottom: 4rem;
    }
    
    .features-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2.5rem;
    }
    
    .feature-card {
        background: linear-gradient(135deg, var(--dark-gray), rgba(35, 38, 47, 0.8));
        padding: 3rem 2rem;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.4s;
    }
    
    .feature-card:hover {
        transform: translateY(-10px);
        border-color: rgba(255, 107, 53, 0.3);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
    }
    
    .feature-icon {
        font-size: 3rem;
        margin-bottom: 1.5rem;
    }
    
    .feature-card h3 {
        font-size: 1.3rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    
    .feature-card p {
        color: rgba(255, 255, 255, 0.6);
    }
    
    /* === АДАПТИВНОСТЬ === */
    @media (max-width: 1024px) {
        .hero-content { grid-template-columns: 1fr; }
        .hero-visual { display: none; }
        .stats-grid { grid-template-columns: repeat(2, 1fr); }
        .features-grid { grid-template-columns: repeat(2, 1fr); }
        .instructors-grid { grid-template-columns: repeat(2, 1fr); }
    }
    
    @media (max-width: 768px) {
        .hero-title { font-size: 2.5rem; }
        .hero-buttons { flex-direction: column; }
        .stats-grid, .features-grid, .courses-grid, .instructors-grid { grid-template-columns: 1fr; }
    }
</style>

<?php include __DIR__ . '/../templates/footer.php'; ?>
