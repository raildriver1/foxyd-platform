<?php
/**
 * Страница отслеживания прогресса
 * С радиальным графиком навыков
 */

if (!isLoggedIn()) {
    setFlash('error', 'Необходимо авторизоваться');
    redirect('/login');
}

$currentUser = getCurrentUser();
$userCourses = getUserCourses(getCurrentUserId());

// Собираем статистику
$totalLessonsCompleted = 0;
$totalTimeSpent = 0;
$skills = [];

// Получаем прогресс по всем курсам
foreach ($userCourses as $course) {
    $lessons = getCourseLessons($course['id']);
    foreach ($lessons as $lesson) {
        if (isLessonCompleted(getCurrentUserId(), $lesson['id'])) {
            $totalLessonsCompleted++;
            $totalTimeSpent += $lesson['duration'];
            
            // Добавляем навык
            $courseName = $course['title'];
            if (!isset($skills[$courseName])) {
                $skills[$courseName] = 0;
            }
            $skills[$courseName]++;
        }
    }
}

$title = 'Мой прогресс';
include 'templates/header.php';
?>

<style>
    .progress-container-page {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 2rem;
        align-items: start;
    }
    
    .profile-card {
        position: sticky;
        top: 100px;
    }
    
    .profile-header {
        text-align: center;
        padding: 2rem;
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
        border-radius: 16px 16px 0 0;
    }
    
    .profile-avatar {
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        margin: 0 auto 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        border: 4px solid rgba(255, 255, 255, 0.5);
    }
    
    .profile-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .profile-role {
        opacity: 0.9;
        font-size: 0.95rem;
    }
    
    .profile-stats {
        padding: 2rem;
        background: var(--bg-secondary);
        border-radius: 0 0 16px 16px;
    }
    
    .stat-row {
        display: flex;
        justify-content: space-between;
        padding: 1rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    
    .stat-row:last-child {
        border-bottom: none;
    }
    
    .stat-label {
        color: var(--medium-gray);
        font-size: 0.95rem;
    }
    
    .stat-value {
        font-weight: 700;
        color: var(--dark-gray);
        font-size: 1.1rem;
    }
    
    .level-indicator {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }
    
    .level-text {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8rem;
        font-weight: 600;
    }
    
    .level-number {
        color: var(--primary-orange);
        font-size: 1.5rem;
    }
    
    .skills-radar {
        background: var(--bg-secondary);
        border-radius: 16px;
        padding: 2.5rem;
        box-shadow: 0 4px 20px var(--shadow);
        border: 1px solid var(--border-color);
    }
    
    .radar-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 2rem;
        color: var(--dark-gray);
    }
    
    .course-progress-list {
        margin-top: 2rem;
    }
    
    .course-progress-item {
        background: var(--bg-secondary);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 4px 20px var(--shadow);
        border: 1px solid var(--border-color);
        transition: all 0.3s;
    }
    
    .course-progress-item:hover {
        transform: translateX(8px);
        box-shadow: 0 8px 30px var(--shadow-hover);
        border-color: var(--primary-orange);
    }
    
    .course-progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .course-progress-title {
        font-weight: 700;
        color: var(--dark-gray);
        font-size: 1.1rem;
    }
    
    .course-progress-percent {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--primary-orange);
    }
    
    @media (max-width: 1024px) {
        .progress-container-page {
            grid-template-columns: 1fr;
        }
        
        .profile-card {
            position: relative;
            top: 0;
        }
    }
</style>

<div class="progress-container-page">
    <!-- Профиль слева -->
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                <?= substr($currentUser['name'], 0, 1) ?>
            </div>
            <div class="profile-name"><?= e($currentUser['name']) ?></div>
            <div class="profile-role">
                <?php if ($currentUser['role'] === 'student'): ?>
                    🎓 Студент
                <?php elseif ($currentUser['role'] === 'instructor'): ?>
                    👨‍🏫 Преподаватель
                <?php else: ?>
                    👑 Администратор
                <?php endif; ?>
            </div>
        </div>
        
        <div class="profile-stats">
            <div class="stat-row">
                <span class="stat-label">Уроков пройдено</span>
                <span class="stat-value"><?= $totalLessonsCompleted ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Время обучения</span>
                <span class="stat-value"><?= formatDuration($totalTimeSpent) ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Активных курсов</span>
                <span class="stat-value"><?= count($userCourses) ?></span>
            </div>
            
            <div class="level-indicator">
                <div class="level-text">
                    <span>Уровень</span>
                    <span class="level-number">
                        <?= min(10, floor($totalLessonsCompleted / 5) + 1) ?>
                    </span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= ($totalLessonsCompleted % 5) * 20 ?>%"></div>
                </div>
                <div style="text-align: center; margin-top: 0.5rem; font-size: 0.85rem; color: var(--medium-gray);">
                    <?= ($totalLessonsCompleted % 5) ?> / 5 до след. уровня
                </div>
            </div>
        </div>
    </div>
    
    <!-- Радиальный график -->
    <div>
        <div class="skills-radar">
            <h2 class="radar-title">📊 Карта навыков</h2>
            <canvas id="skillsCanvas" width="800" height="800"></canvas>
        </div>
        
        <!-- Прогресс по курсам -->
        <div class="course-progress-list">
            <h2 style="font-size: 2rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--dark-gray);">
                Мои курсы
            </h2>
            
            <?php if (empty($userCourses)): ?>
                <div class="card" style="text-align: center; padding: 3rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📚</div>
                    <p style="font-size: 1.2rem; color: var(--medium-gray);">
                        Вы еще не начали ни одного курса
                    </p>
                    <a href="/courses" class="btn btn-primary" style="margin-top: 1.5rem;">
                        Выбрать курс
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($userCourses as $course): ?>
                    <?php $progress = getCourseProgress(getCurrentUserId(), $course['id']); ?>
                    <div class="course-progress-item">
                        <div class="course-progress-header">
                            <div>
                                <div class="course-progress-title"><?= e($course['title']) ?></div>
                                <div style="color: var(--medium-gray); font-size: 0.9rem; margin-top: 0.3rem;">
                                    <?= e($course['category']) ?>
                                </div>
                            </div>
                            <div class="course-progress-percent"><?= $progress ?>%</div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                        </div>
                        <a href="/course?id=<?= $course['id'] ?>" class="btn btn-secondary" style="width: 100%; margin-top: 1rem;">
                            Продолжить обучение
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Радиальный график навыков (как на картинке)
const canvas = document.getElementById('skillsCanvas');
const ctx = canvas.getContext('2d');

// Данные навыков из PHP
const skills = <?= json_encode(array_keys($skills)) ?>;
const skillValues = <?= json_encode(array_values($skills)) ?>;

// Дополняем общими навыками для красоты
const allSkills = [
    ...skills,
    'Problem Solving',
    'Critical Thinking',
    'Algorithms',
    'Data Structures',
    'Clean Code',
    'Testing',
    'Git & GitHub',
    'API Development'
];

const skillData = allSkills.map((skill, index) => ({
    name: skill,
    value: skillValues[index] || Math.floor(Math.random() * 5) + 1
}));

// Ждем установки темы перед отрисовкой
setTimeout(() => {
function drawRadarChart() {
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const maxRadius = Math.min(centerX, centerY) - 100;
    const angleStep = (Math.PI * 2) / skillData.length;
    
    // Определяем текущую тему
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(23, 26, 32, 0.05)';
    const lineColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(23, 26, 32, 0.1)';
    const textColor = isDark ? '#ffffff' : '#171a20';
    const centerColor = isDark ? '#ffffff' : '#171a20';
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    // Рисуем круги-уровни
    for (let i = 1; i <= 5; i++) {
        ctx.beginPath();
        ctx.arc(centerX, centerY, (maxRadius / 5) * i, 0, Math.PI * 2);
        ctx.strokeStyle = gridColor;
        ctx.lineWidth = 1;
        ctx.stroke();
    }
    
    // Рисуем линии к навыкам
    skillData.forEach((skill, index) => {
        const angle = angleStep * index - Math.PI / 2;
        const endX = centerX + Math.cos(angle) * maxRadius;
        const endY = centerY + Math.sin(angle) * maxRadius;
        
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.lineTo(endX, endY);
        ctx.strokeStyle = lineColor;
        ctx.lineWidth = 1;
        ctx.stroke();
        
        // Рисуем точку навыка
        const value = Math.min(skill.value, 5);
        const pointRadius = (maxRadius / 5) * value;
        const pointX = centerX + Math.cos(angle) * pointRadius;
        const pointY = centerY + Math.sin(angle) * pointRadius;
        
        ctx.beginPath();
        ctx.arc(pointX, pointY, 6, 0, Math.PI * 2);
        ctx.fillStyle = '#ff6b35';
        ctx.fill();
        
        // Текст навыка
        const textX = centerX + Math.cos(angle) * (maxRadius + 40);
        const textY = centerY + Math.sin(angle) * (maxRadius + 40);
        
        ctx.fillStyle = textColor;
        ctx.font = '12px -apple-system, BlinkMacSystemFont, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(skill.name, textX, textY);
    });
    
    // Соединяем точки (полигон)
    ctx.beginPath();
    skillData.forEach((skill, index) => {
        const angle = angleStep * index - Math.PI / 2;
        const value = Math.min(skill.value, 5);
        const pointRadius = (maxRadius / 5) * value;
        const pointX = centerX + Math.cos(angle) * pointRadius;
        const pointY = centerY + Math.sin(angle) * pointRadius;
        
        if (index === 0) {
            ctx.moveTo(pointX, pointY);
        } else {
            ctx.lineTo(pointX, pointY);
        }
    });
    ctx.closePath();
    ctx.fillStyle = 'rgba(255, 107, 53, 0.2)';
    ctx.fill();
    ctx.strokeStyle = '#ff6b35';
    ctx.lineWidth = 2;
    ctx.stroke();
    
    // Центральная точка
    ctx.beginPath();
    ctx.arc(centerX, centerY, 8, 0, Math.PI * 2);
    ctx.fillStyle = centerColor;
    ctx.fill();
}

// Анимация появления
let animationProgress = 0;
function animate() {
    if (animationProgress < 1) {
        animationProgress += 0.02;
        requestAnimationFrame(animate);
    }
    drawRadarChart();
}

animate();

// Перерисовка при переключении темы
const originalToggleTheme = window.toggleTheme;
window.toggleTheme = function() {
    if (originalToggleTheme) {
        originalToggleTheme();
    }
    setTimeout(() => {
        drawRadarChart();
    }, 50);
};
}, 100); // Закрываем setTimeout для ожидания установки темы
</script>

<?php include 'templates/footer.php'; ?>
