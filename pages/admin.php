<?php
if (!isAdmin()) {
    setFlash('error', 'Доступ запрещен');
    redirect('/');
}

// Статистика
$stmt = $conn->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM courses");
$totalCourses = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(DISTINCT user_id) FROM progress");
$activeStudents = $stmt->fetchColumn();

$title = 'Админ-панель';
include 'templates/header.php';
?>

<div class="card">
    <h1 class="card-title">Панель администратора</h1>
    
    <div class="grid-3" style="margin-top: 2rem;">
        <div class="card" style="margin: 0; background: linear-gradient(135deg, var(--primary-orange), var(--secondary-orange)); color: white;">
            <h3 style="color: white; margin-bottom: 0.5rem;">Пользователей</h3>
            <div style="font-size: 3rem; font-weight: 800;"><?= $totalUsers ?></div>
        </div>
        
        <div class="card" style="margin: 0; background: linear-gradient(135deg, #4cd964, #34c759); color: white;">
            <h3 style="color: white; margin-bottom: 0.5rem;">Курсов</h3>
            <div style="font-size: 3rem; font-weight: 800;"><?= $totalCourses ?></div>
        </div>
        
        <div class="card" style="margin: 0; background: linear-gradient(135deg, #5ac8fa, #007aff); color: white;">
            <h3 style="color: white; margin-bottom: 0.5rem;">Активных студентов</h3>
            <div style="font-size: 3rem; font-weight: 800;"><?= $activeStudents ?></div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
