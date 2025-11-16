<?php
/**
 * Календарь записи к инструкторам
 * Интерактивный календарь с выбором времени
 */

if (!isLoggedIn()) {
    setFlash('error', 'Необходимо авторизоваться');
    redirect('/login');
}

$user = getCurrentUser();

// Получаем список инструкторов
$stmt = $conn->query("
    SELECT id, name, email 
    FROM users 
    WHERE role = 'instructor'
    ORDER BY name
");
$instructors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем предстоящие встречи пользователя
$upcomingAppointments = getUpcomingAppointments($user['id']);

// Обработка создания встречи
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_appointment'])) {
    $instructorId = (int)$_POST['instructor_id'];
    $appointmentDate = $_POST['appointment_date'];
    $appointmentTime = $_POST['appointment_time'];
    $duration = (int)$_POST['duration'];
    $notes = trim($_POST['notes'] ?? '');
    
    $appointmentDateTime = $appointmentDate . ' ' . $appointmentTime;
    
    $stmt = $conn->prepare("
        INSERT INTO appointments (student_id, instructor_id, appointment_date, duration, notes, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    
    if ($stmt->execute([$user['id'], $instructorId, $appointmentDateTime, $duration, $notes])) {
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
        <!-- Создание новой записи -->
        <div class="card">
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
                    Создать запись
                </button>
            </form>
        </div>
        
        <!-- Предстоящие встречи -->
        <div class="card">
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
                                <span class="badge badge-orange">
                                    <?= $appointment['status'] === 'pending' ? 'Ожидает' : 'Подтверждено' ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($appointment['notes'])): ?>
                                <div class="appointment-notes">
                                    <strong>Тема:</strong> <?= e($appointment['notes']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="calendar-sidebar">
        <!-- Мини-календарь -->
        <div class="card">
            <div class="card-header">
                <h3>Календарь</h3>
            </div>
            <div id="mini-calendar"></div>
        </div>
        
        <!-- Подсказки -->
        <div class="card">
            <div class="card-header">
                <h3>💡 Подсказки</h3>
            </div>
            <ul class="tips-list">
                <li>Встречи проходят онлайн в Zoom или Google Meet</li>
                <li>Инструктор свяжется с вами за день до встречи</li>
                <li>Вы можете перенести встречу за 24 часа</li>
                <li>Подготовьте вопросы заранее для эффективной консультации</li>
            </ul>
        </div>
    </div>
</div>

<style>
    .calendar-container {
        display: grid;
        grid-template-columns: 1fr 350px;
        gap: 2rem;
    }
    
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
        background: rgba(255, 255, 255, 0.02);
        padding: 1.5rem;
        border-radius: 15px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        transition: all 0.3s;
    }
    
    .appointment-card:hover {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(255, 107, 53, 0.3);
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
    }
    
    .appointment-header > div {
        flex-grow: 1;
    }
    
    .appointment-header h3 {
        font-size: 1.2rem;
        margin-bottom: 0.3rem;
    }
    
    .appointment-meta {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.9rem;
    }
    
    .appointment-notes {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.95rem;
    }
    
    /* === МИНИ КАЛЕНДАРЬ === */
    #mini-calendar {
        padding: 1rem;
    }
    
    /* === ПОДСКАЗКИ === */
    .tips-list {
        list-style: none;
        padding: 0;
    }
    
    .tips-list li {
        padding: 0.8rem;
        margin-bottom: 0.5rem;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 10px;
        border-left: 3px solid var(--primary-orange);
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.7);
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
    }
    
    /* === АДАПТИВНОСТЬ === */
    @media (max-width: 1024px) {
        .calendar-container {
            grid-template-columns: 1fr;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
// Простой мини-календарь
function createMiniCalendar() {
    const calendar = document.getElementById('mini-calendar');
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();
    
    const monthNames = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
    
    let html = '<div style="text-align: center; margin-bottom: 1rem; font-weight: 700; font-size: 1.1rem;">';
    html += monthNames[month] + ' ' + year;
    html += '</div>';
    
    html += '<div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.3rem; text-align: center;">';
    
    // Дни недели
    const dayNames = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
    dayNames.forEach(day => {
        html += '<div style="font-size: 0.8rem; color: rgba(255, 255, 255, 0.5); padding: 0.5rem 0;">' + day + '</div>';
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
        const isToday = day === today;
        const style = isToday 
            ? 'background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange)); color: white; font-weight: 700;'
            : 'color: rgba(255, 255, 255, 0.7);';
        html += '<div style="padding: 0.6rem; border-radius: 8px; ' + style + ' cursor: pointer;">' + day + '</div>';
    }
    
    html += '</div>';
    calendar.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', createMiniCalendar);
</script>

<?php include __DIR__ . '/../templates/footer.php'; ?>
