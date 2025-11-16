<?php
/**
 * Вспомогательные функции платформы Foxyd
 */

// === АВТОРИЗАЦИЯ ===

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser() {
    global $conn;
    
    if (!isLoggedIn()) {
        return null;
    }
    
    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([getCurrentUserId()]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            session_destroy();
            return null;
        }
        
        return $user;
    } catch (Exception $e) {
        return null;
    }
}

function isAdmin() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'admin';
}

function isTutor() {
    $user = getCurrentUser();
    return $user && $user['role'] === 'tutor';
}

// === НАВИГАЦИЯ ===

function redirect($url) {
    header("Location: " . $url);
    exit;
}

// === БЕЗОПАСНОСТЬ ===

function e($string) {
    if ($string === null || $string === '') {
        return '';
    }
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

// === ФОРМАТИРОВАНИЕ ===

function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' ₽';
}

function formatDate($date) {
    return date('d.m.Y H:i', strtotime($date));
}

function formatDuration($minutes) {
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    
    if ($hours > 0) {
        return $hours . ' ч ' . $mins . ' мин';
    }
    return $mins . ' мин';
}

// === FLASH СООБЩЕНИЯ ===

function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// === КУРСЫ ===

function getAllCourses() {
    global $conn;
    
    $stmt = $conn->query("
        SELECT c.*, u.name as instructor_name 
        FROM courses c 
        LEFT JOIN users u ON c.instructor_id = u.id 
        ORDER BY c.created_at DESC
    ");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCourseById($id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT c.*, u.name as instructor_name 
        FROM courses c 
        LEFT JOIN users u ON c.instructor_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCourseLessons($courseId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT * FROM lessons 
        WHERE course_id = ? 
        ORDER BY order_num ASC
    ");
    $stmt->execute([$courseId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// === ПРОГРЕСС ===

function getCourseProgress($userId, $courseId) {
    global $conn;
    
    // Получаем все уроки курса
    $stmt = $conn->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
    $stmt->execute([$courseId]);
    $totalLessons = $stmt->fetchColumn();
    
    if ($totalLessons == 0) {
        return 0;
    }
    
    // Получаем завершенные уроки
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM progress 
        WHERE user_id = ? AND course_id = ? AND completed = 1
    ");
    $stmt->execute([$userId, $courseId]);
    $completedLessons = $stmt->fetchColumn();
    
    return round(($completedLessons / $totalLessons) * 100);
}

function isLessonCompleted($userId, $lessonId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT completed FROM progress 
        WHERE user_id = ? AND lesson_id = ?
    ");
    $stmt->execute([$userId, $lessonId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result && $result['completed'] == 1;
}

function markLessonCompleted($userId, $courseId, $lessonId) {
    global $conn;
    
    // Проверяем существует ли запись
    $stmt = $conn->prepare("
        SELECT id FROM progress 
        WHERE user_id = ? AND lesson_id = ?
    ");
    $stmt->execute([$userId, $lessonId]);
    $exists = $stmt->fetch();
    
    if ($exists) {
        // Обновляем
        $stmt = $conn->prepare("
            UPDATE progress 
            SET completed = 1, completed_at = CURRENT_TIMESTAMP 
            WHERE user_id = ? AND lesson_id = ?
        ");
        $stmt->execute([$userId, $lessonId]);
    } else {
        // Создаем новую запись
        $stmt = $conn->prepare("
            INSERT INTO progress (user_id, course_id, lesson_id, completed, completed_at) 
            VALUES (?, ?, ?, 1, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([$userId, $courseId, $lessonId]);
    }
}

function getUserCourses($userId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT DISTINCT c.*, u.name as instructor_name 
        FROM courses c 
        LEFT JOIN users u ON c.instructor_id = u.id
        INNER JOIN progress p ON c.id = p.course_id
        WHERE p.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$userId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// === КАЛЕНДАРЬ ===

function getUpcomingAppointments($userId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT a.*, u.name as instructor_name 
        FROM appointments a
        LEFT JOIN users u ON a.instructor_id = u.id
        WHERE a.student_id = ? AND a.appointment_date >= datetime('now')
        ORDER BY a.appointment_date ASC
    ");
    $stmt->execute([$userId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getInstructorAppointments($instructorId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT a.*, u.name as student_name 
        FROM appointments a
        LEFT JOIN users u ON a.student_id = u.id
        WHERE a.instructor_id = ? AND a.appointment_date >= datetime('now')
        ORDER BY a.appointment_date ASC
    ");
    $stmt->execute([$instructorId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// === ВАЛИДАЦИЯ ===

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^\+?[0-9\s\-\(\)]{10,}$/', $phone);
}

// === РЕПЕТИТОРЫ ===

function getAllTutors() {
    global $conn;
    
    $stmt = $conn->query("
        SELECT t.*, u.name, u.email, u.phone 
        FROM tutors t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.verified = 1
        ORDER BY t.rating DESC
    ");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTutorById($id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT t.*, u.name, u.email, u.phone 
        FROM tutors t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.id = ?
    ");
    $stmt->execute([$id]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getTutorByUserId($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM tutors WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// === БРОНИРОВАНИЯ ===

function getStudentBookings($userId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT b.*, u.name as tutor_name, t.subjects, t.price_per_hour
        FROM bookings b
        JOIN tutors t ON b.tutor_id = t.id
        JOIN users u ON t.user_id = u.id
        WHERE b.student_id = ?
        ORDER BY b.lesson_date DESC
    ");
    $stmt->execute([$userId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTutorBookings($tutorId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT b.*, u.name as student_name, u.phone as student_phone, u.email as student_email
        FROM bookings b
        JOIN users u ON b.student_id = u.id
        WHERE b.tutor_id = ?
        ORDER BY b.lesson_date DESC
    ");
    $stmt->execute([$tutorId]);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
