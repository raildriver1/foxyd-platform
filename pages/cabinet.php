<?php
/**
 * Личный кабинет - отслеживание прогресса
 * С радиальной диаграммой навыков
 */

if (!isLoggedIn()) {
    setFlash('error', 'Необходимо авторизоваться');
    redirect('/login');
}

$user = getCurrentUser();

// Проверка что пользователь существует
if (!$user) {
    setFlash('error', 'Ошибка загрузки профиля');
    redirect('/login');
}

// Для репетиторов получаем заявки от школьников
$appointments = [];
if ($user['role'] === 'tutor') {
    $stmt = $conn->prepare("
        SELECT a.*, u.name as student_name, u.email as student_email 
        FROM appointments a
        LEFT JOIN users u ON a.student_id = u.id
        WHERE a.instructor_id = ?
        ORDER BY a.appointment_date DESC, a.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Получаем курсы пользователя
$userCourses = getUserCourses($user['id']);

// Получаем общую статистику
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT course_id) as courses_count,
           COUNT(*) as lessons_completed
    FROM progress 
    WHERE user_id = ? AND completed = 1
");
$stmt->execute([$user['id']]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Проверяем что статистика получена
if (!$stats) {
    $stats = ['courses_count' => 0, 'lessons_completed' => 0];
}

// Вычисляем навыки на основе пройденных курсов
$skills = [];
foreach ($userCourses as $course) {
    $progress = getCourseProgress($user['id'], $course['id']);
    if ($progress > 0) {
        $category = $course['category'] ?? 'Общие';
        if (!isset($skills[$category])) {
            $skills[$category] = 0;
        }
        $skills[$category] += $progress;
    }
}

// Нормализуем навыки (максимум 100)
foreach ($skills as $skill => $value) {
    $skills[$skill] = min(100, $value);
}

// Если нет навыков - добавляем демо данные для визуализации
if (empty($skills)) {
    $skills = [
        'Алгебра' => 0,
        'Геометрия' => 0,
        'Математический анализ' => 0,
        'Теория вероятностей' => 0,
        'Статистика' => 0
    ];
}

$title = 'Мой прогресс';
include __DIR__ . '/../templates/header.php';
?>

<div class="profile-container">
    <div class="profile-sidebar">
        <!-- Профиль пользователя -->
        <div class="profile-card">
            <div class="profile-avatar">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            <h2 class="profile-name"><?= e($user['name']) ?></h2>
            <p class="profile-email"><?= e($user['email']) ?></p>
            
            <!-- Роль пользователя -->
            <div style="margin-bottom: 1.5rem;">
                <?php
                $roleLabels = [
                    'admin' => '👑 Администратор',
                    'tutor' => '👨‍🏫 Репетитор',
                    'student' => '🎓 Школьник'
                ];
                $roleColors = [
                    'admin' => 'linear-gradient(135deg, #ff6b35, #ff8c42)',
                    'tutor' => 'linear-gradient(135deg, #5ac8fa, #007aff)',
                    'student' => 'linear-gradient(135deg, #4cd964, #34c759)'
                ];
                $roleLabel = $roleLabels[$user['role']] ?? '🎓 Школьник';
                $roleColor = $roleColors[$user['role']] ?? $roleColors['student'];
                ?>
                <div class="badge" style="background: <?= $roleColor ?>; color: white; padding: 0.5rem 1.2rem; font-size: 0.95rem; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    <?= $roleLabel ?>
                </div>
            </div>
            
            <?php if ($user['role'] !== 'tutor' && $user['role'] !== 'admin'): ?>
            <!-- Уровень пользователя -->
            <div class="user-level">
                <div class="level-header">
                    <span>Уровень <?= min(10, floor($stats['lessons_completed'] / 10) + 1) ?></span>
                    <span><?= ($stats['lessons_completed'] % 10) * 10 ?>%</span>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= ($stats['lessons_completed'] % 10) * 10 ?>%"></div>
                </div>
            </div>
            
            <!-- Очки -->
            <div class="points-section">
                <h3>Очки</h3>
                <div class="points-value"><?= $stats['lessons_completed'] * 150 ?> XP</div>
                <div class="badges">
                    <?php if ($stats['lessons_completed'] >= 5): ?>
                        <span class="badge badge-orange">5 PHP</span>
                    <?php endif; ?>
                    <?php if ($stats['lessons_completed'] >= 10): ?>
                        <span class="badge badge-success">5 С++</span>
                    <?php endif; ?>
                    <?php if ($stats['lessons_completed'] >= 15): ?>
                        <span class="badge badge-info">Активный</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="profile-main">
        <?php if ($user['role'] !== 'tutor' && $user['role'] !== 'admin'): ?>
        <!-- Радиальная диаграмма навыков -->
        <div class="card skills-card">
            <div class="card-header">
                <h2 class="card-title">Навыки</h2>
            </div>
            
            <div class="skills-container">
                <div style="max-width: 500px; margin: 0 auto;">
                    <canvas id="skillsChart" width="500" height="500"></canvas>
                </div>
            </div>
            
            <div class="skills-legend">
                <?php foreach ($skills as $skill => $value): ?>
                    <div class="legend-item">
                        <span class="legend-dot"></span>
                        <span class="legend-name"><?= e($skill) ?></span>
                        <span class="legend-value"><?= round($value) ?>%</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($user['role'] === 'tutor'): ?>
        <!-- Заявки от школьников для репетиторов -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">📋 Заявки на занятия (<?= count($appointments) ?>)</h2>
            </div>
            
            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📅</div>
                    <h3>У вас пока нет заявок</h3>
                    <p>Школьники смогут записываться к вам через календарь</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <?php foreach ($appointments as $appointment): ?>
                        <?php
                        $statusColors = [
                            'pending' => ['bg' => 'rgba(255, 140, 66, 0.1)', 'text' => '#ff8c42', 'label' => '⏳ Ожидает'],
                            'confirmed' => ['bg' => 'rgba(76, 217, 100, 0.1)', 'text' => '#4cd964', 'label' => '✓ Подтверждено'],
                            'cancelled' => ['bg' => 'rgba(255, 59, 48, 0.1)', 'text' => '#ff3b30', 'label' => '✗ Отменено']
                        ];
                        $status = $statusColors[$appointment['status']] ?? $statusColors['pending'];
                        ?>
                        <div style="padding: 1.5rem; background: var(--input-bg); border-radius: 12px; border: 1px solid var(--border-color);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem;"><?= e($appointment['student_name']) ?></h3>
                                    <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                        📧 <?= e($appointment['student_email']) ?>
                                    </div>
                                </div>
                                <div style="padding: 0.4rem 1rem; background: <?= $status['bg'] ?>; color: <?= $status['text'] ?>; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                                    <?= $status['label'] ?>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem; padding: 1rem; background: var(--bg-tertiary); border-radius: 8px;">
                                <div>
                                    <div style="color: var(--text-tertiary); font-size: 0.85rem; margin-bottom: 0.3rem;">Дата и время</div>
                                    <div style="font-weight: 600;">📅 <?= date('d.m.Y H:i', strtotime($appointment['appointment_date'])) ?></div>
                                </div>
                                <div>
                                    <div style="color: var(--text-tertiary); font-size: 0.85rem; margin-bottom: 0.3rem;">Длительность</div>
                                    <div style="font-weight: 600;">⏱️ <?= $appointment['duration'] ?> мин</div>
                                </div>
                            </div>
                            
                            <?php if ($appointment['notes']): ?>
                                <div style="padding: 1rem; background: var(--bg-tertiary); border-radius: 8px; margin-bottom: 1rem;">
                                    <div style="color: var(--text-tertiary); font-size: 0.85rem; margin-bottom: 0.5rem;">Примечания:</div>
                                    <div style="white-space: pre-wrap;"><?= e($appointment['notes']) ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <div style="color: var(--text-tertiary); font-size: 0.85rem;">
                                Создана: <?= date('d.m.Y H:i', strtotime($appointment['created_at'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($user['role'] === 'student'): ?>
        <!-- Мои курсы -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Мои курсы</h2>
            </div>
            
            <?php if (empty($userCourses)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📚</div>
                    <h3>Вы еще не записаны ни на один курс</h3>
                    <p>Выберите курс из каталога и начните обучение</p>
                    <a href="/courses" class="btn btn-primary">Выбрать курс</a>
                </div>
            <?php else: ?>
                <div class="courses-list">
                    <?php foreach ($userCourses as $course): ?>
                        <?php $progress = getCourseProgress($user['id'], $course['id']); ?>
                        <div class="course-item">
                            <div class="course-info">
                                <h3><?= e($course['title']) ?></h3>
                                <p><?= e($course['short_description']) ?></p>
                            </div>
                            
                            <div class="course-progress">
                                <div class="progress-header">
                                    <span>Прогресс</span>
                                    <span class="progress-percent"><?= $progress ?>%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                                </div>
                            </div>
                            
                            <a href="/course?id=<?= $course['id'] ?>" class="btn btn-primary">Продолжить</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js для радиальной диаграммы -->
<?php if ($user['role'] === 'student'): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Данные навыков из PHP
const skillsData = <?= json_encode($skills) ?>;

// Подготовка данных для диаграммы
const labels = Object.keys(skillsData);
const data = Object.values(skillsData);

// Проверка что есть canvas элемент
const canvas = document.getElementById('skillsChart');
if (canvas && labels.length > 0) {
    // Ждем немного чтобы тема успела установиться из localStorage
    setTimeout(() => {
        const ctx = canvas.getContext('2d');
        
        // Определяем текущую тему
        const getCurrentTheme = () => document.documentElement.getAttribute('data-theme');
        const isDark = () => getCurrentTheme() === 'dark';
        
        // Функция для получения цветов в зависимости от темы
        const getThemeColors = () => {
            const dark = isDark();
            return {
                gridColor: dark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(23, 26, 32, 0.1)',
                tickColor: dark ? 'rgba(255, 255, 255, 0.5)' : 'rgba(23, 26, 32, 0.5)',
                labelColor: dark ? 'rgba(255, 255, 255, 0.9)' : 'rgba(23, 26, 32, 0.9)',
                angleLineColor: dark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(23, 26, 32, 0.1)'
            };
        };
        
        // Важно: получаем цвета ПОСЛЕ того как тема установлена
        let colors = getThemeColors();
    
    const chart = new Chart(ctx, {
        type: 'radar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Мои навыки (%)',
                data: data,
                fill: true,
                backgroundColor: 'rgba(255, 107, 53, 0.2)',
                borderColor: 'rgba(255, 107, 53, 1)',
                pointBackgroundColor: 'rgba(255, 107, 53, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(255, 107, 53, 1)',
                borderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true,
                    backgroundColor: 'rgba(23, 26, 32, 0.95)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(255, 107, 53, 0.5)',
                    borderWidth: 2,
                    padding: 15,
                    displayColors: false,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 16
                    },
                    callbacks: {
                        label: function(context) {
                            return context.parsed.r.toFixed(0) + '%';
                        }
                    }
                }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    min: 0,
                    max: 100,
                    ticks: {
                        stepSize: 20,
                        color: colors.tickColor,
                        backdropColor: 'transparent',
                        font: {
                            size: 13
                        },
                        callback: function(value) {
                            return value + '%';
                        }
                    },
                    grid: {
                        color: colors.gridColor,
                        circular: true
                    },
                    pointLabels: {
                        color: colors.labelColor,
                        font: {
                            size: 14,
                            weight: '700'
                        },
                        padding: 15
                    },
                    angleLines: {
                        color: colors.angleLineColor
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });
    
    // Обновление цветов при смене темы
    const originalToggleTheme = window.toggleTheme;
    window.toggleTheme = function() {
        if (originalToggleTheme) {
            originalToggleTheme();
        }
        
        // Обновляем цвета диаграммы
        setTimeout(() => {
            const newColors = getThemeColors();
            chart.options.scales.r.ticks.color = newColors.tickColor;
            chart.options.scales.r.grid.color = newColors.gridColor;
            chart.options.scales.r.pointLabels.color = newColors.labelColor;
            chart.options.scales.r.angleLines.color = newColors.angleLineColor;
            chart.update();
        }, 50);
    };
    }, 100); // Закрываем setTimeout для ожидания установки темы
} else {
    console.error('Canvas element not found or no skill data available');
}
</script>
<?php endif; ?>

<style>
    .profile-container {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 2rem;
    }
    
    /* === САЙДБАР === */
    .profile-sidebar {
        position: sticky;
        top: 100px;
        height: fit-content;
    }
    
    .profile-card {
        background: var(--bg-secondary);
        padding: 2rem;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        text-align: center;
        box-shadow: 0 4px 20px var(--shadow);
    }
    
    .profile-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 700;
        color: white;
        margin: 0 auto 1.2rem;
        box-shadow: 0 10px 40px rgba(255, 107, 53, 0.4);
    }
    
    .profile-name {
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.4rem;
    }
    
    .profile-email {
        color: rgba(255, 255, 255, 0.6);
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }
    
    [data-theme="light"] .profile-email {
        color: rgba(23, 26, 32, 0.6);
    }
    
    .user-level {
        background: rgba(255, 255, 255, 0.05);
        padding: 1.2rem;
        border-radius: 15px;
        margin-bottom: 1.5rem;
    }
    
    [data-theme="light"] .user-level {
        background: rgba(23, 26, 32, 0.05);
    }
    
    .level-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8rem;
        font-weight: 600;
    }
    
    .points-section {
        text-align: center;
    }
    
    .points-section h3 {
        font-size: 1.1rem;
        margin-bottom: 1rem;
        color: rgba(255, 255, 255, 0.7);
    }
    
    [data-theme="light"] .points-section h3 {
        color: rgba(23, 26, 32, 0.7);
    }
    
    .points-value {
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 1.5rem;
    }
    
    .badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    /* === НАВЫКИ === */
    .skills-card {
        margin-bottom: 2rem;
    }
    
    .skills-container {
        display: flex;
        justify-content: center;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 15px;
    }
    
    [data-theme="light"] .skills-container {
        background: rgba(23, 26, 32, 0.02);
    }
    
    #skillsChart {
        max-width: 100%;
        height: auto !important;
    }
    
    .skills-legend {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.8rem;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 10px;
    }
    
    [data-theme="light"] .legend-item {
        background: rgba(23, 26, 32, 0.05);
        border: 1px solid rgba(23, 26, 32, 0.08);
    }
    
    .legend-dot {
        width: 12px;
        height: 12px;
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        border-radius: 50%;
    }
    
    .legend-name {
        flex-grow: 1;
        font-weight: 600;
    }
    
    .legend-value {
        color: var(--primary-orange);
        font-weight: 700;
    }
    
    /* === КУРСЫ === */
    .courses-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .course-item {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 2rem;
        align-items: center;
        padding: 2rem;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s;
    }
    
    [data-theme="light"] .course-item {
        background: rgba(23, 26, 32, 0.02);
        border: 1px solid rgba(23, 26, 32, 0.08);
    }
    
    .course-item:hover {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(255, 107, 53, 0.3);
    }
    
    [data-theme="light"] .course-item:hover {
        background: rgba(23, 26, 32, 0.04);
        border-color: rgba(255, 107, 53, 0.3);
    }
    
    .course-info h3 {
        font-size: 1.3rem;
        margin-bottom: 0.5rem;
    }
    
    .course-info p {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.95rem;
    }
    
    [data-theme="light"] .course-info p {
        color: rgba(23, 26, 32, 0.6);
    }
    
    .course-progress {
        min-width: 200px;
    }
    
    .progress-percent {
        color: var(--primary-orange);
        font-weight: 700;
    }
    
    /* === ПУСТОЕ СОСТОЯНИЕ === */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }
    
    .empty-icon {
        font-size: 5rem;
        margin-bottom: 1.5rem;
    }
    
    .empty-state h3 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }
    
    .empty-state p {
        color: rgba(255, 255, 255, 0.6);
        margin-bottom: 2rem;
    }
    
    [data-theme="light"] .empty-state p {
        color: rgba(23, 26, 32, 0.6);
    }
    
    /* === АДАПТИВНОСТЬ === */
    @media (max-width: 1200px) {
        .profile-container {
            grid-template-columns: 280px 1fr;
            gap: 1.5rem;
        }
        
        .profile-card {
            padding: 1.5rem;
        }
        
        .profile-avatar {
            width: 70px;
            height: 70px;
            font-size: 2rem;
        }
    }
    
    @media (max-width: 1024px) {
        .profile-container {
            grid-template-columns: 1fr;
        }
        
        .profile-sidebar {
            position: relative;
            top: 0;
        }
        
        .course-item {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .skills-legend {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .skills-legend {
            grid-template-columns: 1fr;
        }
        
        .skills-container {
            padding: 0.5rem;
        }
        
        .card {
            padding: 1.5rem;
        }
        
        .profile-card {
            padding: 1.5rem;
        }
        
        .course-item {
            padding: 1.5rem;
        }
    }
</style>

<?php include __DIR__ . '/../templates/footer.php'; ?>
