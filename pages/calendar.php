<?php
/**
 * Календарь со streak-трекингом активности
 * Отслеживание дней занятий и запись к инструкторам
 */

if (!isLoggedIn()) {
    setFlash('error', 'Необходимо авторизоваться');
    redirect('/login');
}

$user = getCurrentUser();

// Получаем список репетиторов
$stmt = $conn->query("
    SELECT id, name, email 
    FROM users 
    WHERE role = 'tutor'
    ORDER BY name
");
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем предстоящие встречи
$upcomingAppointments = getUpcomingAppointments($user['id']);

// Получаем активность пользователя для streak (дни с прогрессом)
$stmt = $conn->prepare("
    SELECT DISTINCT DATE(completed_at) as activity_date
    FROM progress
    WHERE user_id = ? AND completed = 1 AND completed_at IS NOT NULL
    ORDER BY activity_date DESC
");
$stmt->execute([$user['id']]);
$activityDates = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Вычисляем текущий streak
$currentStreak = 0;
$maxStreak = 0;
$tempStreak = 0;
$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

if (!empty($activityDates)) {
    // Проверяем текущий streak
    if ($activityDates[0] === $today || $activityDates[0] === $yesterday) {
        $currentStreak = 1;
        $lastDate = strtotime($activityDates[0]);
        
        for ($i = 1; $i < count($activityDates); $i++) {
            $currentDate = strtotime($activityDates[$i]);
            $diff = ($lastDate - $currentDate) / 86400; // разница в днях
            
            if ($diff == 1) {
                $currentStreak++;
                $lastDate = $currentDate;
            } else {
                break;
            }
        }
    }
    
    // Вычисляем максимальный streak
    $tempStreak = 1;
    $maxStreak = 1;
    $lastDate = strtotime($activityDates[0]);
    
    for ($i = 1; $i < count($activityDates); $i++) {
        $currentDate = strtotime($activityDates[$i]);
        $diff = ($lastDate - $currentDate) / 86400;
        
        if ($diff == 1) {
            $tempStreak++;
            $maxStreak = max($maxStreak, $tempStreak);
        } else {
            $tempStreak = 1;
        }
        $lastDate = $currentDate;
    }
}

// Обработка создания встречи
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_appointment'])) {
    $instructorId = (int)$_POST['instructor_id'];
    $appointmentDate = $_POST['appointment_date'];
    $appointmentTime = $_POST['appointment_time'];
    $duration = (int)$_POST['duration'];
    $phone = trim($_POST['phone'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    $appointmentDateTime = $appointmentDate . ' ' . $appointmentTime;
    
    // Добавляем телефон в примечания если указан
    $fullNotes = $notes;
    if (!empty($phone)) {
        $fullNotes = "Телефон: " . $phone . ($notes ? "\n\n" . $notes : "");
    }
    
    $stmt = $conn->prepare("
        INSERT INTO appointments (student_id, instructor_id, appointment_date, duration, notes, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    
    if ($stmt->execute([$user['id'], $instructorId, $appointmentDateTime, $duration, $fullNotes])) {
        setFlash('success', 'Запись создана! Инструктор свяжется с вами');
        redirect('/calendar');
    } else {
        setFlash('error', 'Ошибка при создании записи');
    }
}

$title = 'Календарь';
include __DIR__ . '/../templates/header.php';
?>

<div class="calendar-container">
    <div class="calendar-main">
        <!-- Статистика активности -->
        <div class="card glass-card">
            <div class="streak-stats">
                <div class="streak-item">
                    <div class="streak-icon">🔥</div>
                    <div class="streak-info">
                        <div class="streak-value"><?= $currentStreak ?></div>
                        <div class="streak-label">Текущий streak</div>
                    </div>
                </div>
                
                <div class="streak-item">
                    <div class="streak-icon">⭐</div>
                    <div class="streak-info">
                        <div class="streak-value"><?= $maxStreak ?></div>
                        <div class="streak-label">Максимальный</div>
                    </div>
                </div>
                
                <div class="streak-item">
                    <div class="streak-icon">📊</div>
                    <div class="streak-info">
                        <div class="streak-value"><?= count($activityDates) ?></div>
                        <div class="streak-label">Дней активности</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Создание новой записи -->
        <div class="card glass-card">
            <div class="card-header">
                <h2 class="card-title">📅 Запись на консультацию</h2>
            </div>
            
            <form method="POST" class="appointment-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="instructor_id">Выберите инструктора *</label>
                        <select id="instructor_id" name="instructor_id" class="form-control" required>
                            <option value="">-- Выберите инструктора --</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?= $instructor['id'] ?>">
                                    <?= e($instructor['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Ваш телефон *</label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="form-control" 
                            placeholder="+7 (999) 123-45-67"
                            value="<?= e($user['phone'] ?? '') ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="appointment_date">Дата *</label>
                        <input 
                            type="date" 
                            id="appointment_date" 
                            name="appointment_date" 
                            class="form-control" 
                            min="<?= date('Y-m-d') ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="appointment_time">Время *</label>
                        <input 
                            type="time" 
                            id="appointment_time" 
                            name="appointment_time" 
                            class="form-control"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="duration">Длительность *</label>
                        <select id="duration" name="duration" class="form-control" required>
                            <option value="30">30 минут</option>
                            <option value="60" selected>60 минут</option>
                            <option value="90">90 минут</option>
                            <option value="120">120 минут</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="notes">Комментарий</label>
                    <textarea 
                        id="notes" 
                        name="notes" 
                        class="form-control" 
                        rows="3"
                        placeholder="Опишите тему консультации или вопросы, которые хотите обсудить"
                    ></textarea>
                </div>
                
                <button type="submit" name="create_appointment" class="btn btn-primary btn-large">
                    ✨ Создать запись
                </button>
            </form>
        </div>
        
        <!-- Предстоящие встречи -->
        <div class="card glass-card">
            <div class="card-header">
                <h2 class="card-title">🗓️ Ваши встречи</h2>
            </div>
            
            <?php if (empty($upcomingAppointments)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📅</div>
                    <h3>У вас пока нет запланированных встреч</h3>
                    <p>Создайте запись к инструктору, чтобы получить персональную консультацию</p>
                </div>
            <?php else: ?>
                <div class="appointments-list">
                    <?php foreach ($upcomingAppointments as $appointment): ?>
                        <div class="appointment-card">
                            <div class="appointment-header">
                                <div class="appointment-icon">👤</div>
                                <div>
                                    <h3><?= e($appointment['instructor_name']) ?></h3>
                                    <p class="appointment-meta">
                                        <?= formatDate($appointment['appointment_date']) ?>
                                        • <?= $appointment['duration'] ?> мин
                                    </p>
                                </div>
                                <span class="badge <?= $appointment['status'] === 'confirmed' ? 'badge-green' : 'badge-orange' ?>">
                                    <?= $appointment['status'] === 'pending' ? '⏳ Ожидает' : '✓ Подтверждено' ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($appointment['notes'])): ?>
                                <div class="appointment-notes">
                                    <?= nl2br(e($appointment['notes'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="calendar-sidebar">
        <!-- Календарь с активностью -->
        <div class="card glass-card">
            <div class="card-header">
                <h3>📆 Календарь активности</h3>
            </div>
            <div id="activity-calendar"></div>
        </div>
        
        <!-- Подсказки -->
        <div class="card glass-card">
            <div class="card-header">
                <h3>💡 Подсказки</h3>
            </div>
            <ul class="tips-list">
                <li>🔥 Занимайтесь каждый день чтобы не потерять streak!</li>
                <li>💬 Встречи проходят онлайн в Zoom или Google Meet</li>
                <li>📞 Инструктор свяжется с вами за день до встречи</li>
                <li>⏰ Вы можете перенести встречу за 24 часа</li>
                <li>📝 Подготовьте вопросы заранее</li>
            </ul>
        </div>
    </div>
</div>

<style>
    /* === LIQUID GLASS ЭФФЕКТЫ === */
    .glass-card {
        background: rgba(255, 255, 255, 0.03) !important;
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2),
                    inset 0 1px 0 rgba(255, 255, 255, 0.1);
    }
    
    [data-theme="light"] .glass-card {
        background: rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(23, 26, 32, 0.12) !important;
        box-shadow: 0 8px 32px rgba(23, 26, 32, 0.1),
                    0 2px 8px rgba(23, 26, 32, 0.06),
                    inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }
    
    .glass-card:hover {
        background: rgba(255, 255, 255, 0.05) !important;
        border-color: rgba(255, 107, 53, 0.3) !important;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3),
                    inset 0 1px 0 rgba(255, 255, 255, 0.15),
                    0 0 0 1px rgba(255, 107, 53, 0.2);
    }
    
    [data-theme="light"] .glass-card:hover {
        background: rgba(255, 255, 255, 0.85) !important;
        border-color: rgba(255, 107, 53, 0.35) !important;
        box-shadow: 0 12px 40px rgba(23, 26, 32, 0.15),
                    0 4px 12px rgba(23, 26, 32, 0.08),
                    inset 0 1px 0 rgba(255, 255, 255, 0.9),
                    0 0 0 1px rgba(255, 107, 53, 0.25);
    }
    
    /* === ПЛАВНЫЕ ПЕРЕХОДЫ СТРАНИЦ === */
    .main-content {
        animation: pageSlideIn 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    @keyframes pageSlideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .calendar-container {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 2rem;
    }
    
    /* === СТАТИСТИКА STREAK === */
    .streak-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        padding: 1rem 0;
    }
    
    .streak-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        background: var(--input-bg);
        border-radius: 15px;
        border: 1px solid var(--border-color);
        transition: all var(--transition-speed) var(--transition-ease);
    }
    
    .streak-item:hover {
        transform: translateY(-5px) scale(1.02);
        border-color: var(--primary-orange);
        box-shadow: 0 10px 30px var(--shadow);
    }
    
    .streak-icon {
        font-size: 2.5rem;
        filter: drop-shadow(0 4px 10px rgba(255, 107, 53, 0.3));
    }
    
    .streak-value {
        font-size: 2rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .streak-label {
        font-size: 0.85rem;
        color: var(--text-secondary);
        font-weight: 500;
    }
    
    /* === ФОРМА === */
    .appointment-form {
        padding: 1rem 0;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .btn-large {
        width: 100%;
        padding: 1rem 2rem;
        font-size: 1.1rem;
    }
    
    /* === ВСТРЕЧИ === */
    .appointments-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .appointment-card {
        background: var(--input-bg);
        padding: 1.5rem;
        border-radius: 15px;
        border: 1px solid var(--border-color);
        transition: all 0.5s var(--transition-ease);
    }
    
    .appointment-card:hover {
        background: var(--bg-tertiary);
        border-color: var(--primary-orange);
        transform: translateX(10px) scale(1.01);
        box-shadow: 0 10px 30px var(--shadow);
    }
    
    .appointment-header {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .appointment-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
    }
    
    .appointment-header > div {
        flex-grow: 1;
    }
    
    .appointment-header h3 {
        font-size: 1.2rem;
        margin-bottom: 0.3rem;
        color: var(--text-primary);
    }
    
    .appointment-meta {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }
    
    .appointment-notes {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
        color: var(--text-secondary);
        font-size: 0.95rem;
        line-height: 1.6;
    }
    
    /* === КАЛЕНДАРЬ === */
    #activity-calendar {
        padding: 1rem 0.5rem;
    }
    
    .calendar-header {
        text-align: center;
        margin-bottom: 1.5rem;
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--text-primary);
    }
    
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 0.5rem;
        margin-top: 1rem;
    }
    
    .calendar-day-name {
        text-align: center;
        font-size: 0.75rem;
        color: var(--text-tertiary);
        padding: 0.5rem 0;
        font-weight: 600;
    }
    
    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 0.85rem;
        color: var(--text-secondary);
        transition: all 0.3s var(--transition-ease);
        cursor: pointer;
        position: relative;
    }
    
    .calendar-day:hover {
        background: var(--bg-tertiary);
        transform: scale(1.1);
    }
    
    .calendar-day.today {
        background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange));
        color: white;
        font-weight: 700;
        box-shadow: 0 4px 15px rgba(255, 107, 53, 0.4);
    }
    
    .calendar-day.active {
        background: rgba(76, 217, 100, 0.2);
        color: #4cd964;
        font-weight: 600;
    }
    
    .calendar-day.active::after {
        content: '✓';
        position: absolute;
        top: 2px;
        right: 2px;
        font-size: 0.6rem;
    }
    
    .calendar-day.streak {
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.2), rgba(255, 140, 66, 0.2));
        border: 1px solid var(--primary-orange);
    }
    
    /* === ПОДСКАЗКИ === */
    .tips-list {
        list-style: none;
        padding: 0;
    }
    
    .tips-list li {
        padding: 0.9rem;
        margin-bottom: 0.5rem;
        background: var(--input-bg);
        border-radius: 10px;
        border-left: 3px solid var(--primary-orange);
        font-size: 0.9rem;
        color: var(--text-secondary);
        transition: all 0.3s var(--transition-ease);
    }
    
    .tips-list li:hover {
        background: var(--bg-tertiary);
        transform: translateX(5px);
        border-left-width: 5px;
    }
    
    /* === ПУСТОЕ СОСТОЯНИЕ === */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }
    
    .empty-icon {
        font-size: 5rem;
        margin-bottom: 1.5rem;
        animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-20px);
        }
    }
    
    .empty-state h3 {
        font-size: 1.5rem;
        margin-bottom: 1rem;
        color: var(--text-primary);
    }
    
    .empty-state p {
        color: var(--text-secondary);
    }
    
    /* === АДАПТИВНОСТЬ === */
    @media (max-width: 1024px) {
        .calendar-container {
            grid-template-columns: 1fr;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .streak-stats {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .appointment-card:hover {
            transform: translateX(5px) scale(1.01);
        }
        
        .calendar-day {
            font-size: 0.75rem;
        }
    }
</style>

<script>
// Создание календаря с активностью
function createActivityCalendar() {
    const calendar = document.getElementById('activity-calendar');
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();
    
    const monthNames = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
    
    // Дни с активностью из PHP
    const activityDates = <?= json_encode($activityDates) ?>;
    const activitySet = new Set(activityDates);
    
    let html = '<div class="calendar-header">';
    html += monthNames[month] + ' ' + year;
    html += '</div>';
    
    html += '<div class="calendar-grid">';
    
    // Дни недели
    const dayNames = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
    dayNames.forEach(day => {
        html += '<div class="calendar-day-name">' + day + '</div>';
    });
    
    // Дни месяца
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = now.getDate();
    
    // Пустые ячейки в начале
    const startDay = firstDay === 0 ? 6 : firstDay - 1;
    for (let i = 0; i < startDay; i++) {
        html += '<div></div>';
    }
    
    // Дни
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        const isToday = day === today;
        const isActive = activitySet.has(dateStr);
        
        let classes = 'calendar-day';
        if (isToday) classes += ' today';
        if (isActive && !isToday) classes += ' active';
        
        html += '<div class="' + classes + '" title="' + dateStr + '">' + day + '</div>';
    }
    
    html += '</div>';
    calendar.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', createActivityCalendar);

// Автозаполнение телефона из профиля
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.getElementById('phone');
    if (phoneInput && !phoneInput.value) {
        // Можно добавить автоформатирование номера
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value[0] === '7') value = value.substring(1);
                if (value.length <= 10) {
                    const parts = [];
                    if (value.length > 0) parts.push('+7');
                    if (value.length >= 1) parts.push(' (' + value.substring(0, 3));
                    if (value.length >= 4) parts.push(') ' + value.substring(3, 6));
                    if (value.length >= 7) parts.push('-' + value.substring(6, 8));
                    if (value.length >= 9) parts.push('-' + value.substring(8, 10));
                    e.target.value = parts.join('');
                }
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
